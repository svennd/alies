<style>
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

/* Import Google font - Poppins */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');

#fill-color:checked ~ label{
  color: #4A98F7;
}

.xxx .options{
  list-style: none;
  /* margin: 10px 0 0 5px; */
}

.option #size-slider{
  width: 100%;
  height: 5px;
  margin-top: 10px;
}
.colors .options{
  display: flex;
  justify-content: space-between;
}
.colors .option{
  list-style: none;
  height: 20px;
  width: 20px;
  border-radius: 50%;
  margin-top: 3px;
  position: relative;
}
.colors .option:nth-child(1){
  background-color: #fff;
  border: 1px solid #bfbfbf;
}
.colors .option:nth-child(2){
  background-color: #000;
}
.colors .option:nth-child(3){
  background-color: #E02020;
}
.colors .option:nth-child(4){
  background-color: #6DD400;
}
.colors .option:nth-child(5){
  background-color: #4A98F7;
}
.colors .option.selected::before{
  position: absolute;
  content: "";
  top: 50%;
  left: 50%;
  height: 12px;
  width: 12px;
  background: inherit;
  border-radius: inherit;
  border: 2px solid #fff;
  transform: translate(-50%, -50%);
}
.colors .option:first-child.selected::before{
  border-color: #ccc;
}
.option #color-picker{
  opacity: 0;
  cursor: pointer;
}
.canvas {
  cursor: crosshair;
  border: 2px solid gray;
}

</style>
<div class="row">
	<?php echo ($drawing_temp) ? '<img src="' . base_url() . $drawing_temp[0].'" id="RestoreImg" alt="restored image" class="d-none" />' : ''; ?>
	<!-- controls -->
	<div class="col">
	<?php echo $this->lang->line('tools'); ?>:
  <br/>
      <button class="btn btn-outline-primary option tool my-1" type="button" id="brush" data-divbtn="pencil" title="Pencil"><i class="fas fa-pencil-alt fa-fw"></i></button>
      <button class="btn btn-outline-primary option tool my-1" type="button" id="eraser" data-divbtn="eraser" title="Eraser"><i class="fas fa-eraser fa-fw"></i></button>
		<br/>
			<button class="btn btn-outline-primary option tool my-1" type="button" id="rectangle" data-divbtn="square" title="Square"><i class="far fa-square fa-fw"></i></button>
			<button class="btn btn-outline-primary option tool my-1" type="button" id="circle" data-divbtn="ellipse" title="Ellipse"><i class="far fa-circle fa-fw"></i></button>
      <br/>
      <input type="checkbox" id="fill-color" class="my-1" checked> <label for="fill-color">Fill color</label>

      <br />
      <?php echo $this->lang->line('size'); ?>

      <br />
      <input type="range" id="size-slider" min="1" max="30" value="5">

      <br />
      <div class="colors">
        <label class="title"><?php echo $this->lang->line('color'); ?></label>
        <ul class="options" style="padding:0;width:140px;">
          <li class="option"></li>
          <li class="option selected"></li>
          <li class="option"></li>
          <li class="option"></li>
          <li class="option">
            <input type="color" id="color-picker" value="#4A98F7">
          </li>
        </ul>
      </div>
	</div>

	<!-- drawing area -->
	<div class="col-md-10" id="drawing">
    <canvas class="canvas"></canvas>
		<div id="remark" class="small text-right">&nbsp;</div>
		<input type="hidden" name="event_id" value="<?php echo $event_id; ?>" id="event_id" />
    <input type="hidden" id="reset_url" name="reset_url" value="<?php echo base_url(); ?>files/reset_draw/<?php echo $event_id; ?>" />
	</div>

</div>
<div class="row">
	<div class="col">&nbsp;</div>
	<div class="col-md-10">
		<?php echo $this->lang->line('templates'); ?> :	
		<div class="dropbox templates" id="templates">
			<img src="<?php echo base_url(); ?>assets/img/templates/eyes.png" alt="eyes" class="img-fluid img-thumbnail align-middle" />
			<img src="<?php echo base_url(); ?>assets/img/templates/dog.png" alt="dog" class="img-fluid img-thumbnail align-middle" />
			<img src="<?php echo base_url(); ?>assets/img/templates/skelet.jpg" alt="dog skelet" class="img-fluid img-thumbnail align-middle" />
		</div>
    <br/>
    <p class="text-right">
			<button class="btn btn-outline-danger mx-3 clear-canvas" id="paint-clear" type="button" ><i class="fas fa-trash fa-fw"></i> <?php echo $this->lang->line('wipe'); ?></button>
			<button class="btn btn-outline-primary" id="paint-store" type="button"><i class="fas fa-file-upload fa-fw"></i> <?php echo $this->lang->line('store'); ?></button>
		</p>

	</div>
</div>

<br/>

<script src="<?php echo base_url(); ?>assets/js/drawie/index.js"></script>
<script>

document.addEventListener("DOMContentLoaded", function(){
$('a[data-toggle="tab"]').on('shown.bs.tab', function (event) {
//   event.target // newly activated tab
//   event.relatedTarget // previous active tab
// console.log("fired");
// console.log(event.target);
// console.log(event.relatedTarget);
	$('canvas').attr('height', '400px');
	$('canvas').attr('width', (Math.round($('#drawing').width()) - 15) + 'px');
	// $('canvas').attr('style', 'background-color:blue');
});
});
</script>