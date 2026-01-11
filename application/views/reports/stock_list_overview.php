<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
		  <div class="card-header d-flex flex-row align-items-center justify-content-between">
                <div>
                    <a href="<?php echo base_url('accounting/dashboard'); ?>"><?php echo $this->lang->line('admin'); ?></a> / <?php echo $this->lang->line('stock_list'); ?>
                </div>
            </div>
            <div class="card-body">
			<?php echo $this->lang->line('stock_list_explain'); ?>
			<br/>
			<?php if ($locations): ?>
				<table class="table" id="dataTable">
				<thead>
				<tr>
					<th><?php echo $this->lang->line('location'); ?></th>
					<th><?php echo $this->lang->line('export'); ?></th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($locations as $loc): ?>
				<tr>
					<td><?php echo $loc['name']; ?></td>
					<td><a href="<?php echo base_url(); ?>reports/stock_list/1/<?php echo $loc['id']; ?>" class="btn btn-outline-success btn-sm"><i class="fa-regular fa-file-excel"></i> <?php echo $this->lang->line('export'); ?></a></td>
				</tr>
				<?php endforeach; ?>
				<!-- all -->
				<tr>
					<td>All</td>
					<td>
						<a href="<?php echo base_url('reports/stock_list/1'); ?>" class="btn btn-outline-success btn-sm"><i class="fa-regular fa-file-excel"></i> <?php echo $this->lang->line('export'); ?></a><br/><br/>
						<a href="<?php echo base_url('reports/stock_list/1/0/6'); ?>" class="btn btn-outline-info btn-sm"><i class="fa-regular fa-file-excel"></i> 6%</a>
						<a href="<?php echo base_url('reports/stock_list/1/0/21'); ?>" class="btn btn-outline-info btn-sm"><i class="fa-regular fa-file-excel"></i> 21%</a><br/>
                        <div class="alert alert-danger mt-2" role="alert">
                            Als de inkoopprijs 0 is -voor de volledige verpakking-, wordt er gerekend met 0,01 om fouten te voorkomen. De prijs per INKOOPPRIJS_STUK kan nog steeds 0 zijn als de afronding te groot is.  
                            <pre>INKOOPPRIJS_STUK = (aantal producten per verpakking / verkoopsprijs verpakking)</pre>
                        </div>
					</td>
				</tr>
				<tr>
					<td><?php echo $this->lang->line('Clients'); ?></td>
					<td>
						<!-- required for end-of-year book keeping -->
						<a href="<?php echo base_url('reports/clients_list/1'); ?>" class="btn btn-outline-danger btn-sm"><i class="fa-regular fa-file-excel"></i> <?php echo $this->lang->line('active_clients'); ?></a><br/>
						<small><i><?php echo $this->lang->line('active_clients_explain'); ?></i><br/></small><br/>
						<a href="<?php echo base_url('reports/clients_list'); ?>" class="btn btn-outline-danger btn-sm"><i class="fa-regular fa-file-excel"></i> <?php echo $this->lang->line('export_client_all'); ?></a>
					</td>
				</tr>
				</tbody>
				</table>
			<?php endif; ?>
                </div>
		</div>

	</div>
      
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#admin").addClass('active');
});
</script>
  