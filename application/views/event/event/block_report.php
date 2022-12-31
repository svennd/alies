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

		<div class="form-group">
			<label for="title_field">Title :</label>
			<input type="text" name="title" class="form-control" value="<?php echo $event_info['title']; ?>" id="title_field">
		</div>
		
		<div class="form-group">
		<label for="anamnese">Report</label>
		<textarea class="form-control" name="anamnese" id="anamnese" rows="12"><?php echo $event_info['anamnese']; ?></textarea>
		<small id="autosave_anamnese">last update : <?php echo timespan(strtotime($event_info['updated_at']), time(), 1); ?> Ago</small>
		</div>
		
		<hr />
		<div class="form-group row">
		<label for="staticEmail" class="col-sm-2 col-form-label">Vet</label>
		<div class="col-sm-10">
			<input type="text" readonly class="form-control-plaintext" id="staticEmail" value="<?php echo $event_info['vet']['first_name']; ?>">
		</div>
		</div>
		<div class="form-group row">
		<label for="supp_vet_1" class="col-sm-2 col-form-label">Vet 2</label>
		<div class="col-sm-10">
				<select name="supp_vet_1" style="width:100%" id="supp_vet_1" data-allow-clear="1">
					<?php if($event_info['vet_1_sup']): ?>
					<option value="<?php echo $event_info['vet_1_sup']['id']; ?>" selected><?php echo $event_info['vet_1_sup']['first_name']; ?></option>
					<?php endif; ?>
				</select>
		</div>
		</div>
		<div class="form-group row">
		<label for="supp_vet_2" class="col-sm-2 col-form-label">Vet 3</label>
		<div class="col-sm-10">
				<select name="supp_vet_2" style="width:100%" id="supp_vet_2" data-allow-clear="1">
						<?php if($event_info['vet_2_sup']): ?>
						<option value="<?php echo $event_info['vet_2_sup']['id']; ?>" selected><?php echo $event_info['vet_2_sup']['first_name']; ?></option>
						<?php endif; ?>
				</select>
		</div>
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

$('#anamnese').trumbowyg({

    btns: [
        ['strong', 'em', 'fontsize'],
        ['undo', 'redo'],
        ['link'],
        ['unorderedList'],
        ['removeformat'],
        ['fullscreen'],
		['template'],
    ],
	autogrow: true,
    plugins: {
        fontsize: {
            sizeList: [
                '14px',
                '18px',
                '22px'
            ],
            allowCustomSize: false
        },
        templates: [
            {
                name: 'Anamnese_Behandeling',
                html: 'ANAMNESE, ONDERZOEK EN DIAGNOSE:<br/><br/><br/><br/>BEHANDELING:<br/><br/><br/><br/>'
            }
        ]
    }
})
.on('tbwchange', function(){ 
	// autosave
	clearTimeout(t);
	t = setTimeout(function() {
		content = $('#anamnese').trumbowyg('html');
		title = $('#title_field').val();
		$.ajax({
			method: 'POST',
			url: '<?php echo base_url(); ?>events_report/anamnese/' + <?php echo $event_info['id']; ?>,
			data: {
				title: title,
				anamnese: content
			}
			});
		$("#autosave_anamnese").html("<i class='far fa-save'></i> " + new Date().toTimeString().split(" ")[0]);
	}, 750);	
});

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
  .on( "click", function(e) {
			$("#my_old_browser").click();
	})
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

		// console.log(e.originalEvent.dataTransfer);
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

/* populate supporting vets */
$('#supp_vet_1').select2({
	theme: 'bootstrap4',
	placeholder: 'Select vet',
  ajax: {
    url: '<?php echo base_url(); ?>vet/ajax_get_vets',
    dataType: 'json'
  },
});

$('#supp_vet_2').select2({
	theme: 'bootstrap4',
	placeholder: 'Select vet',
  ajax: {
    url: '<?php echo base_url(); ?>vet/ajax_get_vets',
    dataType: 'json'
  },
});

});
</script>
