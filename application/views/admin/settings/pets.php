
<strong>Pets</strong>
<p>Configuration of pets table</p>

<div class="list-group mb-5 shadow">
    <div class="list-group-item list-group-item-action">
        <div class="row align-items-center">
            <div class="col">
                <strong class="mb-0">Auto Death</strong>
                <p class="text-muted mb-0">Allow the system to mark pets as death.<br/>Based on the age defined below and<br/> atleast <strong>no event</strong> for 2 years. (including hidden events)<br/>
                    <i>The value "0 years" disables auto death for this type.</i>
                </p>
            </div>
            <div class="col-auto">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="autdeath" name="check_autdeath" <?php echo (isset($config['autdeath']) && base64_decode($config['autdeath'])) ? 'checked' : ''; ?>>
                    <label class="custom-control-label" for="autdeath"></label>
                </div>
            </div>
        </div>
    </div>
    <?php foreach(array(DOG, CAT, HORSE, BIRD, RABBIT, OTHER) as $type): ?>
    <div class="list-group-item list-group-item-action">
        <div class="row align-items-center">
            <div class="col">
                <strong class="mb-0"><?php echo get_symbol($type, true); ?></strong>
                <p class="text-muted mb-0"></p>
            </div>
            <div class="col-auto">
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control" id="auto_dead_<?php echo $type; ?>" name="conf_auto_dead_<?php echo $type; ?>" value="<?php echo (isset($config['auto_dead_' . $type])) ? base64_decode($config['auto_dead_' . $type]) : ''; ?>" autocomplete="auto_dead_<?php echo $type ?>" placeholder="90">
                    <div class="input-group-append">
                        <span class="input-group-text" id="basic-addon2">years</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>