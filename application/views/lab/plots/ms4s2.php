<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@1.4.0"></script>

<div class="row">
    <div class="col-4" style="height:250px;">
        <canvas id="chart-wbc"></canvas>
		<div class="small text-muted mt-1">
    <span class="text-danger">|</span> Mono start -> end
</div>
    </div>
    <div class="col-4" style="height:250px;">
        <canvas id="chart-rbc"></canvas>
    </div>
    <div class="col-4" style="height:250px;">
        <canvas id="chart-thr"></canvas>
    </div>
</div>

<?php
$meta = json_decode($lab_info['metadata'], true);
$markers = [];

if (isset($meta['markers']) && is_array($meta['markers'])) {
    $markers = $meta['markers'];
}
?>

<script>
const labPlots = <?= json_encode($plots); ?>;
const labMarkers = <?= json_encode($markers); ?>;
</script>

<script src="<?= base_url('assets/js/lab.charts.js'); ?>"></script>