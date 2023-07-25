<style>
.dropbox{
    border:2px dashed #d3d8e8;
}
.drag-over{
    border:2px dashed #6984da;
	color:#6984da;
}
</style>
<?php if($event_info['no_history'] == 0): ?>
	<form action="<?php echo base_url(); ?>events_report/update_report/<?php echo $event_id; ?>" method="post" autocomplete="off">

		<div class="form row">
			<div class="form-group col-md-6">
				<label for="title_field"><?php echo $this->lang->line('title'); ?></label>
				<input type="text" name="title" class="form-control" value="<?php echo $event_info['title']; ?>" id="title_field">
			</div>
			<div class="form-group px-3">
				<label for="mainVet"><?php echo $this->lang->line('vet'); ?></label>
				<div class="input-group">
					<input type="text" type="text" id="mainVet" class="form-control" value="<?php echo $event_info['vet']['first_name']; ?>" disabled>
					<div class="input-group-append">
						<button name="show_extra_vets" id="show_extra_vets" class="btn btn-outline-success bounceit"><i class="fa-solid fa-user-plus"></i></button>
					</div>
				</div>
			</div>
			<div class="form-group col-md-4 <?php echo ($event_info['vet_1_sup'] || $event_info['vet_2_sup']) ? '' : 'collapse' ; ?>" id="extra_vet" >
				<label for="mainVet"><?php echo $this->lang->line('vet'); ?></label>
				<select name="sup_vet[]" style="width:100%" id="supp_vet_1" data-allow-clear="1" multiple="multiple">
					<?php if($event_info['vet_1_sup']): ?>
					<option value="<?php echo $event_info['vet_1_sup']['id']; ?>" selected><?php echo $event_info['vet_1_sup']['first_name']; ?></option>
					<?php endif; ?>
					<?php if($event_info['vet_2_sup']): ?>
					<option value="<?php echo $event_info['vet_2_sup']['id']; ?>" selected><?php echo $event_info['vet_2_sup']['first_name']; ?></option>
					<?php endif; ?>
				</select>
			</div>
		</div>
		
		<div class="form-group">
		<label for="anamnese"><?php echo $this->lang->line('report'); ?></label>
		<textarea class="form-control" name="anamnese" id="anamnese" rows="12"><?php echo (empty($event_info['anamnese'])) ? $autotemplate : nl2br($event_info['anamnese']); ?></textarea>
		<small id="autosave_anamnese"><?php echo $this->lang->line('last_update') ?> : <?php echo timespan(strtotime($event_info['updated_at']), time(), 1) . ' '. $this->lang->line('ago'); ?></small>
		</div>
		
		<hr />
		<input type="hidden" name="pet_id" value="<?php echo $pet['id']; ?>" />

		<p class="text-right">
		<!-- <button value="report" class="btn btn-outline-success"><i class="fas fa-save" ></i> Save Report</button> -->
		<button class="btn btn-outline-primary" type="submit" name="submit" value="report"><i class="fas fa-save fa-fw"></i> <?php echo $this->lang->line('store'); ?></button>
		</p>
	</form>
<?php endif; ?>



<script>
/*
	work on users
*/

document.addEventListener("DOMContentLoaded", function(){

// autosave timer
var t;
var HasChanged = false;

$('#anamnese').trumbowyg({

	btns: [
        ['strong', 'em'],
        ['undo', 'redo'],
        ['unorderedList'],
    ],
	autogrow: true,
})
.on('tbwchange', function(){ HasChanged = true; });
// auto update
setInterval(function() {
  if (HasChanged) {	
    $.post('<?php echo base_url('events_report/anamnese/' . $event_info['id']); ?>', 
	{ 
		title: $('#title_field').val(), 
		anamnese: $('#anamnese').trumbowyg('html')
    });
	$("#autosave_anamnese").html("<i class='far fa-save'></i> " + new Date().toTimeString().split(" ")[0]);
    HasChanged = false;
  }
}, 3*1000);

// Check for unsaved data
window.onbeforeunload = function() {
  if (HasChanged) {
    return 'There are unsaved changes. Are you sure you want to leave?';
  }
}

$(".file_line")
	.on( "click", function(e) {
			var id = $(this).attr('id').split("_")[1];
			$.ajax({
			  url: "<?php echo base_url("files/delete_file/"); ?>" + id,
				cache: false
			}).done(function() {
				$("#upload_" + id).toggle();
			});
	});

/* if upload by click */
$("#my_old_browser")
	.on( "change", function(e) {

			$("#upload_field").addClass('drag-over').html("");

			var filelist = $(this)[0].files;
			var file;

			for(var i=0;i<filelist.length;i++){
					var file = filelist[i];
					var file_loader =
						'<h4 class="small font-weight-bold m-3">'+file.name+'<span class="float-right" id="text_status_' + file.name + '">&nbsp;</span></h4>' +
						'<div class="progress m-3"><div id="bar_' + file.name + '" class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div></div>';
					$("#upload_field").append(file_loader);
			}

			// loop through files
			for (var i = 0; i < filelist.length; i++) {
			    file = filelist[i];
			    uploadFile(file);
			}

	});

/* add files to event */
$("#upload_field")
  .on("click", function() {
    $("#my_old_browser").click();
  })
  .on("dragover dragenter", function(e) {
    $(this).addClass('drag-over');
    e.preventDefault();
    e.stopPropagation();
  })
  .on("dragleave", function(e) {
    $(this).removeClass('drag-over');
    e.preventDefault();
    e.stopPropagation();
  })
  
  .on("drop", function(e) {
    $(this).addClass('drag-over');
    $(this).html("");

    e.preventDefault();
    e.stopPropagation();

    if (e.originalEvent.dataTransfer && e.originalEvent.dataTransfer.files.length) {
      var filelist = e.originalEvent.dataTransfer.files;
      for (var i = 0; i < filelist.length; i++) {
        var file = filelist[i];
        var file_loader =
          '<h4 class="small font-weight-bold m-3">' + file.name + '<span class="float-right" id="text_status_' + file.name + '">&nbsp;</span></h4>' +
          '<div class="progress m-3"><div id="bar_' + file.name + '" class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div></div>';
        $(this).append(file_loader);
        uploadFile(file);
      }
    }
  });

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
	// console.log(response);
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


$("#show_extra_vets").on("click", function() {
	event.preventDefault();
	$('#extra_vet').collapse('toggle');
	$(this).toggleClass('btn-outline-success btn-outline-danger');
});

/* populate supporting vets */
$('#supp_vet_1').select2({
	maximumSelectionLength: 2,
	theme: 'bootstrap4',
	placeholder: 'Select vet',
  ajax: {
    url: '<?php echo base_url(); ?>vet/ajax_get_vets',
    dataType: 'json'
  },
});

});
</script>
