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
	
	<?php include "report/block_report.php"; ?>
</div>

