<?php $total = 0; ?>
<?php $total_excl = 0; ?>
<div class="table-responsive">
    <!-- required for JS coloring -->
    <input type="hidden" id="current_location_vet" name="current_location_vet" value="<?php echo $u_location; ?>" />
    <input type="hidden" id="current_event" name="current_event" value="<?php echo $event_id; ?>" />

    <table class="table table-sm" id="invoice_table">
    <thead>
        <tr class="thead-light depad_header">
            <th><?php echo $this->lang->line('name'); ?></th>
            <th class="d-none d-sm-table-cell"><?php echo $this->lang->line('volume'); ?></th>
            <th class="d-none d-sm-table-cell"><?php echo $this->lang->line('lotnr'); ?></th>
            <th class="d-none d-sm-table-cell"><?php echo $this->lang->line('VAT'); ?></th>
            <th><?php echo $this->lang->line('Price'); ?></th>
            <th class="d-none d-sm-table-cell"><?php echo $this->lang->line('net_price'); ?></th>
            <th class="d-none d-sm-table-cell"><?php echo $this->lang->line('options'); ?></th>
        </tr>
    </thead>
    <tbody>
    <?php include "event/block_add.php"; ?>
    <?php // include "event/block_add_prod_proc.php"; ?>
    <?php include "event/block_procedures.php"; ?>
    <?php include "event/block_consumables.php"; ?>
    </tbody>
    <tfoot>
        <tr>
            <td class="d-none d-sm-table-cell">&nbsp;</td>
            <td class="d-none d-sm-table-cell">&nbsp;</td>
            <td class="d-none d-sm-table-cell">&nbsp;</td>
            <td><i><?php echo $this->lang->line('sum'); ?></i></td>
            <td><i id="bruto_sum"><?php echo round($total, 2); ?> &euro;</i></td>
            <td class="d-none d-sm-table-cell sensitive"><i id="netto_sum"><?php echo round($total_excl,2); ?> &euro;</i></td>
            <td class="d-none d-sm-table-cell">&nbsp;</td>
        </tr>
    </tfoot>
    </table>
</div>