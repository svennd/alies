<?php $total = 0; ?>
<?php $total_excl = 0; ?>
<div class="table-responsive">
    <table class="table table-sm" id="invoice_table">
    <thead>
        <tr class="thead-light depad_header">
            <th><?php echo $this->lang->line('name'); ?></th>
            <th><?php echo $this->lang->line('volume'); ?></th>
            <th><?php echo $this->lang->line('lotnr'); ?></th>
            <th><?php echo $this->lang->line('VAT'); ?></th>
            <th><?php echo $this->lang->line('Price'); ?></th>
            <th><?php echo $this->lang->line('net_price'); ?></th>
            <th><?php echo $this->lang->line('options'); ?></th>
        </tr>
    </thead>
    <tbody>
    <?php include "event/block_add_prod_proc.php"; ?>
    <?php include "event/block_procedures.php"; ?>
    <?php include "event/block_consumables.php"; ?>
    <?php // include "event/block_add_barcode.php"; ?>
    </tbody>
    <tfoot>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td><i><?php echo $this->lang->line('sum'); ?></i></td>
            <td><i><?php echo round($total, 2); ?> &euro;</i></td>
            <td class="sensitive"><i><?php echo round($total_excl,2); ?> &euro;</i></td>
            <td>&nbsp;</td>
        </tr>
    </tfoot>
    </table>
</div>
<script>
document.addEventListener("DOMContentLoaded", function(){
  $(".sensitive").hover(function() {
    $(this).toggleClass('sensitive', 300);
  });
});

</script>
