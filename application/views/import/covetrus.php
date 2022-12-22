<style>
.dropbox{
    border:2px dashed #d3d8e8;
}

.dropbox:hover {
    border:2px dashed #6984da;
	color:#6984da;
}

.drag-over{
    border:2px dashed #6984da;
	color:#6984da;
}

</style>


<div class="row">
	<div class="col-lg-12 mb-4">
		<?php if($processed === false): ?>
			<div class="card shadow mb-4">		
				<div class="card-header">
					Upload price list covertrus
				</div>
				<div class="card-body">
					<div class="dropbox d-flex align-items-center" id="upload_field">
						<p class="text-center w-100"><i class="fas fa-cloud-upload-alt fa-3x m-2"></i><br/>Click here or drag the files here.</p>
					</div>
					<input type="file" style="display:none" name="manual_file_upload" id="my_old_browser" multiple />
					
					<h1></h1>
					<form action="<?php echo base_url(); ?>import/import_covetrus" method="post" autocomplete="off">
						<input type="hidden" id="file_upload" name="file" value="" />
						<button type="submit" name="submit" value="1" class="btn btn-primary">Process data</button>
					</form>
				</div>
			</div>

		<?php else: ?>
		<div class="card shadow mb-4">
			<div class="card-header">
				Changes
			</div>
			<div class="card-body">
				Processed : <?php echo $processed; ?> products;
				<table class="table" id="dataTable">
					<thead>
						<tr>
							<th>Art nr.</th>
							<th>Description</th>
							<th>New</th>
							<th>Old</th>
							<th>Diff</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($changes as $x): ?>
						<tr>
							<td><?php echo $x['art_nr']; ?></td>
							<td><?php echo $x['description']; ?></td>
							<td><?php echo $x['new']; ?></td>
							<td><?php echo $x['old']; ?></td>
							<td><?php echo ($x['new']) ? round((($x['new']-$x['old'])/$x['new'])*100).'%' : '-'; ?></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
		<?php endif; ?>
	</div>

	<?php if($processed === false): ?>
	<div class="col-lg-12 mb-4">
		<div class="card shadow mb-4">
			<div class="card-header">
				Product list from wholesale
			</div>
			<div class="card-body">
				<?php if ($product): ?>
				<table class="table" id="dataTable">
					<thead>
						<tr>
							<th>Art nr.</th>
							<th>Description</th>
							<th>Bruto</th>
							<th>btw</th>
							<th>sell price</th>
							<th>distributor</th>
							<th>CNK</th>
							<th>VHB</th>
							<th>last update</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($products as $p): ?>
						<tr>
							<td><?php echo $p['vendor_id']; ?></td>
							<td><?php echo $p['description']; ?></td>
							<td><?php echo $p['bruto']; ?></td>
							<td><?php echo $p['btw']; ?></td>
							<td><?php echo $p['sell_price']; ?></td>
							<td><?php echo $p['distributor']; ?></td>
							<td><?php echo $p['CNK']; ?></td>
							<td><?php echo $p['VHB']; ?></td>
							<td><?php echo date_format(date_create($p['updated_at']), $user->user_date); ?></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<?php else: ?>
					No products in wholesale.
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php endif; ?>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#dataTable").DataTable();
	$("#adminmgm").show();
	$("#admin").addClass('active');
	$("#adminlocation").addClass('active');

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
	const url = '<?php echo base_url(); ?>files/append/<?php echo date('mdy'); ?>?nocache=' + new Date().getTime();
	const url_complete = '<?php echo base_url(); ?>files/file_complete/<?php echo date('mdy'); ?>';

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
		text_status.html(" Complete!");
        console.log(response.file);
        $("#file_upload").val(response.file);
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