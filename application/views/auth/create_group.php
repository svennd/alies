<h1 class="h3 mb-0 text-gray-800"><?php echo lang('create_group_heading');?></h1>
<p><?php echo lang('create_group_subheading');?></p>


<div id="infoMessage"><?php echo $message;?></div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
              <h6 class="m-0 font-weight-bold text-primary">Create group</h6>
            </div>
            <div class="card-body">
<?php echo form_open("auth/create_group");?>

      <p>
            <?php echo lang('create_group_name_label', 'group_name');?> <br />
            <?php echo form_input($group_name);?>
      </p>

      <p>
            <?php echo lang('create_group_desc_label', 'description');?> <br />
            <?php echo form_input($description);?>
      </p>

      <p><?php echo form_submit('submit', lang('create_group_submit_btn'));?></p>

<?php echo form_close();?>

<?php if ($group_list) : ?>
<hr />
	<p>Current group(s):</p>
	<ul>
	<?php foreach ($group_list as $group) : ?>
		<li>(#<?php echo $group['id']; ?>) <?php echo $group['name']; ?> : <?php echo $group['description']; ?></li>
	<?php endforeach; ?>
	</ul>
<?php endif; ?>
            </div>
		</div>

<script>
document.addEventListener("DOMContentLoaded", function(){
	$("#usermanagement").show();
	$("#usermgm").addClass('active');
	$("#creategroup").addClass('active');
});
</script>