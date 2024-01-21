<div class="col-lg-3 mb-4">
    <?php if ($bill['status'] != BILL_PAID): ?>

        <?php
            $cash = round((float) $bill['cash'], 2);
            $card = round((float) $bill['card'], 2);
            $transfer = round((float) $bill['transfer'], 2);
            if ($card + $cash + $transfer != 0) :
                $total_short = round( (float) $bill['total_brut'] - ($card + $cash + $transfer) , 2);
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
        <div class="input-group mb-1">
            <div class="input-group-prepend">
                <span class="input-group-text" for="card_value" style="width: 150px;"><a href="#" id="select_card" onclick="event.preventDefault()"><i class="fab fa-fw fa-cc-visa" style="color:blue"></i>&nbsp;<?php echo $this->lang->line('card'); ?></a></span>
            </div>
            <input type="text" class="form-control" id="card_value" name="card_value" value="<?php echo ($card != 0) ? $card:'';?>" />
            <div class="input-group-append">
                <span class="input-group-text">&euro;</span>
            </div>
        </div>

        <!-- cash -->
        <div class="input-group mb-1">
            <div class="input-group-prepend">
                <span class="input-group-text" for="cash_value" style="width: 150px;"><a href="#" id="select_cash" onclick="event.preventDefault()"><i class="fa-solid fa-fw fa-money-bill" style="color:green"></i>&nbsp;<?php echo $this->lang->line('cash'); ?></a></span>
            </div>
            <input type="text" class="form-control" id="cash_value" name="cash_value" value="<?php echo ($cash != 0) ? $cash:'';?>" />
            <div class="input-group-append">
                <span class="input-group-text">&euro;</span>
            </div>
        </div>

        <!-- transfer -->
        <div class="input-group mb-1">
            <div class="input-group-prepend">
                <span class="input-group-text" for="transfer_value" style="width: 150px;"><a href="#" id="select_transfer" onclick="event.preventDefault()"><i class="fa-solid fa-fw fa-money-bill-transfer" style="color:tomato"></i>&nbsp;<?php echo $this->lang->line('transfer'); ?></a></span>
            </div>
            <input type="text" class="form-control" id="transfer_value" name="transfer_value" value="<?php echo ($transfer != 0) ? $transfer:'';?>" />
            <div class="input-group-append">
                <span class="input-group-text">&euro;</span>
            </div>
        </div>


        <!-- help & status -->
        <input type="hidden" id="total_brut" name="total_brut" value="<?php echo $bill['total_brut']; ?>" />
        <div class="input-group mt-3" style="padding-left: 150px;">
            <div class="input-group-prepend">
                <span class="input-group-text" for="transfer_value"><a href="#" id="calculate"><i class="fas fa-calculator"></i></a></span>
            </div>
            <input type="text" disabled class="form-control disabled" id="payment_info" name="payment_info"/>
            <div class="input-group-append">
                <span class="input-group-text">&euro;</span>
            </div>
        </div>
       
        <!-- status -->
        <i><small id="" class="form-text text-muted ml-2">&nbsp;</small></i>

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

        <button type="submit" name="submit" value="1" class="btn btn-outline-success bounceit"><i class="fa-solid fa-house-medical-circle-check"></i> <?php echo $this->lang->line('payment_process'); ?></button>
        
        <small><br/><?php echo $this->lang->line('payment'); ?> : <?php echo get_bill_status($bill['status']); ?></small>

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
</div>