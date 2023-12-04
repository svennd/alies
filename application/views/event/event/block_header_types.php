<div class="dropdown no-arrow">
    <?php if($event_info['no_history'] == 1): ?>
    <a href="<?php echo base_url('events_report/enable_history/' . $event_id); ?>" role="button" id="dropdownMenuLink" class="btn btn-outline-success btn-sm">
        <i class="fas fa-eye"></i><span>&nbsp;<?php echo $this->lang->line('enable_report'); ?></span>
    </a>
    <?php else: ?>
    <a href="<?php echo base_url('events_report/set_type/' . $event_id . '/' . DISEASE); ?>" role="button" class="btn btn-outline-primary btn-sm <?php echo ($event_info["type"] == DISEASE) ? "btn-outline-success" : "" ?>">
        <i class="fas fa-virus"></i><span>&nbsp;<?php echo $this->lang->line('disease'); ?></span>
    </a>
    <a href="<?php echo base_url('events_report/set_type/'. $event_id . '/' . OPERATION); ?>" role="button" class="btn btn-outline-primary btn-sm <?php echo ($event_info["type"] == OPERATION) ? "btn-outline-success" : "" ?>">
        <i class="fas fa-hand-holding-medical"></i><span>&nbsp;<?php echo $this->lang->line('operation'); ?></span>
    </a>
    <a href="<?php echo base_url('events_report/disable_history/' . $event_id); ?>" role="button" id="dropdownMenuLink" class="btn btn-outline-danger btn-sm">
        <i class="fas fa-eye-slash"></i><span>&nbsp;<?php echo $this->lang->line('disable_report'); ?></span>
    </a>
    <?php endif; ?>
</div>	