<!-- based on https://github.com/bnjasim/paint-application-javascript -->
<style>

#sketch {
	border: 2px solid gray;
  position: relative;
	height: 500px;
	padding: 10px;
}

#tmp_canvas {
	position: absolute;
	left: 10px;
	top: 10px;
	cursor: crosshair;
}

#text_tool {
    position: absolute;
    border: 1px dashed black;
    outline: 0;
    display: none;

    padding: 4px 8px;
    font: 14px;
    overflow: hidden;
    white-space: nowrap;
}

</style>

<div class="row">
	<div class="col">

	    <div id="sketch">
	      <canvas id="canvas"></canvas>
	    </div>
	    <div id="remark"></div>
	</div>
</div>
<br/>

<input type="color" name="color" id="picked_color" value="#000000" class="mb-2">

  <div class="btn-group" id="paint-panel" role="group" aria-label="Basic example">
    <button class="btn btn-sm btn-outline-success" type="button" data-divbtn="pencil" title="Pencil"><i class="fas fa-pencil-alt"></i></button>
    <button class="btn btn-sm btn-outline-success" type="button" data-divbtn="eraser" title="Eraser"><i class="fas fa-eraser"></i></button>
    <button class="btn btn-sm btn-outline-success" type="button" data-divbtn="square" title="Square"><i class="far fa-square"></i></button>
    <button class="btn btn-sm btn-outline-success" type="button" data-divbtn="ellipse" title="Ellipse"><i class="far fa-circle"></i></button>
    <button class="btn btn-sm btn-outline-success" type="button" data-divbtn="fill" title="Fill"><i class="fas fa-fill-drip"></i></button>
    <button class="btn btn-sm btn-outline-success" type="button" data-divbtn="text" title="Text"><i class="fas fa-font"></i></button>
	  <button class="btn btn-sm btn-outline-success" id="undo-tool" type="button" title="Undo"><i class="fas fa-undo"></i></button>
	  <button class="btn btn-sm btn-outline-success" id="redo-tool" type="button" title="Redo"><i class="fas fa-redo"></i></button>
  </div>

  <div class="btn-group" role="group" aria-label="Basic example">
	  <button id="paint-clear" type="button" class="btn btn-sm btn-outline-success">Clear</button>
	  <button id="paint-save" type="button" class="btn btn-sm btn-outline-success">Download</button>
	  <button id="paint-store" type="button" class="btn btn-sm btn-outline-primary">Store</button>
	  <button id="paint-eyes" type="button" class="btn btn-sm btn-outline-success">Load eyes</button>
	  <button id="paint-dog" type="button" class="btn btn-sm btn-outline-success">Load dog</button>
	</div>

<!--
  <form id="choose-size">
    <div class="title">pick size</div>
    <div class="radio-group"><input type="radio" name="size" value="1">1</div>
    <div class="radio-group"><input type="radio" name="size" value="2" checked="checked">2</div>
    <div class="radio-group"><input type="radio" name="size" value="3">3</div>
    <div class="radio-group"><input type="radio" name="size" value="4">4</div>
  </form>
-->


<script>
document.addEventListener("DOMContentLoaded", function(event) {

	var sketch = document.querySelector('#sketch');
	var canvas = document.querySelector('#canvas');
	var tmp_canvas = document.createElement('canvas');
	canvas.width = $(sketch).width();
	canvas.height = $(sketch).height();
	tmp_canvas.width = canvas.width;
	tmp_canvas.height = canvas.height;

	// auto save
	sketch.onclick = function(){
	setTimeout(
	function() {
		console.log("sending to store!");
		crop = cropImageFromCanvas(ctx);

		 // set background color
		 newCanvas = document.createElement("canvas");
		 newCanvas.width = crop.width;
		 newCanvas.height = crop.height;

		 bck_ctx = newCanvas.getContext('2d');
		 bck_ctx.fillStyle = '#FFFFFF';
		 bck_ctx.fillRect(0, 0, crop.width, crop.height);
		 bck_ctx.drawImage(crop, 0, 0);

		var photo = newCanvas.toDataURL('image/jpeg');
		$.ajax({
		  method: 'POST',
		  url: '<?php echo base_url(); ?>files/drawing/<?php echo $event_id; ?>',
		  data: {
		    drawing: photo
		  }
		});
		}, 750);
	}

	var undo_canvas = [];
	var undo_canvas_len = 7;
	for (var i=0; i<undo_canvas_len; ++i) {
		var ucan = document.createElement('canvas');
		ucan.width = canvas.width;
		ucan.height = canvas.height;
		var uctx = ucan.getContext('2d');
		undo_canvas.push({'ucan':ucan, 'uctx':uctx, 'redoable':false});
	}

	var undo_canvas_top = 0;

	var ctx = canvas.getContext('2d');
	var tmp_ctx = tmp_canvas.getContext('2d');
	tmp_canvas.id = 'tmp_canvas';
	sketch.appendChild(tmp_canvas);

	var mouse = {x: 0, y: 0};
	var start_mouse = {x:0, y:0};
	var eraser_width = 10;
	var fontSize = '14px';

	// Pencil Points
	var ppts = [];

	var chosen_size = 2; // by default
	/* Drawing on Paint App */
	tmp_ctx.lineWidth = 3;
	tmp_ctx.lineJoin = 'round';
	tmp_ctx.lineCap = 'round';
	tmp_ctx.strokeStyle = 'black';
	tmp_ctx.fillStyle = 'black';

	// paint functions
	var paint_pencil = function(e) {

		mouse.x = typeof e.offsetX !== 'undefined' ? e.offsetX : e.layerX;
		mouse.y = typeof e.offsetY !== 'undefined' ? e.offsetY : e.layerY;
		//console.log(mouse.x + " "+mouse.y);
		// Saving all the points in an array
		ppts.push({x: mouse.x, y: mouse.y});

		if (ppts.length < 3) {
			var b = ppts[0];
			tmp_ctx.beginPath();
			//ctx.moveTo(b.x, b.y);
			//ctx.lineTo(b.x+50, b.y+50);
			tmp_ctx.arc(b.x, b.y, tmp_ctx.lineWidth / 2, 0, Math.PI * 2, !0);
			tmp_ctx.fill();
			tmp_ctx.closePath();
			return;
		}

		// Tmp canvas is always cleared up before drawing.
		tmp_ctx.clearRect(0, 0, tmp_canvas.width, tmp_canvas.height);

		tmp_ctx.beginPath();
		tmp_ctx.moveTo(ppts[0].x, ppts[0].y);

		for (var i = 0; i < ppts.length; i++)
			tmp_ctx.lineTo(ppts[i].x, ppts[i].y);

		tmp_ctx.stroke();

	};

	var paint_square = function(e) {
		mouse.x = typeof e.offsetX !== 'undefined' ? e.offsetX : e.layerX;
		mouse.y = typeof e.offsetY !== 'undefined' ? e.offsetY : e.layerY;
		// Tmp canvas is always cleared up before drawing.
    	tmp_ctx.clearRect(0, 0, tmp_canvas.width, tmp_canvas.height);
 		tmp_ctx.beginPath();
    	tmp_ctx.moveTo(start_mouse.x, start_mouse.y);

		var x = Math.min(mouse.x, start_mouse.x);
		var y = Math.min(mouse.y, start_mouse.y);
		var width = Math.abs(mouse.x - start_mouse.x);
		var height = Math.abs(mouse.y - start_mouse.y);
		tmp_ctx.strokeRect(x, y, width, height);
		tmp_ctx.closePath();
	}

	var paint_ellipse = function(e) {
		mouse.x = typeof e.offsetX !== 'undefined' ? e.offsetX : e.layerX;
		mouse.y = typeof e.offsetY !== 'undefined' ? e.offsetY : e.layerY;
		// Tmp canvas is always cleared up before drawing.
    	tmp_ctx.clearRect(0, 0, tmp_canvas.width, tmp_canvas.height);

    	var x = start_mouse.x;
    	var y = start_mouse.y;
    	var w = (mouse.x - x);
    	var h = (mouse.y - y);

  		tmp_ctx.save(); // save state
        tmp_ctx.beginPath();

        tmp_ctx.translate(x, y);
        tmp_ctx.scale(w/2, h/2);
        tmp_ctx.arc(1, 1, 1, 0, 2 * Math.PI, false);

        tmp_ctx.restore(); // restore to original state
        tmp_ctx.stroke();
        tmp_ctx.closePath();

	}

	var paint_text = function(e) {
		// Tmp canvas is always cleared up before drawing.
    	tmp_ctx.clearRect(0, 0, tmp_canvas.width, tmp_canvas.height);
     	mouse.x = typeof e.offsetX !== 'undefined' ? e.offsetX : e.layerX;
		mouse.y = typeof e.offsetY !== 'undefined' ? e.offsetY : e.layerY;

    	var x = Math.min(mouse.x, start_mouse.x);
    	var y = Math.min(mouse.y, start_mouse.y);
    	var width = Math.abs(mouse.x - start_mouse.x);
    	var height = Math.abs(mouse.y - start_mouse.y);

    	textarea.style.left = x + 'px';
    	textarea.style.top = y + 'px';
    	textarea.style.width = width + 'px';
    	textarea.style.height = height + 'px';

    	textarea.style.display = 'block';
	}

	var paint_eraser = function(e) {
		mouse.x = typeof e.offsetX !== 'undefined' ? e.offsetX : e.layerX;
		mouse.y = typeof e.offsetY !== 'undefined' ? e.offsetY : e.layerY;
		// erase from the main ctx
    	ctx.clearRect(mouse.x, mouse.y, eraser_width, eraser_width);
	}


	// Choose tool
	tool = 'pencil';
	tools_func = {'pencil':paint_pencil, 'square':paint_square, 'ellipse':paint_ellipse, 'eraser':paint_eraser, 'text':paint_text};

	$('#paint-panel').on('click', function(event){
		// remove the mouse down eventlistener if any
		tmp_canvas.removeEventListener('mousemove', tools_func[tool], false);

		var target = event.target,
			tagName = target.tagName.toLowerCase();

		if(target && tagName != 'button'){
			target = target.parentNode;
        	tagName = target.tagName.toLowerCase();
		}

		if(target && tagName === 'button'){
			tool = $(target).data('divbtn');
		}
	});

	$('#picked_color').on('input', function() {
		// remove the mouse down eventlistener if any
		tmp_canvas.removeEventListener('mousemove', tools_func[tool], false);

		// set color
		var color = $(this).val();
		tmp_ctx.strokeStyle = color;
		tmp_ctx.fillStyle = color;
	});


	// Mouse-Down
	tmp_canvas.addEventListener('mousedown', function(e) {

		mouse.x = typeof e.offsetX !== 'undefined' ? e.offsetX : e.layerX;
		mouse.y = typeof e.offsetY !== 'undefined' ? e.offsetY : e.layerY;
		start_mouse.x = mouse.x;
    	start_mouse.y = mouse.y;
    	tmp_ctx.clearRect(0, 0, tmp_canvas.width, tmp_canvas.height);

		if (tool === 'pencil') {
			tmp_canvas.addEventListener('mousemove', paint_pencil, false);
			ppts.push({x: mouse.x, y: mouse.y});
			paint_pencil(e);
		}

		if (tool === 'square') {
			tmp_canvas.addEventListener('mousemove', paint_square, false);
		}

		if (tool === 'ellipse') {
			tmp_canvas.addEventListener('mousemove', paint_ellipse, false);
    	}

    	if (tool === 'text') {
    		tmp_canvas.addEventListener('mousemove', paint_text, false);
    		textarea.style.display = 'none'; // important to hide when clicked outside
    	}

    	if (tool === 'eraser') {
    		tmp_canvas.addEventListener('mousemove', paint_eraser, false);
    		// erase from the main ctx
    		ctx.clearRect(mouse.x, mouse.y, eraser_width, eraser_width);
    	}

    	if (tool === 'fill') {

        var input_color = $("#picked_color").val();
        var rgb_color = hex2rgb(input_color);

    		var replace_r = rgb_color.r;
    		var replace_g = rgb_color.g;
    		var replace_b = rgb_color.b;

    		var imgd = ctx.getImageData(0, 0, canvas.width, canvas.height);
				var pix = imgd.data;
				// console.log(pix);
				// pix is row-wise straightened array
				var pos = 4 * (canvas.width * mouse.y + mouse.x);

				// console.log(pos);
				// ppts.push({x: mouse.x, y: mouse.y});
				// paint_pencil(e);

				// var target_color = map_to_color(pix[pos],pix[pos+1],pix[pos+2],pix[pos+3]);
				var target_color = rgb2hex(pix[pos],pix[pos+1],pix[pos+2], pix[pos+3]);
				// console.log(pix[pos],pix[pos+1],pix[pos+2]);

			// console.log("input" + input_color, "target" + target_color);
      if (input_color !== target_color) {
				var Q = [pos];

				while (Q.length > 0) {

					pos = Q.shift();
					if (rgb2hex(pix[pos],pix[pos+1],pix[pos+2],pix[pos+3]) !== target_color) {
						continue; // color is already changed
					}

					var left = find_left_most_similar_pixel(pix, pos, target_color);
					var right = find_right_most_similar_pixel(pix, pos, target_color);

					for (var i=left; i<=right; i=i+4) {
						pix[i] = replace_r;
						pix[i+1] = replace_g;
						pix[i+2] = replace_b;
						pix[i+3] = 255; // not transparent

						var top = i - 4*canvas.width;
						var down = i + 4*canvas.width;

						if (top >= 0 && rgb2hex(pix[top], pix[top+1], pix[top+2], pix[top+3]) === target_color)
							Q.push(top);

						if (down < pix.length && rgb2hex(pix[down],pix[down+1],pix[down+2],pix[down+3]) === target_color)
							Q.push(down);
					}

				}
				// Draw the ImageData at the given (x,y) coordinates.
				ctx.putImageData(imgd, 0, 0);
			}

    	}

	}, false);

	// for filling
	var find_left_most_similar_pixel = function(pix, pos, target_color) {
		var y = Math.floor(pos/(4*canvas.width));
		var left = pos;
		var end = y * canvas.width * 4;
		while (end < left) {
			if (rgb2hex(pix[left-4],pix[left-3],pix[left-2],pix[left-1]) === target_color)
				left = left - 4;
			else
				break;
		}
		return left;
	}

	var find_right_most_similar_pixel = function(pix, pos, target_color) {
		var y = Math.floor(pos/(4*canvas.width));
		var right = pos;
		var end = (y+1) * canvas.width * 4 - 4;
		while (end > right) {
			if (rgb2hex(pix[right+4],pix[right+5],pix[right+6],pix[right+7]) === target_color)
				right = right + 4;
			else
				break;
		}
		return right;
	}

	// text-tool
	var textarea = document.createElement('textarea');
	textarea.id = 'text_tool';
	sketch.appendChild(textarea);


	textarea.addEventListener('mouseup', function(e) {
		tmp_canvas.removeEventListener('mousemove', paint_text, false);
	}, false);

	// set the color
	textarea.addEventListener('mousedown', function(e){
		textarea.style.color = tmp_ctx.strokeStyle;
		textarea.style['font-size'] = fontSize;
	}, false);


	textarea.addEventListener('blur', function(e) {
		var lines = textarea.value.split('\n');
			var ta_comp_style = getComputedStyle(textarea);
    		var fs = ta_comp_style.getPropertyValue('font-size');

    		var ff = ta_comp_style.getPropertyValue('font-family');

    		tmp_ctx.font = fs + ' ' + ff;
    		tmp_ctx.textBaseline = 'hanging';

    		for (var n = 0; n < lines.length; n++) {
        		var line = lines[n];

        		tmp_ctx.fillText(
            		line,
            		parseInt(textarea.style.left),
            		parseInt(textarea.style.top) + n*parseInt(fs)
        		);
    		}

    		// Writing down to real canvas now
    		ctx.drawImage(tmp_canvas, 0, 0);
    		textarea.style.display = 'none';
    		textarea.value = '';
    		// Clearing tmp canvas
			tmp_ctx.clearRect(0, 0, tmp_canvas.width, tmp_canvas.height);

			// keep the image in the undo_canvas
			undo_canvas_top = next_undo_canvas(undo_canvas_top);
			var uctx = undo_canvas[undo_canvas_top]['uctx'];
			uctx.clearRect(0, 0, canvas.width, canvas.height);
			uctx.drawImage(canvas, 0, 0);
			undo_canvas[undo_canvas_top]['redoable'] = false;
	});

	tmp_canvas.addEventListener('mouseup', function() {
		tmp_canvas.removeEventListener('mousemove', tools_func[tool], false);

		// Writing down to real canvas now
		// text-tool is managed when textarea.blur() event
		if (tool !='text') {
			ctx.drawImage(tmp_canvas, 0, 0);
			// keep the image in the undo_canvas
			undo_canvas_top = next_undo_canvas(undo_canvas_top);
			var uctx = undo_canvas[undo_canvas_top]['uctx'];
			uctx.clearRect(0, 0, canvas.width, canvas.height);
			uctx.drawImage(canvas, 0, 0);
			undo_canvas[undo_canvas_top]['redoable'] = false;
		}


		// Clearing tmp canvas
		tmp_ctx.clearRect(0, 0, tmp_canvas.width, tmp_canvas.height);

		// Emptying up Pencil Points
		ppts = [];
	}, false);

	var next_undo_canvas = function(top) {
		if (top === undo_canvas_len-1)
			return 0;
		else
			return top+1;
	}

	var prev_undo_canvas = function(top) {
		if (top === 0)
			return undo_canvas_len-1;
		else
			return  top-1;
	}

	// clear paint area
	$('#paint-clear').click(function(){
		ctx.clearRect(0, 0, tmp_canvas.width, tmp_canvas.height);
		// keep the image in the undo_canvas
		undo_canvas_top = next_undo_canvas(undo_canvas_top);
		var uctx = undo_canvas[undo_canvas_top]['uctx'];
		uctx.clearRect(0, 0, canvas.width, canvas.height);
		uctx.drawImage(canvas, 0, 0);
		undo_canvas[undo_canvas_top]['redoable'] = false;
	});


	// Change Size
  $('.radio-group').on('click', function(){
		var s = $('input[name=size]:checked').val();

    // console.log("got" + s);
		if (s==='1') {
			tmp_ctx.lineWidth = 1;
			eraser_width = 5;
			fontSize = '10px';
		}
		if (s==='2') {
			tmp_ctx.lineWidth = 3;
			eraser_width = 10;
			fontSize = '14px';
		}
		if (s==='3') {
			tmp_ctx.lineWidth = 6;
			eraser_width = 15;
			fontSize = '18px';
		}
		if (s==='4') {
			tmp_ctx.lineWidth = 10;
			eraser_width = 20;
			fontSize = '22px';
		}
	});

	// undo-redo tools
	$('#undo-tool').on('click', function(){
		var prev = prev_undo_canvas(undo_canvas_top);
		if (!undo_canvas[prev].redoable) {
			// console.log(undo_canvas_top+' prev='+prev);
			var ucan = undo_canvas[prev]['ucan'];
			ctx.clearRect(0, 0, canvas.width, canvas.height);
			ctx.drawImage(ucan, 0, 0);
			undo_canvas[undo_canvas_top].redoable = true;
			undo_canvas_top = prev;
		}
	});

	$('#redo-tool').on('click', function(){
		var next = next_undo_canvas(undo_canvas_top);
		if (undo_canvas[next].redoable) {
			// console.log(undo_canvas_top);
			var ucan = undo_canvas[next]['ucan'];
			ctx.clearRect(0, 0, canvas.width, canvas.height);
			ctx.drawImage(ucan, 0, 0);
			undo_canvas[next].redoable = false;
			undo_canvas_top = next;
		}
	});


/* download image */
$('#paint-save').click(function(){

	crop = cropImageFromCanvas(ctx);

	 // set background color
	 newCanvas = document.createElement("canvas");
	 newCanvas.width = crop.width;
	 newCanvas.height = crop.height;

	 bck_ctx = newCanvas.getContext('2d');
	 bck_ctx.fillStyle = '#FFFFFF';
	 bck_ctx.fillRect(0, 0, crop.width, crop.height);
	 bck_ctx.drawImage(crop, 0, 0);

	 var dataURL = newCanvas.toDataURL("image/jpeg", 1.0);
 	    downloadImage(dataURL, 'my-canvas.jpeg');
});

/* download image */
$('#paint-store').click(function(){

	crop = cropImageFromCanvas(ctx);

	 // set background color
	 newCanvas = document.createElement("canvas");
	 newCanvas.width = crop.width;
	 newCanvas.height = crop.height;

	 bck_ctx = newCanvas.getContext('2d');
	 bck_ctx.fillStyle = '#FFFFFF';
	 bck_ctx.fillRect(0, 0, crop.width, crop.height);
	 bck_ctx.drawImage(crop, 0, 0);

	var photo = newCanvas.toDataURL('image/jpeg');
	$.ajax({
	  method: 'POST',
	  url: '<?php echo base_url(); ?>files/drawing/<?php echo $event_id; ?>',
	  data: {
	    drawing: photo
	  }
	});
});

$('#paint-eyes').click(function(){

	var background = new Image();
	background.src = "<?php echo base_url(); ?>assets/img/templates/eyes.png";

	background.onload = function(){
	    ctx.drawImage(background,0,0);
	}
});

$('#paint-dog').click(function(){

	var background = new Image();
	background.src = "<?php echo base_url(); ?>assets/img/templates/dog.png";

	background.onload = function(){
	    ctx.drawImage(background,0,0);
	}
});

});

// crop image
function cropImageFromCanvas(ctx) {
  var canvas = ctx.canvas,
    w = canvas.width, h = canvas.height,
    pix = {x:[], y:[]},
    imageData = ctx.getImageData(0,0,canvas.width,canvas.height),
    x, y, index;

  for (y = 0; y < h; y++) {
    for (x = 0; x < w; x++) {
      index = (y * w + x) * 4;
      if (imageData.data[index+3] > 0) {
        pix.x.push(x);
        pix.y.push(y);
      }
    }
  }
  pix.x.sort(function(a,b){return a-b});
  pix.y.sort(function(a,b){return a-b});
  var n = pix.x.length-1;

  w = 1 + pix.x[n] - pix.x[0];
  h = 1 + pix.y[n] - pix.y[0];
  var cut = ctx.getImageData(pix.x[0], pix.y[0], w+1, h+1);

  canvas.width = w;
  canvas.height = h;
  ctx.putImageData(cut, 0, 0);

	return canvas;
}

// Save | Download image
function downloadImage(data, filename = 'untitled.jpeg') {
		var a = document.createElement('a');
		a.href = data;
		a.download = filename;
		document.body.appendChild(a);
		a.click();
}

// https://stackoverflow.com/questions/5623838/rgb-to-hex-and-hex-to-rgb
const hex2rgb = (hex) => {
    const r = parseInt(hex.slice(1, 3), 16)
    const g = parseInt(hex.slice(3, 5), 16)
    const b = parseInt(hex.slice(5, 7), 16)
    return {r, g, b}
}
const rgb2hex = (r, g, b, a) => {
		if (a === 0) { return '#ffffff'; }
    var rgb = (r << 16) | (g << 8) | b
    return '#' + rgb.toString(16).padStart(6, 0)
}

</script>
