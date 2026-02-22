
	<p>
		    <div class="card shadow mb-4">

			<div class="card-header d-flex flex-row align-items-center justify-content-between">
				<div><a href="<?php echo base_url(); ?>tools"><?php echo $this->lang->line('tools'); ?></a> / duplicate client finder</div>
			</div>

          <div class="card-body">
            <div class='alert alert-info' role='alert'>Looks for identical phone numbers (starting with 04*)</div>
              
            <table class="table table-sm" id="dataTable">
				<thead>
				<tr>
                    <th>id</th>
                    <th>first_name</th>
                    <th>last_name</th>
                    <th>phone</th>
                    <th>last_bill</th>
                    <th>merge</th>
				</tr>
				</thead>
                    <?php foreach ($duplicates as $dup): ?>
                        <tr>
                            <td>
                                    <?php 
                                        // IDs 
                                        $ids = explode(",", $dup['owner_ids']);
                                        $count = 0;
                                        foreach($ids as $owner_id): ?>
                                        <a href="<?php echo base_url('owners/detail/' . $owner_id); ?>"><?php echo $owner_id; ?></a>
                                        <?php
                                            $count++;
                                            if($count >= 3) { echo "..."; break; }
                                        endforeach; 
                                        ?>
                            </td>
                            <td><?php echo $dup['first_name']; ?></td>
                            <td><?php echo $dup['last_name']; ?></td>
                            <td><?php echo $dup['phone']; ?></td>
                            <td><?php echo $dup['max_last_bill']; ?></td>
                            <td><a href="<?php echo base_url('tools/merge_clients/' . $ids[1] . "/" . $ids[0]); ?>">merge</a></td>
                        </tr>
                    <?php endforeach; ?>
				</table>
                <div class='alert alert-info' role='alert'>Merge will always merge the newest bill (keep) with the older bill (disable), if multiple records only the first 2 will be merged (newest + newest -1s).</div>
              
                </div>
		</div>
	</p>
	
<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#dataTable").DataTable(
        {
            "order": [[ 5, "desc" ]]
        }
    );
});
</script>
