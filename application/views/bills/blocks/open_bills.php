<?php if ($open_bills): ?>
    <div class="alert alert-danger" role="alert">
    <?php echo $this->lang->line('open_invoices'); ?> !<br/>
        <?php foreach($open_bills as $b): ?>
            <a href="<?php echo base_url(); ?>invoice/get_bill/<?php echo $b['id']; ?>"><?php echo $b['amount']; ?> &euro;</a><br/>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
