<div class="modal fade" id="staticBackdrop" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="staticBackdropLabel"><?php echo $this->lang->line('select_location'); ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
			<p><?php echo $this->lang->line('select_location_long'); ?></p>
			<div class="btn-toolbar" role="toolbar" aria-label="Toolbar with button groups">
				<div class="btn-group mr-2" role="group" aria-label="First group">
					<?php foreach($location as $l) : ?>
					<a class="btn <?php echo ($suggest_location == -1 || $suggest_location == $l['id']) ? "btn-outline-success": "btn-outline-warning"; ?>" href="<?php echo base_url('/welcome/change_location/' . $l['id']); ?>"> 
            <i class="fa-solid fa-fw fa-location-dot" style="color:<?php echo $l['color']; ?>"></i>
					  <?php echo $l['name'] ?>
					</a>
					<?php endforeach; ?>
				</div>
			<!-- <div class="btn-group mr-2" role="group" aria-label="Second group">
				<button type="button" class="btn btn-danger" data-dismiss="modal">None</button>	
			</div> -->
		</div>
      </div>
    </div>
  </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function(){
  $('#staticBackdrop').modal('show');
});
</script>