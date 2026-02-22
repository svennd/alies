<div class="card shadow mb-4">
	<div class="card-header d-flex flex-row align-items-center justify-content-between">
		<div>Vamreg / out / edit</div>
	</div>
	<div class="card-body">
    <form action="<?php echo base_url('vamreg/save_out/' . $entry['id'] . '/' . $year . '/' . $quarter); ?>" method="post" autocomplete="off">
        <div class="form-row mb-3">
            <div class="col">
                <label>Volume</label>
				<div class="form-inline">
					<div class="form-group">
                    	<input type="text" class="form-control" name="new_volume" id="sell" value="<?php echo $entry['total_quantity'] ?>" required>
					</div>
					<div class="form-group mx-sm-1">
						<select name="ab_unit" class="form-control" id="ab_unit">
							<option value="PACKS" <?php if($entry['unit'] == 'PACKS') echo 'selected'; ?>>Pack</option>
							<option value="PIECE" <?php if($entry['unit'] == 'PIECE') echo 'selected'; ?>>Piece</option>
							<option value="ML" <?php if($entry['unit'] == 'ML') echo 'selected'; ?>>ml</option>
							<option value="PRESTATION" <?php if($entry['unit'] == 'PRESTATION') echo 'selected'; ?>>prestation</option>
							<option value="TUBE" <?php if($entry['unit'] == 'TUBE') echo 'selected'; ?>>tube</option>
							<option value="G" <?php if($entry['unit'] == 'G') echo 'selected'; ?>>g</option>
						</select>
					</div>
				</div>
            </div>
            <div class="col">
                <label for="exampleFormControlInput3">out_date</label>
                <div class="input-group mb-3">
                    <input type="date" class="form-control" name="out_date" value="<?php echo $entry['out_date'] ?>">
                </div>
            </div>
        </div>
		<input type="hidden" name="cnk" value="<?php echo $entry['cnk']; ?>">
        <button type="submit" name="submit" value="1" class="btn btn-primary">Edit</button>
    </form>
    </div>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#prd").show();
	$("#products").addClass('active');
	$("#stock").addClass('active');
});
</script>
