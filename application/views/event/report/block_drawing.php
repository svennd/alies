<!-- based on https://github.com/bnjasim/paint-application-javascript -->
<style>

#sketch {
	border: 2px solid gray;
  	position: relative;
	min-height: 500px;
	width: 100%;
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

.panel-resizable {
  resize: vertical;
  overflow: hidden;
}

/* range style */

.range {
    display: table;
    position: relative;
    height: 25px;
    background-color: rgb(245, 245, 245);
    border-radius: 4px;
    -webkit-box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
    box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
    cursor: pointer;
}

.range input[type="range"] {
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    -ms-appearance: none !important;
    -o-appearance: none !important;
    appearance: none !important;

    display: table-cell;
    width: 100%;
    background-color: transparent;
    height: 25px;
    cursor: pointer;
}
.range input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    -ms-appearance: none !important;
    -o-appearance: none !important;
    appearance: none !important;

    width: 11px;
    height: 25px;
    color: rgb(255, 255, 255);
    text-align: center;
    white-space: nowrap;
    vertical-align: baseline;
    border-radius: 0px;
    background-color: rgb(153, 153, 153);
}

.range input[type="range"]::-moz-slider-thumb {
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    -ms-appearance: none !important;
    -o-appearance: none !important;
    appearance: none !important;
    
    width: 11px;
    height: 25px;
    color: rgb(255, 255, 255);
    text-align: center;
    white-space: nowrap;
    vertical-align: baseline;
    border-radius: 0px;
    background-color: rgb(153, 153, 153);
}

.range output {
    display: table-cell;
    padding: 0px 5px 2px;
    min-width: 40px;
    color: rgb(255, 255, 255);
    background-color: rgb(153, 153, 153);
    text-align: center;
    text-decoration: none;
    border-radius: 4px;
    border-bottom-left-radius: 0;
    border-top-left-radius: 0;
    width: 1%;
    white-space: nowrap;
    vertical-align: middle;

    -webkit-transition: all 0.5s ease;
    -moz-transition: all 0.5s ease;
    -o-transition: all 0.5s ease;
    -ms-transition: all 0.5s ease;
    transition: all 0.5s ease;

    -webkit-user-select: none;
    -khtml-user-select: none;
    -moz-user-select: -moz-none;
    -o-user-select: none;
    user-select: none;
}
.range input[type="range"] {
    outline: none;
}

.range.range-primary input[type="range"]::-webkit-slider-thumb {
    background-color: rgb(66, 139, 202);
}
.range.range-primary input[type="range"]::-moz-slider-thumb {
    background-color: rgb(66, 139, 202);
}
.range.range-primary output {
    background-color: rgb(66, 139, 202);
}
.range.range-primary input[type="range"] {
    outline-color: rgb(66, 139, 202);
}

</style>
<div class="row">

	<!-- controls -->
	<div class="col" id="control_tools">
		Tools:
		<br/>
			<button class="btn btn-outline-primary my-1" type="button" data-divbtn="pencil" title="Pencil"><i class="fas fa-pencil-alt fa-fw"></i></button>
			<button class="btn btn-outline-primary my-1" type="button" data-divbtn="eraser" title="Eraser"><i class="fas fa-eraser fa-fw"></i></button>
		<br/>
			<button class="btn btn-outline-primary my-1" type="button" data-divbtn="square" title="Square"><i class="far fa-square fa-fw"></i></button>
			<button class="btn btn-outline-primary my-1" type="button" data-divbtn="ellipse" title="Ellipse"><i class="far fa-circle fa-fw"></i></button>
		<br/>
			<button class="btn btn-outline-primary my-1" type="button" data-divbtn="fill" title="Fill"><i class="fas fa-fill-drip fa-fw"></i></button>
			<button class="btn btn-outline-primary my-1" type="button" data-divbtn="text" title="Text"><i class="fas fa-font fa-fw"></i></button>
		<br/>
		<br/>
			<button class="btn btn-outline-primary my-1" id="undo-tool" type="button" title="Undo"><i class="fas fa-undo fa-fw"></i></button>
			<button class="btn btn-outline-primary my-1" id="redo-tool" type="button" title="Redo"><i class="fas fa-redo fa-fw"></i></button>
		<br/>
			<button class="btn btn-outline-danger my-1" id="paint-clear" type="button" ><i class="fas fa-trash fa-fw"></i> Wipe&nbsp;&nbsp;</button>
		<br/>
		<br/>
	 		<button class="btn btn-outline-primary my-1" id="paint-store" type="button"><i class="fas fa-file-upload fa-fw"></i> Upload&nbsp;</button>
		<br/>
	</div>

	<!-- drawing area -->
	<div class="col-md-10">
		<div id="sketch">
			<canvas id="canvas" width="800" height="500"></canvas>
		</div>
		<input type="hidden" id="auto_safe_value" name="auto_safe_value" value="0" />
		<div id="remark" class="small text-right">&nbsp;</div>
	</div>

	<!-- color & size selection -->
	<div class="col-md-1">
		Color : 
		<p id="colorpicker">
		<br/>
	 		<button class="btn btn-outline-dark my-1" data-divbtn="red" type="button"><i class="fas fa-tint fa-fw" style="color:red;"></i></button>
	 		<button class="btn btn-outline-dark my-1" data-divbtn="blue" type="button"><i class="fas fa-tint fa-fw" style="color:blue;"></i></button>
	 		<button class="btn btn-outline-dark my-1" data-divbtn="yellow" type="button"><i class="fas fa-tint fa-fw" style="color:yellow;"></i></button>
		<br/>
	 		<button class="btn btn-outline-dark my-1" data-divbtn="orange" type="button"><i class="fas fa-tint fa-fw" style="color:orange;"></i></button>
	 		<button class="btn btn-outline-dark my-1" data-divbtn="green" type="button"><i class="fas fa-tint fa-fw" style="color:green;"></i></button>
	 		<button class="btn btn-outline-dark my-1" data-divbtn="purple" type="button"><i class="fas fa-tint fa-fw" style="color:purple;"></i></button>
		<br/>
		</p>
		Custom Color:
		<br/>
			<input type="color" name="color" id="picked_color" value="#23EFBF" class="btn btn-outline-dark my-1" style="width:98px;">
		<br/>
		<br/>
		Size:
			<div class="range range-primary">
				<input type="range" name="range" id="size_selector" min="1" max="10" value="2">
				<output id="rangePrimary">2</output>
			</div> 

	</div>
</div>
<div class="row">
	<div class="col-md-1">&nbsp;</div>
	<div class="col-md-10">
		Templates :	
		<div class="dropbox templates" id="templates">
			<img src="<?php echo base_url(); ?>assets/img/templates/eyes.png" alt="eyes" class="img-fluid img-thumbnail align-middle" />
			<img src="<?php echo base_url(); ?>assets/img/templates/dog.png" alt="dog" class="img-fluid img-thumbnail align-middle" />
			<img src="<?php echo base_url(); ?>assets/img/templates/skelet.jpg" alt="dog skelet" class="img-fluid img-thumbnail align-middle" />
		</div>
	</div>
</div>

<br/>

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
		// SaveToServer(canvas, '<?php echo $event_id; ?>', $("#auto_safe_value").val(), false);
		$("#remark").html("auto saved " + new Date().toTimeString().split(" ")[0]);
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
	var eraser_width = 6;
	var fontSize = '15px';

	// Pencil Points
	var ppts = [];

	var chosen_size = 3; // by default
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

	// menu
	$('#control_tools').on('click', function(event){
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

	// custom color picker
	$('#picked_color').on('input', function() {
		// remove the mouse down eventlistener if any
		tmp_canvas.removeEventListener('mousemove', tools_func[tool], false);

		// set color
		var color = $(this).val();
		tmp_ctx.strokeStyle = color;
		tmp_ctx.fillStyle = color;
	});

	// preselected color picker
	$('#colorpicker').on('click', function(event){
		tmp_canvas.removeEventListener('mousemove', tools_func[tool], false); 
		var target = event.target,
			tagName = target.tagName.toLowerCase();

		if(target && tagName != 'button'){
			target = target.parentNode;
        	tagName = target.tagName.toLowerCase();
		}

		if(target && tagName === 'button'){
			tmp_ctx.strokeStyle = $(target).data('divbtn'); 
			tmp_ctx.fillStyle = $(target).data('divbtn'); 
		}
	});

	// templates
	$('#templates').on('click', function(event){

		// if an image was clicked add it as background
		if(event.target.src !== undefined){
			// clear canvas
			ctx.clearRect(0, 0, tmp_canvas.width, tmp_canvas.height);
			
			// draw new background
			var background = new Image();
			background.src = event.target.src;
			background.onload = function() {
				// reset scope for different dimension
				canvas.width = this.width;
   				canvas.height = this.height;
				tmp_canvas.width = this.width;
				tmp_canvas.height = this.height;
				ctx.drawImage(background, 0, 0);
			}
		}
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
			var input_color = tmp_ctx.fillStyle;
			var rgb_color = hex2rgb(input_color);

    		var replace_r = rgb_color.r;
    		var replace_g = rgb_color.g;
    		var replace_b = rgb_color.b;

    		var imgd = ctx.getImageData(0, 0, canvas.width, canvas.height);
				var pix = imgd.data;
				var pos = 4 * (canvas.width * mouse.y + mouse.x);
				var target_color = rgb2hex(pix[pos],pix[pos+1],pix[pos+2], pix[pos+3]);

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

	$('#size_selector').on("change input", function() {
		var SelectedSize = $(this).val();
		$('#rangePrimary').html(SelectedSize);

		tmp_ctx.lineWidth = SelectedSize;
		eraser_width = SelectedSize*2;
		fontSize = 10+Math.round(SelectedSize*1.5) + 'px'; /* 12px -> 25px */
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
			var ucan = undo_canvas[next]['ucan'];
			ctx.clearRect(0, 0, canvas.width, canvas.height);
			ctx.drawImage(ucan, 0, 0);
			undo_canvas[next].redoable = false;
			undo_canvas_top = next;
		}
	});

	/* store on server image */
	$('#paint-store').click(function(){
		SaveToServer(canvas, '<?php echo $event_id; ?>', $("#auto_safe_value").val(), true);
		$("#remark").html("<strong>saved</strong> " + new Date().toTimeString().split(" ")[0]);
		$("#paint-store").addClass('btn-outline-success').html('Stored!');
	});


});

/*
 Save drawing :
	- copy image into a new canvas
	- crop the new canvas
	- set a white background color
	- send to server
 */
function SaveToServer(canvas, event_id, auto, isFinal) {
	
	var newAuto = 0;

	// create canvas & copy input canvas to it
	const store_canvas = document.createElement('canvas');
		store_canvas.width = canvas.width;
		store_canvas.height = canvas.height;

	store_ctx = store_canvas.getContext('2d');
		store_ctx.drawImage(canvas, 0, 0);
	
	// do the cropping
	const crop = trimCanvas(store_canvas);

	// add the background color
	const addBackgroundCanvas = document.createElement("canvas");
		addBackgroundCanvas.width = crop.width;
		addBackgroundCanvas.height = crop.height;

	var bck_ctx = addBackgroundCanvas.getContext('2d');
		 bck_ctx.fillStyle = '#FFFFFF';
		 bck_ctx.fillRect(0, 0, crop.width, crop.height);
		 bck_ctx.drawImage(crop, 0, 0);

	$.ajax({
		method: 'POST',
		url: isFinal ? '<?php echo base_url(); ?>files/drawing/' + event_id + '/' + auto + '/final' : '<?php echo base_url(); ?>files/drawing/' + event_id + '/' + auto,
		data: {
			drawing: addBackgroundCanvas.toDataURL('image/jpeg')
		},
		success: function (result) {
			const json = JSON.parse(result);
			isFinal ? $("#auto_safe_value").val(0) : $("#auto_safe_value").val(json.auto);
		}
	});
}

/* source: https://stackoverflow.com/questions/11796554/automatically-crop-html5-canvas-to-contents */
function trimCanvas(canvas) {
    const context = canvas.getContext('2d');

    const topLeft = {
        x: canvas.width,
        y: canvas.height,
        update(x,y){
            this.x = Math.min(this.x,x);
            this.y = Math.min(this.y,y);
        }
    };

    const bottomRight = {
        x: 0,
        y: 0,
        update(x,y){
            this.x = Math.max(this.x,x);
            this.y = Math.max(this.y,y);
        }
    };

    const imageData = context.getImageData(0,0,canvas.width,canvas.height);

    for(let x = 0; x < canvas.width; x++){
        for(let y = 0; y < canvas.height; y++){
            const alpha = imageData.data[((y * (canvas.width * 4)) + (x * 4)) + 3];
            if(alpha !== 0){
                topLeft.update(x,y);
                bottomRight.update(x,y);
            }
        }
    }

    const width = bottomRight.x - topLeft.x;
    const height = bottomRight.y - topLeft.y;

    const croppedCanvas = context.getImageData(topLeft.x,topLeft.y,width,height);
    canvas.width = width;
    canvas.height = height;
    context.putImageData(croppedCanvas,0,0);

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
