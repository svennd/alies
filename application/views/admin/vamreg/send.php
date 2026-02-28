<?php include 'blocks/menu.php'; ?>
<div class="card shadow mb-4">
    <div class="card-header d-flex flex-row align-items-center justify-content-between">
        <div>Vamreg / send</div>
        <div>
            <a href="<?= base_url("vamreg/out/$prevY/$prevQ") ?>"
            class="btn btn-outline-success btn-sm">
                <i class="fas fa-angle-double-left fa-fw"></i>
                Q<?= $prevQ ?> <?= $prevY ?>
            </a>

            <span class="mx-2 font-weight-bold">
                Q<?= $quarter ?> <?= $year ?>
            </span>

            <?php if (!$isCurrentQuarter): ?>
                <a href="<?= base_url("vamreg/out/$nextY/$nextQ") ?>"
                class="btn btn-outline-success btn-sm">
                    Q<?= $nextQ ?> <?= $nextY ?>
                    <i class="fas fa-angle-double-right fa-fw"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
	<div class="card-body">

 	</div>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#admin").addClass('active');
	$("#dataTable").DataTable();
	$("#vamreg-send").addClass('active');
});
</script>