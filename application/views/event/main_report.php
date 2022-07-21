<style>
.topfields {
	width: 150px;
	padding: 10px 50px;
	font-size: 1.1em;
}

.max {
	height: 100%;
}

.templates {
	width: 100%;
	height: 150px;
}

.templates  .img-fluid {
    /* display: block; */
	margin-top: 5px;
	margin-left: 5px;
    width: auto;
    max-height: 95%;
}

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

.tab-content > #media.tab-pane {
   display: block;
   height:0;
   max-height:0;
   overflow:hidden;
}

.tab-content > #media.active  {
   display: block;
   height:auto;
   max-height: 100%;
}

</style>
<div class="col-lg-12">
	<div class="card shadow mb-4">
		<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
			Report
			<div class="dropdown no-arrow">
				<a href="<?php echo base_url(); ?>invoice/get_bill/<?php echo $event_info['payment']; ?>" class="btn btn-outline-success btn-sm"><i class="fas fa-arrow-right"></i> Show Invoice</a>
				<a href="<?php echo base_url(); ?>invoice/get_bill/<?php echo $event_info['payment']; ?>" class="btn btn-outline-success btn-sm"><i class="fas fa-print"></i> Print Invoice</a>
				<?php if($event_info['no_history'] == 1): ?>
				<a href="<?php echo base_url(); ?>events/enable_history/<?php echo $event_id; ?>" role="button" id="dropdownMenuLink" class="btn btn-outline-primary btn-sm">
				<i class="fas fa-eye"></i> Enable Report
				</a>
				<?php else: ?>
				<a href="<?php echo base_url(); ?>events/disable_history/<?php echo $event_id; ?>" role="button" id="dropdownMenuLink" class="btn btn-outline-primary btn-sm">
				<i class="fas fa-eye-slash"></i> Disable Report
				</a>
				<?php endif; ?>
			</div>
		</div>

		<div class="card-body">
			<?php include 'report/block_report_header.php' ?>
		</div>
	</div>
	<div class="card shadow mb-4">

	<?php $uploaded_files = ($event_uploads) ? '&nbsp;<span class="badge badge-info">' . count($event_uploads) . '</span>' : ''; ?>
	<div class="card-header d-flex flex-row align-items-center justify-content-between">
			<ul class="nav nav-tabs card-header-tabs" id="mynavtab" role="tablist">
			<li class="nav-item" role="presentation"><a class="nav-link active" id="info-tab" data-toggle="tab" href="#info" role="tab" aria-controls="info" aria-selected="true">Report</a></li>
			<li class="nav-item" role="presentation"><a class="nav-link" id="media-tab" data-toggle="tab" href="#media" role="tab" aria-controls="media" aria-selected="false">Media</a></li>
			<li class="nav-item" role="presentation"><a class="nav-link" id="files-tab" data-toggle="tab" href="#files" role="tab" aria-controls="files" aria-selected="false">Files<?php echo $uploaded_files; ?></a></li>
			</ul>
	</div>

	<form action="<?php echo base_url(); ?>events/update_report/<?php echo $event_id; ?>" method="post" autocomplete="off">
	<div class="card-body">
		<div class="tab-content" id="myTabContent">
			<div class="tab-pane active show" id="info" role="tabpanel" aria-labelledby="info-tab">
				<?php include "report/block_report.php"; ?>
			</div>
			<div class="tab-pane" id="media" role="tabpanel" aria-labelledby="media-tab">
				<?php include "report/block_drawing.php"; ?>
			</div>
			<div class="tab-pane" id="files" role="tabpanel" aria-labelledby="files-tab">
				<?php include "report/block_attachments.php"; ?>
			</div>
		</div>

		<hr />
		<input type="hidden" name="pet_id" value="<?php echo $pet['id']; ?>" />
		<button type="submit" name="submit" value="report" class="btn btn-outline-success" id="save_report_submit"><i class="fas fa-save" ></i> Save Report</button>
		<button type="submit" name="submit" value="finished_report" class="btn btn-outline-primary"><i class="fas fa-clipboard-check"></i> Finish</button>
	</div>
	</form>
</div>



</div>

