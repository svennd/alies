<?php 

    # catch 0 division
    $omzet_vorig_jaar                 = ($yearly_earnings_ly == 0 ) ? 1 : $yearly_earnings_ly;
    $omzet_dit_jaar                   = ($yearly_earnings == 0 ) ? 1 : $yearly_earnings;
    $klanten_contacten                = ($client_contacts_year == 0 ) ? 1 : $client_contacts_year;
    $klanten_contacten_vorig_jaar     = ($client_contacts_year_ly == 0 ) ? 1 : $client_contacts_year_ly;

    $str_monthly = ($month == 0) ? $this->lang->line('monthly') : $current_date->Format('F');

    
    $procent_dit_jaar_voorbij = (365 / (((int) date("z") == 0) ? 1 : (int) date("z")));
    $predictie_omzet_dit_jaar = $procent_dit_jaar_voorbij * $omzet_dit_jaar;
    $predictie_klanten_dit_jaar = $procent_dit_jaar_voorbij * $klanten_contacten;
    
    $p = (($predictie_omzet_dit_jaar - $omzet_vorig_jaar)/$predictie_omzet_dit_jaar)*100;
    $y = (($predictie_klanten_dit_jaar - $klanten_contacten_vorig_jaar)/$predictie_klanten_dit_jaar)*100;
?>


<div class="row <?php echo ($user->vsens) ? '' : 'sensitive'; ?>">
    <div class="col-lg-6 col-xl-3 mb-4">
        <div class="card border-left-success text-black h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="me-3">
                        <div class="text-black-50 small"><?php echo $this->lang->line('earnings'); ?> (<?php echo $str_monthly; ?>)</div>
                        <div class="text-lg fw-bold"><a href="<?php echo base_url('reports/revenue'); ?>">&euro; <?php echo number_format($monthly_earnings, 0, ',', '.'); ?></a></div>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-dollar-sign feather-xl text-black-50"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-xl-3 mb-4">
        <div class="card border-left-primary text-black h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="me-3">
                        <div class="text-black-75 small"><?php echo $this->lang->line('earnings'); ?> (<?php echo $this->lang->line('annual'); ?>)</div>
                        <div class="text-lg fw-bold">&euro; <?php echo number_format($yearly_earnings, 0, ',', '.'); ?></div>
                        <div class="text-xs fw-bold d-inline-flex align-items-center">
                            <?php if($omzet_vorig_jaar != 1 && $p >= 0.5): ?>
                                <span class="text-success">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                                            <polyline points="17 6 23 6 23 12"></polyline>
                                    </svg>
                                    <?php echo round($p,0); ?>% (<?php echo $this->lang->line('est'); ?>)
                                </span>
                            <?php elseif($omzet_vorig_jaar != 1 && $p <= -0.5): ?>
                                <span class="text-danger">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline>
                                        <polyline points="17 18 23 18 23 12"></polyline>
                                    </svg>
                                    <?php echo abs(round($p,0)); ?>% (<?php echo $this->lang->line('est'); ?>)
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-black-50"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-xl-3 mb-4">
        <div class="card border-left-success text-black h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="me-3">
                        <div class="text-black-50 small"><?php echo $this->lang->line('events'); ?> (<?php echo $str_monthly; ?>)</div>
                        <div class="text-lg fw-bold"><?php echo $client_contacts; ?></div>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-square feather-xl text-black-50"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-xl-3 mb-4">
        <div class="card border-left-primary text-black h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="me-3">
                        <div class="text-black-75 small"><?php echo $this->lang->line('events'); ?> (<?php echo $this->lang->line('annual'); ?>)</div>
                        <div class="text-lg fw-bold"><?php echo $client_contacts_year; ?></div>
                        <div class="text-xs fw-bold d-inline-flex align-items-center">
                            <?php if($klanten_contacten_vorig_jaar != 1 && $y > 0): ?>
                                <span class="text-success">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                                            <polyline points="17 6 23 6 23 12"></polyline>
                                    </svg>
                                    <?php echo round($y,0); ?>% (<?php echo $this->lang->line('est'); ?>)
                                </span>
                            <?php elseif($klanten_contacten_vorig_jaar != 1 && $y < 0): ?>
                                <span class="text-danger">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline>
                                        <polyline points="17 18 23 18 23 12"></polyline>
                                    </svg>
                                    <?php echo abs(round($y,0)); ?>% (<?php echo $this->lang->line('est'); ?>)
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-square feather-xl text-black-50"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row <?php echo ($user->vsens) ? '' : 'sensitive'; ?>">
    <div class="col-lg-8 mb-4">
        <div class="card shadow mb-4 h-100">
                <div class="card-header d-flex flex-row align-items-center justify-content-between">
                    <div><?php echo $this->lang->line('earnings_overview'); ?></div>
                    <div class="dropdown no-arrow">
                        <?php $current_date->modify('-1 month'); ?>
                        <a href="<?php echo base_url('accounting/dashboard/' . ($month-1)); ?>" class="btn btn-outline-success btn-sm"><i class="fas fa-angle-double-left fa-fw"></i> <?php echo $current_date->format('F'); ?></a>
                        <?php if ($month < 0): ?>
                            <?php $current_date->modify('+2 month'); ?>
                            <a href="<?php echo base_url('accounting/dashboard/' . ($month+1)); ?>" class="btn btn-outline-success btn-sm"><i class="fas fa-angle-double-right fa-fw"></i> <?php echo $current_date->format('F'); ?></a>
                        <?php else: ?>
                            <?php $current_date->modify('+1 month'); ?>
                            <a href="#" class="btn btn-outline-secondary btn-sm"><i class="fas fa-home fa-fw"></i> <?php echo $current_date->format('F'); ?></a>
                        <?php endif; ?>
                        
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="myChart"></canvas>
                    </div>
                    <br/>
                    <input type="button" id="start_from_zero" class="btn btn-light btn-sm" style="color:#3080d0" name="button" value="Start (0)" />
                </div>
            </div>
	</div>
    <div class="col-lg-4 mb-4">
        <div class="card shadow mb-4 h-100">
                <div class="card-header d-flex flex-row align-items-center justify-content-between">
                    <div><?php echo $this->lang->line('revenue_source'); ?> (<?php echo $str_monthly; ?>)</div>
                    <div class="dropdown no-arrow">
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4">
                        <?php if($distribution_proc_prod['products']['total'] > 0 || $distribution_proc_prod['procedures'] > 0): ?>
                        <canvas id="myPieChart"></canvas>
                        <button type="button" class="btn btn-light btn-sm" style="color:#2596be;background-color:rgba(30,129,176,0.2);">6%</button>
                        <button type="button" class="btn btn-light btn-sm" style="color:#e28743;background-color:rgba(226,135,67,0.2);">21%</button>
                        <?php else: ?>
                            <?php echo $this->lang->line('no_data'); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
	</div>
</div>

<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card shadow mb-4 h-100">
            <div class="card-header d-flex flex-row align-items-center justify-content-between">
                <div><?php echo $this->lang->line('admin_tools'); ?></div>
                <div class="dropdown no-arrow">
                </div>
            </div>
            <div class="card-body">
                <div style="display:flex; flex-wrap: wrap; gap: 1em;">
                <?php 
    $tiles = array(
                array(
                    'img' => 'stock_error.png',
                    'alt' => 'stock_error',
                    'link' => 'stock/stock_check',
                    'text' => $this->lang->line('stock_error') . ' (' . $stock_error_count . ')',
                ),
                array(
                    'img' => 'wholesale.png',
                    'alt' => 'wholesale',
                    'link' => 'import/import_covetrus',
                    'text' => $this->lang->line('wholesale'),
                ),
                array(
                    'img' => 'settings.png',
                    'alt' => 'settings',
                    'link' => 'admin/settings',
                    'text' => $this->lang->line('settings'),
                ),
                array(
                    'img' => 'breeds.png',
                    'alt' => 'breeds',
                    'link' => 'breeds',
                    'text' => $this->lang->line('breeds'),
                ),
                array(
                    'img' => 'stock_list.png',
                    'alt' => 'stock_list',
                    'link' => 'reports/stock_list',
                    'text' => $this->lang->line('stock_list'),
                ),
                array(
                    'img' => 'locations.png',
                    'alt' => 'locations',
                    'link' => 'admin/locations',
                    'text' => $this->lang->line('locations'),
                ),
                array(
                    'img' => 'types.png',
                    'alt' => 'types',
                    'link' => 'admin/product_types',
                    'text' => $this->lang->line('product_types'),
                ),
                array(
                    'img' => 'backup.png',
                    'alt' => 'backup',
                    'link' => 'backup',
                    'text' => $this->lang->line('backup'),
                ),
                array(
                    'img' => 'booking.png',
                    'alt' => 'booking',
                    'link' => 'admin/booking',
                    'text' => $this->lang->line('booking_codes'),
                ),
                array(
                    'img' => 'members.png',
                    'alt' => 'members',
                    'link' => 'member',
                    'text' => $this->lang->line('user_mgm'),
                ),
                array(
                    'img' => 'stats.png',
                    'alt' => 'stats',
                    'link' => 'stats',
                    'text' => $this->lang->line('stats'),
                ),
                
    );
?>

<?php foreach($tiles as $tile): ?>
    <div style="width:190px;">
        <div class="card h-100 p-1">
            <img src="<?php echo base_url('assets/img/' . $tile['img']); ?>" class="card-img-top" alt="<?php echo $tile['alt']; ?>">
            <div class="card-body text-center">
                <a href="<?php echo base_url($tile['link']); ?>" class="stretched-link"><?php echo $tile['text']; ?></a>
            </div>
        </div>
    </div>
<?php endforeach; ?>

                </div>
            </div> 
        </div>
	</div>
    <div class="col-lg-4 mb-4">
        <div class="card shadow mb-4 h-100">
                <div class="card-header d-flex flex-row align-items-center justify-content-between">
                    <div><?php echo $this->lang->line('logs'); ?> (warnings)</div>
                    <div class="dropdown no-arrow">
                        <a href="<?php echo base_url('logs/nlog'); ?>" class="btn btn-outline-success btn-sm"><i class="fas fa-stream"></i> <?php echo $this->lang->line('global_logs'); ?></a>
                        <a href="<?php echo base_url('logs'); ?>" class="btn btn-outline-info btn-sm"><i class="fas fa-stream"></i> <?php echo $this->lang->line('other_logs'); ?></a>
                    </div>
                </div>
                <div class="card-body overflow-auto" style="height:150px;">
                    <?php if($logs): ?>
                    <table class="table table-sm">
                    <?php foreach($logs as $log): ?>
                        <tr>
                            <td><?php echo time_ago($log['created_at']); ?></td>
                            <td><?php echo $log['event']; ?></td>
                            <td><?php echo isset($log['location']['name']) ? $log['location']['name'] : '-'; ?></td>
                            <td><?php echo isset($log['vet']['first_name']) ? $log['vet']['first_name'] : '-'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
	</div>
</div>

<?php
$data = "";
$months = "";
foreach ($yearly_by_month as $y)
{
    $months .= "'" . DateTime::createFromFormat('d-n-Y', '01-'. $y['m'] .'-'. $y['y'])->format('F') . "',";
    $data .= round($y['total'], 0) . ',';
}


$data_ly = "";
foreach ($yearly_by_month_last_year as $y)
{
    $data_ly .= round($y['total'], 0) . ',';
}
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#dataTable").DataTable();
	$("#adminmgm").show();
	$("#admin").addClass('active');
	$("#adminlocation").addClass('active');

// speedometer chart

<?php if($distribution_proc_prod['products'] > 0 || $distribution_proc_prod['procedures'] > 0): ?>
var ctx = document.getElementById("myPieChart");


const footer = (tooltipItems) => {
  let procent = 0;
  procent = Math.round(tooltipItems[0].parsed/<?php echo round($distribution_proc_prod['total'], 0); ?>*100);
  return procent + ' %';
};

var myPieChart = new Chart(ctx, {
  type: 'doughnut',
  data: {
    labels: ["products", "procedures"],
    datasets: [
    {
      data: [<?php echo round($distribution_proc_prod['products']['total'], 0); ?>, <?php echo round($distribution_proc_prod['procedures'], 0); ?>],
      backgroundColor: ['#4e73df', '#1cc88a'],
      hoverBackgroundColor: ['#2e59d9', '#17a673'],
      hoverBorderColor: "rgba(234, 236, 244, 1)",
    },
    {
      data: [<?php echo ((isset($distribution_proc_prod['products']['6'])) ? $distribution_proc_prod['products']['6'] : 0) . "," . ((isset($distribution_proc_prod['products']['21'])) ? $distribution_proc_prod['products']['21'] : 0); ?>, <?php echo round($distribution_proc_prod['procedures'], 0); ?>],
      backgroundColor: ['#2596be', '#e28743', '#fff'],
      hoverBackgroundColor: ['#2596be', '#e28743', '#fff']
    },
    ],
  },
  options: {
    maintainAspectRatio: false,
    rotation: -90,
    circumference: 180,
    plugins: {
        tooltip: {
            callbacks: {
            footer: footer,
            }
        }
    }
  },
});
<?php endif; ?>

// line chart
const data = {
  labels: [<?php echo $months; ?>],
  datasets: [
    { label: '<?php echo $this->lang->line('this_year'); ?>', data: [<?php echo $data; ?>], fill: 'start', tension: 0.2, borderColor:"#36b9cc", backgroundColor:"rgba(44, 159, 175, 0.3)"},
    { label: '<?php echo $this->lang->line('last_year'); ?>', data: [<?php echo $data_ly; ?>], fill: 'start', tension: 0.2, borderColor:"rgba(175, 44, 61, 0.1)", backgroundColor:"rgba(175, 44, 61, 0.1)"},
  ]
};

const config = {
  type: 'line',
  data: data,
  options: {
    maintainAspectRatio: false,
    responsive: true,
  },
};

const myChart = new Chart(
    document.getElementById('myChart'),
    config
  );

$("#start_from_zero").click(function() {
    myChart.options.scales = { y: { min: 0}};
    myChart.update();
});

});
</script>