<div class="card shadow mb-4">
	<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
	Report
		<div class="dropdown no-arrow">
			
			<?php if($event_info['no_history'] == 1): ?>
			<a href="<?php echo base_url(); ?>events/enable_history/<?php echo $event_id; ?>" role="button" id="dropdownMenuLink">
				<i class="fas fa-eye"></i>
			</a>
			<?php else: ?>
			<a href="<?php echo base_url(); ?>events/disable_history/<?php echo $event_id; ?>" role="button" id="dropdownMenuLink">
				<i class="fas fa-eye-slash"></i>
			</a>
			<?php endif; ?>
		</div>
	</div>
	<?php if($event_info['no_history'] == 0): ?>
	<div class="card-body">
		<form action="<?php echo base_url(); ?>events/update_report/<?php echo $event_id; ?>" method="post" autocomplete="off">
		 
			<div class="form-row">
			<div class="col-md-2">
				<div class="form-group">
					  <label>Type :</label>
				  <select name="type" style="width:100%" id="select_type" data-allow-clear="1">
					<option id="0"></option>
				 </select>
				</div>
			</div>
			
			
			<div class="col">
			  <div class="form-group">
				<label for="exampleFormControlInput3">Title :</label>
				<input type="text" name="title" class="form-control" value="<?php echo $event_info['title']; ?>" id="exampleFormControlInput3">
			  </div>
			</div>
		  </div>
		  
		  <div class="form-group">
			<label for="anamnese">Report</label>
			<textarea class="form-control" name="anamnese" id="anamnese" rows="12"><?php echo $event_info['anamnese']; ?></textarea>
			<small>last update : <?php echo timespan(strtotime($event_info['updated_at']), time(), 1); ?> Ago</small>
		  </div>
		  
		<div class="form-row py-2">
			<div class="col">
			  <div class="dropbox" id="upload_field">
				<p class="text-center"><i class="fas fa-cloud-upload-alt fa-3x m-2"></i></p>
			   </div>
			</div>
			<?php if($event_uploads): ?>
			<div class="col">
				<?php foreach($event_uploads as $upload): ?>
					<a href="<?php echo base_url(); ?>files/get_file/<?php echo $upload['id']; ?>"><?php echo $upload['filename']; ?></a><br/>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
		</div>
		
		  <button type="submit" name="submit" value="report" class="btn btn-outline-success"><i class="fas fa-save"></i> Save</button>
		</form>
	</div>
	<?php endif; ?>
</div>

<style>
.dropbox{
    border:2px dashed #d3d8e8;
}
.drag-over{
    border:2px dashed #6984da;
	color:#6984da;
}
</style>
<script>
/*
	work on users
*/

document.addEventListener("DOMContentLoaded", function(){

/* type images + text + searchable (perhaps overkill) */
function formatState (state) {
  if (!state.id) {
    return state.text;
  }
  var $state = $(
    '<span><i class="' + state.title + '"></i> ' + state.text + '</span>'
  );
  return $state;
};

var data = [
		{ id: 0, text: "Algemene Controle", title:"fas fa-user-md"},
		{ id: 1, text: "Vaccinatie", title:"fas fa-syringe"},
		{ id: 2, text: "Tanden", title:"fas fa-tooth"},
		{ id: 3, text: "Hospitalisatie", title:"fas fa-hospital"},
		{ id: 4, text: "Operatie", title:"fas fa-hammer"},
		{ id: 5, text: "EKG", title:"fas fa-heartbeat"},
	];

$("#select_type").select2({
	placeholder: 'Select type',
	data: data,
	templateResult: formatState
});

<?php if (!empty($event_info['type'])): ?>
$('#select_type').val('<?php echo $event_info["type"]; ?>');
$('#select_type').trigger('change'); 
<?php endif; ?>

/* add files to event */

$("#upload_field")
	.on( "dragover", function(e) {
		$(this).addClass('drag-over');
		e.preventDefault();
		e.stopPropagation();
	})
	.on( "dragenter", function(e) {
		$(this).addClass('drag-over');
		e.preventDefault();
		e.stopPropagation();
	})
	.on( "dragleave", function(e) {
		$(this).removeClass('drag-over');
		e.preventDefault();
		e.stopPropagation();
	})
	.on( "drop", function(e) {
		$(this).addClass('drag-over');
		$(this).html("");
		
		e.preventDefault();
		e.stopPropagation();
				
		if(e.originalEvent.dataTransfer && e.originalEvent.dataTransfer.files.length) {
			var filelist = e.originalEvent.dataTransfer.files;
			for(var i=0;i<filelist.length;i++){
					var file = filelist[i];
					var file_loader = 
						'<h4 class="small font-weight-bold m-3">'+file.name+'<span class="float-right" id="text_status_' + file.name + '">&nbsp;</span></h4>' +
						'<div class="progress m-3"><div id="bar_' + file.name + '" class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div></div>';
					$(this).append(file_loader);
			}
			for(var i=0;i<filelist.length;i++){
					var file = filelist[i];
					uploadFile(file);
			}
		}
			
	})
;

async function uploadFile(file) {
	// const chunkSize = 40000;
	const chunkSize = 40000;
	const url = '<?php echo base_url(); ?>files/new_file_event/<?php echo $event_id; ?>?nocache=' + new Date().getTime();
	const url_complete = '<?php echo base_url(); ?>files/new_file_event_complete/<?php echo $event_id; ?>';
	
	/*
	 file names have a . (dot) in their name making them not selectable
	*/
	var progress = $('div[id="bar_' + file.name + '"]');
	var text_status = $('span[id="text_status_' + file.name + '"]');
	
	var total = (file.size/chunkSize);
	var procent_value = 0;
	
	for (let start = 0; start < file.size; start += chunkSize) {
		const chunk = file.slice(start, start + chunkSize + 1);
		const fd = new FormData();
		fd.append('data', chunk);
		fd.append('file_name', file.name);
		fd.append('file_size', file.size);
		
		/* send file chunk */
		var respons = await fetch(url, {method: 'POST', body: fd}).then(res => res.text());
		
		/* calculate & visualize progress */
		procent_value = Math.ceil((((start + chunkSize)/chunkSize)/total) * 100);
		progress.attr('aria-valuenow', procent_value).css('width', procent_value +'%');
	}
	
	/* send file completion */
	const compl = new FormData();
		compl.append('file_name', file.name);
		compl.append('file_size', file.size);
		
	/* send file chunk */
	var response = await fetch(url_complete, {method: 'POST', body: compl}).then(response => response.json());
	console.log(response);
	if (response.success)
	{
		text_status.html("Complete!");
		progress.addClass("bg-success");
	}
	else
	{
		text_status.html(response.error);
		progress.addClass("bg-danger");
	}
}


});
</script>

