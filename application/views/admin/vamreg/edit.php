<div class="card shadow mb-4">
	<div class="card-header d-flex flex-row align-items-center justify-content-between">
		<div>Admin / Vamreg / in / edit</div>
	</div>
	<div class="card-body">
    <form action="<?php echo base_url('vamreg/save/' . $entry['id'] . '/' . $year . '/' . $quarter); ?>" method="post" autocomplete="off">
        <div class="form-row mb-3">
            <div class="col">
                <label for="exampleFormControlInput3">Volume</label>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" name="new_volume" id="sell" value="<?php echo $entry['in_quantity_pack_count'] ?>">
                </div>
            </div>
            <div class="col">
                <label for="exampleFormControlInput3">Product</label>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" disabled value="<?php echo $entry['wholesale']['description'] ?>">
                </div>
                <label for="exampleFormControlInput3">CNK</label>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" disabled value="<?php echo $entry['cnk'] ?>">
                </div>
                <label for="exampleFormControlInput3">Delivery</label>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" disabled value="<?php echo $entry['delivery'] ?>">
                </div>
            </div>
        </div>
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
