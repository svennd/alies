<div class="card shadow mb-4">
	<div class="card-header d-flex flex-row align-items-center justify-content-between">
		<div>
        <a href="<?php echo base_url('reports'); ?>">Report</a> /
        <a href="<?php echo base_url('reports/bills'); ?>">Invoices</a> /
         Removed bill
        </div>
	</div>
	<div class="card-body">
        <div class="alert alert-warning" role="alert">note : bill has been removed. The events have been kept, but all products (inc. vaccines + redo dates) & procedures have been removed. (file uploads and title/messages have been kept)</div>
        <?php if($events_from_bill): ?>
            <ul>
            <?php foreach($events_from_bill as $events): ?>
                <li><a href="<?php echo base_url('events/event/'. $events['id']); ?>">Event</a></li>    
            <?php endforeach; ?>
            </ul>
        <?php else: ?>
            no events linked to this bill.
        <?php endif; ?>
    </div>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#prd").show();
	$("#products").addClass('active');
	$("#stock").addClass('active');
});
</script>
