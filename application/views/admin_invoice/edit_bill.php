<div class="card shadow mb-4">
	<div class="card-header d-flex flex-row align-items-center justify-content-between">
		<div>
        <a href="<?php echo base_url('reports'); ?>">Report</a> /
        <a href="<?php echo base_url('reports/bills'); ?>">Invoices</a> /
         Edit #<?php echo get_bill_id($bill['id']); ?>
        </div>
	</div>
	<div class="card-body">
    <form action="<?php echo base_url('admin_invoice/edit_bill/' . $bill['id']); ?>" method="post" autocomplete="off">
    <div class="alert alert-warning" role="alert">
    Editing bills directly might cause problems in accounting, please use with caution.
    </div>

        <div class="form-row mb-3">
            <div class="col">
            <div class="card border-danger" style="height:100%;">
                <div class="card-header text-danger">Modified Bill information</div>

                <div class="card-body">
                <label for="exampleFormControlInput3">Amount</label>
                <div class="form-row">
                    <div class="col">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" name="amount" id="amount" value="<?php echo $bill['amount'] ?>">
                            <div class="input-group-append">
                                <span class="input-group-text">&euro;</span>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        &nbsp;
                    </div>
                </div>
                <div class="form-row">
                    <div class="col">
                        <label for="exampleFormControlInput3">Card</label>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" name="card" id="card" value="<?php echo $bill['card'] ?>">
                            <div class="input-group-append">
                                <span class="input-group-text">&euro;</span>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <label for="exampleFormControlInput3">Cash</label>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" name="cash" id="cash" value="<?php echo $bill['cash'] ?>">
                            <div class="input-group-append">
                                <span class="input-group-text">&euro;</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-row mb-3">
                    <div class="col">
                        <label for="location">Location</label>
                        <select name="location" class="form-control" id="location">
                            <?php foreach($locations as $location): ?>
                                <option value="<?php echo $location['id']; ?>" <?php echo ($location['id'] == $bill['location']['id']) ? "selected" : ""; ?>><?php echo $location['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col">
                        <label for="location">Vet</label>
                        <select name="vet" class="form-control" id="location">
                            <?php foreach($vets as $v): ?>
                                <option value="<?php echo $v['id']; ?>" <?php echo ($v['id'] ==  $bill['vet']['id']) ? "selected" : ""; ?>><?php echo $v['first_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

        <div class="form-row mb-3">
            <div class="col">
                <label for="exampleFormControlInput3">State</label><br/>
                <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="status" id="inlineRadio1" value="<?php echo PAYMENT_PAID; ?>" <?php echo ($bill['status'] == PAYMENT_PAID) ? "checked" : ""; ?>>
                <label class="form-check-label" for="inlineRadio1"><?php echo get_bill_status(PAYMENT_PAID); ?></label>
                </div>
                <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="status" id="inlineRadio2" value="<?php echo PAYMENT_PARTIALLY; ?>" <?php echo ($bill['status'] == PAYMENT_PARTIALLY) ? "checked" : ""; ?>>
                <label class="form-check-label" for="inlineRadio2"><?php echo get_bill_status(PAYMENT_PARTIALLY); ?></label>
                </div>
                <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="status" id="inlineRadio3" value="<?php echo PAYMENT_UNPAID; ?>" <?php echo ($bill['status'] == PAYMENT_UNPAID) ? "checked" : ""; ?>>
                <label class="form-check-label" for="inlineRadio3"><?php echo get_bill_status(PAYMENT_UNPAID); ?></label>
                </div>
                <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="status" id="inlineRadio4" value="<?php echo PAYMENT_OPEN; ?>" <?php echo ($bill['status'] == PAYMENT_OPEN) ? "checked" : ""; ?>>
                <label class="form-check-label" for="inlineRadio4"><?php echo get_bill_status(PAYMENT_OPEN); ?></label>
                </div>
                <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="status" id="inlineRadio5" value="<?php echo PAYMENT_NON_COLLECTABLE; ?>" <?php echo ($bill['status'] == PAYMENT_NON_COLLECTABLE) ? "checked" : ""; ?>>
                <label class="form-check-label" for="inlineRadio5"><?php echo get_bill_status(PAYMENT_NON_COLLECTABLE); ?></label>
                </div>
            </div>
        </div>   
        <div class="form-row mb-3">

        <div class="col">
            <label for="date">Created at</label>
            <input type="datetime-local" name="created" class="form-control" id="date" value="<?php  echo $bill['created_at']; ?>">
            </div>
        </div>

                </div>
            </div>
            </div>
            <div class="col">
            <div class="card border-success" style="height:100%;">
                <div class="card-header">Current Bill information</div>
                <div class="card-body text-success">
                    <table class="table">
                        <tr>
                            <td><?php echo $this->lang->line('bill_id'); ?></td>
                            <td><?php echo (!is_null($bill['invoice_id'])) ? get_invoice_id($bill['invoice_id'], $bill['invoice_date'], $this->conf['invoice_prefix']['value']) : 'not assigned'; ?></td>
                        </tr>
                        <tr>
                            <td>Client</td>
                            <td><a href="<?php echo base_url('owners/detail/' . $bill['owner_id']); ?>"><?php echo $bill['owner']['last_name']; ?></a></td>
                        </tr>
                        <tr>
                            <td>Amount</td>
                            <td><?php echo $bill['amount']; ?> &euro; (card : <?php echo $bill['card']; ?> &euro;, cash : <?php echo $bill['cash']; ?> &euro;)</td>
                        </tr>
                        <tr>
                            <td>Vet</td>
                            <td><?php echo $bill['vet']['first_name']; ?></td>
                        </tr>
                        <tr>
                            <td>Location</td>
                            <td><?php echo $bill['location']['name']; ?></td>
                        </tr>
                        <tr>
                            <td>Created</td>
                            <td><?php echo $bill['created_at']; ?><br/><small><?php echo user_format_date($bill['created_at'], $user->user_date); ?></small></td>
                        </tr>
                    </table>
                </div>
            </div>
            </div>
        </div>


        <button type="submit" name="submit" value="1" class="btn btn-primary">Edit</button>
    </form>
    </div>

    
    <div class="col-lg-6">
            <div class="card border-danger my-3" style="height:100%;">
                <div class="card-header <?php echo (!is_null($bill['invoice_id'])) ? "text-secondary" : "text-danger"; ?>">Remove bill</div>

                <div class="card-body">
                    <form action="<?php echo base_url('admin_invoice/rm_bill/' . $bill['id']); ?>" method="post" autocomplete="off">
                        <div class="form-row mb-3">
                            <div class="col text-danger">
                                <label for="exampleFormControlInput3">delete reason</label>
                                <input type="text" class="form-control" name="reason">
                            </div>
                        </div>
                        <button type="submit" name="submit" value="1" class="btn btn-danger" <?php echo (!is_null($bill['invoice_id'])) ? "disabled" : ""; ?>>Delete bill</button>
                    </form>
                </div>
               </div>
    </div>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#prd").show();
	$("#products").addClass('active');
	$("#stock").addClass('active');
});
</script>
