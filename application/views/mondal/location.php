<div class="modal fade" id="staticBackdrop" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="staticBackdropLabel">Select Location</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
			<p>Please select a location</p>
			<div class="btn-toolbar" role="toolbar" aria-label="Toolbar with button groups">
				<div class="btn-group mr-2" role="group" aria-label="First group">
					<?php foreach($location as $l) : ?>
					<a class="btn btn-success" href="<?php echo base_url() . '/welcome/change_location/' . $l['id']; ?>"> 
					<?php echo $l['name'] ?>
					</a>
					<?php endforeach; ?>
				</div>
			<div class="btn-group mr-2" role="group" aria-label="Second group">
				<button type="button" class="btn btn-danger" data-dismiss="modal">None</button>	
			</div>
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