<div class="card shadow mb-4">
	<div class="card-header py-3">
	  <h6 class="m-0 font-weight-bold text-primary">breeds : rebuild frequenty</h6>
	</div>
	<div class="card-body">
    <div class="alert alert-success" role="alert">
    Breeds frequency has been rebuild! Below is a list of the most frequent breeds over a 5 year period (alive), unknown isn't added to frequency levels (it is only shown here)
    </div>
        <?php 
        foreach ($count as $type => $breed_list): 
            arsort($breed_list);
            $top_5 = array_slice($breed_list, 0, 5, true);
        ?>
           <strong><?php echo get_symbol($type, true); ?></strong><br/>
            <?php foreach($top_5 as $breed => $cnt): ?>
                <?php echo $breed . " (" . $cnt . ")"; ?><br/>
            <?php endforeach; ?>
            <br/>
        <?php endforeach; ?>

	</div>
</div>
<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#adminmgm").show();
	$("#admin").addClass('active');
	$("#adminbreed").addClass('active');
});
</script>