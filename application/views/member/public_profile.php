<div class="row">
	<div class="col-lg-12">
		<div class="card mb-4">
			<div class="card-header">
			<a href="<?php echo base_url('/') ?>">Home</a> / <?php echo $this->lang->line('vet') ?> / <?php echo $profile['first_name'] . " " . $profile['last_name']; ?>
			</div>
			<div class="card-body">
				<div class="row">
					<div class="col">
						<img class="img-profile rounded" src="<?php echo base_url() . 'assets/public/' . (!empty($profile['image']) ? $profile['image'] : 'unknown.jpg' ) ; ?>" />
					</div>
					<div class="col">
					<table class="table">
						<tr>
							<td><?php echo $this->lang->line('email') ?></td>
							<td><?php echo $profile['email']; ?></td>
						</tr>
						<tr>
							<td><?php echo $this->lang->line('phone') ?></td>
							<td><?php echo ($profile['phone']) ? $profile['phone'] : '---'; ?></td>
						</tr>
						<tr>
							<td><?php echo $this->lang->line('last_login') ?></td>
							<td><?php echo date($user->user_date, $profile['last_login']); ?><br/>
								<small><?php echo timespan($profile['last_login'], time(), 1);?> <?php echo $this->lang->line('ago'); ?></small>	
							</td>
						</tr>
						<tr>
							<td><?php echo $this->lang->line('sidebar') ?></td>
							<td><svg width="20" height="20"><rect width="20" height="20" style="fill:<?php echo $profile['sidebar'] ?>;stroke-width:1;stroke:rgb(0,0,0)" /></svg></td>
						</tr>
						<tr>
							<td><?php echo $this->lang->line('event_count') ?></td>
							<td><?php echo $event_count;?><br/><small><?php echo $this->lang->line('event_count_explain'); ?></td>
						</tr>
					</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>