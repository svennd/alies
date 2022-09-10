<div class="card shadow mb-4">
	<div class="card-header d-flex flex-row align-items-center justify-content-between">
		<div><a href="<?php echo base_url(); ?>stock">Stock</a> / <a href="<?php echo base_url('stock/stock_detail/' . $stock['products']['id']); ?>"><?php echo $stock['products']['name']; ?></a></div>
	</div>
	<div class="card-body">
    <form action="<?php echo base_url('stock/edit/' . $stock['id']); ?>" method="post" autocomplete="off">
    <div class="alert alert-warning" role="alert">
    Editing stock directly will not be reflected in writeoff or accounting.
    </div>
        <div class="form-row mb-3">
            <div class="col">
                <label for="exampleFormControlInput3">Volume</label>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" name="new_volume" id="sell" value="<?php echo $stock['volume'] ?>">
                    <div class="input-group-append">
                        <span class="input-group-text"><?php echo $stock['products']['unit_sell']; ?></span>
                    </div>
                </div>
            </div>
            <div class="col">
                <label for="exampleFormControlInput3">State</label><br/>
                <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="state" id="inlineRadio1" value="<?php echo STOCK_IN_USE; ?>" <?php echo ($stock['state'] == STOCK_IN_USE) ? "checked" : ""; ?>>
                <label class="form-check-label" for="inlineRadio1"><?php echo stock_state(STOCK_IN_USE); ?></label>
                </div>
                <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="state" id="inlineRadio2" value="<?php echo STOCK_HISTORY; ?>" <?php echo ($stock['state'] == STOCK_HISTORY) ? "checked" : ""; ?>>
                <label class="form-check-label" for="inlineRadio2"><?php echo stock_state(STOCK_HISTORY); ?></label>
                </div>
                <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="state" id="inlineRadio3" value="<?php echo STOCK_ERROR; ?>" <?php echo ($stock['state'] == STOCK_ERROR) ? "checked" : ""; ?>>
                <label class="form-check-label" for="inlineRadio3"><?php echo stock_state(STOCK_ERROR); ?></label>
                </div>
            </div>
        </div>
        <div class="form-row mb-3">
            <div class="col">
            <label for="lotnr">lot nr</label>
            <input type="text" name="lotnr" class="form-control" id="lotnr" value="<?php echo $stock['lotnr'] ?>">
            </div>
            <div class="col">
            <label for="date">End of Life</label>
            <input type="date" name="eol" class="form-control" id="date" value="<?php echo $stock['eol'] ?>">
            </div>
        </div>
        <div class="form-row mb-3">
            <div class="col">
                <label for="current_buy_price">In Price</label>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" name="in_price" id="current_buy_price" value="<?php echo $stock['in_price'] ?>">
                    <div class="input-group-append">
                    <span class="input-group-text">&euro;</span>
                    </div>
                </div>
                <small id="tip">Does not impact selling price!</small>
            </div>
            <div class="col">
                <label for="exampleFormControlInput3">Catalog Price</label>
                <input type="text" class="form-control" name="catalog_price" disabled id="catalog_price" value="<?php echo $stock['products']['buy_price']; ?>">
            </div>
        </div>
        <div class="form-row mb-3">

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
