<style>
.details-button span {
  max-width: 0;
  display: inline-flex;
  white-space: nowrap;
  transition: max-width 0.9s, padding-right 0.45s;
  overflow: hidden;
}

.details-button:hover span, .details-button:focus span {
  max-width: 200px;
}
</style>

<div class="dropdown no-arrow">
    <?php if($event_info['no_history'] == 1): ?>
    <a href="<?php echo base_url(); ?>events_report/enable_history/<?php echo $event_id; ?>" role="button" id="dropdownMenuLink" class="btn btn-outline-success btn-sm details-button">
        <i class="fas fa-eye"></i><span>&nbsp;<?php echo $this->lang->line('show_history'); ?></span>
    </a>
    <?php else: ?>
    <a href="<?php echo base_url(); ?>events_report/set_type/<?php echo $event_id . "/" . DISEASE; ?>" role="button" class="btn btn-outline-primary btn-sm <?php echo ($event_info["type"] == DISEASE) ? "btn-outline-success" : "details-button" ?>">
        <i class="fas fa-virus"></i><span>&nbsp;<?php echo $this->lang->line('disease'); ?></span>
    </a>
    <a href="<?php echo base_url(); ?>events_report/set_type/<?php echo $event_id . "/" . OPERATION; ?>" role="button" class="btn btn-outline-primary btn-sm <?php echo ($event_info["type"] == OPERATION) ? "btn-outline-success" : "details-button" ?>">
        <i class="fas fa-hand-holding-medical"></i><span>&nbsp;<?php echo $this->lang->line('operation'); ?></span>
    </a>
    <a href="<?php echo base_url(); ?>events_report/set_type/<?php echo $event_id . "/" . MEDICINE; ?>" role="button" class="btn btn-outline-primary btn-sm <?php echo ($event_info["type"] == MEDICINE) ? "btn-outline-success" : "details-button" ?>">
        <i class="fas fa-prescription-bottle-alt"></i><span>&nbsp;<?php echo $this->lang->line('medicine'); ?></span>
    </a>
    <a href="<?php echo base_url(); ?>events_report/disable_history/<?php echo $event_id; ?>" role="button" id="dropdownMenuLink" class="btn btn-outline-danger btn-sm details-button">
        <i class="fas fa-eye-slash"></i><span>&nbsp;<?php echo $this->lang->line('no_history'); ?></span>
    </a>
    <?php endif; ?>
</div>	