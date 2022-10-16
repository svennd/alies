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

.templates {
	width: 100%;
	height: 150px;
}

.templates .img-fluid {
    /* display: block; */
	margin-top: 5px;
	margin-left: 5px;
    width: auto;
    max-height: 95%;
}

</style>
<div class="row">
	<?php echo ($drawing_temp) ? '<img src="' . base_url() . $drawing_temp[0].'" id="RestoreImg" alt="restored image" class="d-none" />' : ''; ?>
	<!-- controls -->
	<div class="col" id="control_tools">
	<?php echo $this->lang->line('tools'); ?>:
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
	</div>

	<!-- drawing area -->
	<div class="col-md-10">
		<div id="sketch">
			<canvas id="canvas"></canvas>
		</div>
		<div id="remark" class="small text-right">&nbsp;</div>
		<input type="hidden" name="event_id" value="<?php echo $event_id; ?>" id="event_id" />
	</div>

	<!-- color & size selection -->
	<div class="col-md-1">
	<?php echo $this->lang->line('color'); ?> : 
		<p id="colorpicker">
		<br/>
	 		<button class="btn btn-outline-dark my-1" data-divbtn="red" type="button"><i class="fas fa-tint fa-fw" style="color:red;"></i></button>
	 		<button class="btn btn-outline-dark my-1" data-divbtn="blue" type="button"><i class="fas fa-tint fa-fw" style="color:blue;"></i></button>
	 		<button class="btn btn-outline-dark my-1" data-divbtn="yellow" type="button"><i class="fas fa-tint fa-fw" style="color:yellow;"></i></button>
	 		<button class="btn btn-outline-dark my-1" data-divbtn="orange" type="button"><i class="fas fa-tint fa-fw" style="color:orange;"></i></button>
	 		<button class="btn btn-outline-dark my-1" data-divbtn="green" type="button"><i class="fas fa-tint fa-fw" style="color:green;"></i></button>
	 		<button class="btn btn-outline-dark my-1" data-divbtn="purple" type="button"><i class="fas fa-tint fa-fw" style="color:purple;"></i></button>
		<br/>
		</p>
		<?php echo $this->lang->line('custom_color'); ?> :
		<br/>
			<input type="color" name="color" id="picked_color" value="#23EFBF" class="btn btn-outline-dark my-1" style="width:98px;">
		<br/>
		<br/>
		<?php echo $this->lang->line('size'); ?> :
			<div class="range range-primary">
				<input type="range" name="range" id="size_selector" min="1" max="10" value="2">
				<output id="rangePrimary">2</output>
			</div> 

	</div>
</div>
<div class="row">
	<div class="col-md-1">&nbsp;</div>
	<div class="col-md-10">
		<p class="text-right">
			<button class="btn btn-outline-danger mx-3" id="paint-clear" type="button" ><i class="fas fa-trash fa-fw"></i> <?php echo $this->lang->line('wipe'); ?></button>
			<button class="btn btn-outline-primary" id="paint-store" type="button"><i class="fas fa-file-upload fa-fw"></i> <?php echo $this->lang->line('store'); ?></button>
		</p>
		<?php echo $this->lang->line('templates'); ?> :	
		<div class="dropbox templates" id="templates">
			<img src="<?php echo base_url(); ?>assets/img/templates/eyes.png" alt="eyes" class="img-fluid img-thumbnail align-middle" />
			<img src="<?php echo base_url(); ?>assets/img/templates/dog.png" alt="dog" class="img-fluid img-thumbnail align-middle" />
			<img src="<?php echo base_url(); ?>assets/img/templates/skelet.jpg" alt="dog skelet" class="img-fluid img-thumbnail align-middle" />
		</div>
	</div>
</div>

<br/>

<!-- <script src="<?php echo base_url(); ?>assets/js/drawie/index.js"></script> -->
<script src="<?php echo base_url(); ?>assets/js/drawie/class.js"></script>
<script>
	const canvas = document.querySelector('canvas');
		canvas.width = 800;
		canvas.height = 500;
		canvas.style = "background-color:red;"

	const field = new drawie({
		canvas: canvas,
		width : canvas.width,
		height : canvas.height,
		url: "<?php echo base_url(); ?>"
	});


</script>