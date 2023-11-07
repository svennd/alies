<div class="col-lg-3 mb-4">
    <?php if ($bill['status'] != BILL_PAID): ?>

        <p class="lead"><?php echo $this->lang->line('payment'); ?> : <?php echo get_bill_status($bill['status']); ?></p>
        <?php
            $cash = round((float) $bill['cash'], 2);
            $card = round((float) $bill['card'], 2);
            if ($card + $cash != 0) :
                $total_short = round( (float) $bill['total_brut'] - ($card + $cash) , 2);
        ?>
        <p>
            <?php echo $this->lang->line('shortage'); ?> : <?php echo $total_short; ?> &euro; (<?php echo $this->lang->line('card'); ?> : <?php echo $card; ?> &euro;, <?php echo $this->lang->line('cash'); ?> : <?php echo $cash; ?> &euro;)
        </p>
        <?php endif; ?>

        <div class="row form-group">
            <div class="col-md-6"><label for="totalincbtw"><?php echo $this->lang->line('Total_inc'); ?></label></div>
            <div class="col-md-6"><label for="clientid"><?php echo $this->lang->line('client_id'); ?></label></div>
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" class="form-control" id="totalincbtw" name="clientid" value="<?php echo $bill['total_brut']; ?>" disabled />
                    <div class="input-group-append">
                        <span class="input-group-text">&euro;</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <input type="text" class="form-control" id="clientid" name="clientid" value="#<?php echo $owner['id'];?>" disabled />
            </div>
        </div>

        <!-- card -->
        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <span class="input-group-text" for="exampleCheck1"><a href="#" id="select_card" onclick="event.preventDefault()"><i class="fab fa-cc-visa"></i>&nbsp;<?php echo $this->lang->line('card'); ?></a></span>
            </div>
            <input type="text" class="form-control" id="card_value" name="card_value" value="<?php echo ($card != 0) ? $card:'';?>" />
            <div class="input-group-append">
                <span class="input-group-text">&euro;</span>
            </div>
        </div>

        <!-- cash -->
        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <span class="input-group-text" for="exampleCheck1"><a href="#" id="select_cash" onclick="event.preventDefault()"><i class="fas fa-euro-sign"></i>&nbsp;<?php echo $this->lang->line('cash'); ?></a></span>
            </div>
            <input type="text" class="form-control" id="cash_value" name="cash_value" value="<?php echo ($cash != 0) ? $cash:'';?>" />
            <div class="input-group-append">
                <span class="input-group-text">&euro;</span>
            </div>
            <div class="input-group-append">
                <span class="input-group-text" id="calculate"><a href="#"><i class="fas fa-calculator"></i></a></span>
            </div>
        </div>

        <!-- comment -->
        <h6 class="text-uppercase"><a data-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample"><?php echo $this->lang->line('comment'); ?></a></h6>
						<hr>
        <div class="collapse" id="collapseExample">
            <div class="form-group">
                <label for="msg"><?php echo $this->lang->line('comment_internal'); ?></label>
                <textarea class="form-control" id="msg" name="msg" rows="3"><?php echo (isset($bill['msg']) ? $bill['msg'] : ""); ?></textarea>
            </div>

            <div class="form-group">
                <label for="msg_invoice"><?php echo $this->lang->line('comment_on_bill'); ?></label>
                <textarea class="form-control" id="msg_invoice" name="msg_invoice" rows="3"><?php echo (isset($bill['msg_invoice']) ? $bill['msg_invoice'] : ""); ?></textarea>
            </div>
            </div>
        <!-- status -->
        <i><small id="payment_info" class="form-text text-muted ml-2">&nbsp;</small></i>

        <button type="submit" name="submit" value="1" class="btn btn-outline-success bounceit"><i class="fa-solid fa-house-medical-circle-check"></i> <?php echo $this->lang->line('payment_complete'); ?></button>
    <?php endif; ?>
    
    <?php if ($bill['status'] == BILL_PAID): ?>
        <p class="lead"><?php echo $this->lang->line('payment_complete'); ?></p>
        <?php
            $cash = round((float) $bill['cash'], 2);
            $card = round((float) $bill['card'], 2);
        ?>
        <?php echo $this->lang->line('payed'); ?> : <?php echo $bill['total_brut']; ?> &euro; (<?php echo $this->lang->line('card'); ?> : <?php echo $card; ?> &euro;, <?php echo $this->lang->line('cash'); ?> : <?php echo $cash; ?> &euro;)
        
        <div class="form-group">
            <label for="msg"><?php echo $this->lang->line('comment'); ?> (<?php echo $this->lang->line('comment_internal'); ?>)</label>
            <textarea class="form-control" id="msg" name="msg" rows="2"><?php echo (isset($bill['msg']) ? $bill['msg'] : ""); ?></textarea>
        </div>

        <div class="form-group">
            <label for="msg_invoice"><?php echo $this->lang->line('comment'); ?> (<?php echo $this->lang->line('comment_on_bill'); ?>)</label>
            <textarea class="form-control" id="msg_invoice" name="msg_invoice" rows="2"><?php echo (isset($bill['msg_invoice']) ? $bill['msg_invoice'] : ""); ?></textarea>
        </div>

        <a href="<?php echo base_url('owners/detail/' . $owner['id']); ?>" class="btn btn-outline-primary bounceit"><i class="fa-solid fa-user"></i> <?php echo $this->lang->line('client'); ?></a>
        <a href="#" class="btn btn-outline-success mx-2 bounceit" id="store_messages" onclick="event.preventDefault()"><i class="fa-solid fa-floppy-disk"></i> <?php echo $this->lang->line('store'); ?></a>
        
    <?php endif; ?>

    <?php if ($bill['status'] != BILL_PAID && $bill['status'] != BILL_INCOMPLETE): ?>
        <a href="<?php echo base_url(); ?>invoice/bill_unpay/<?php echo $bill['id']; ?>" class="btn btn-outline-danger mx-2 shakeit" id="bill_unpay" onclick="event.preventDefault()"><i class="fa-solid fa-house-medical-circle-xmark"></i> <?php echo $this->lang->line('drop_from_stock'); ?></a>
    <?php endif; ?>

</div>