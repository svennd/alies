<?php 
    $age = timespan(strtotime($pet['birth']), time(), 1); 
    $cash = (isset($billing_info['cash'])) ? round((float) $billing_info['cash'], 2) : 0;
    $card = (isset($billing_info['card'])) ? round((float) $billing_info['card'], 2) : 0;    
?>
<div class="row">
    <div class="col-4">
        <fieldset class="border max">
            <legend class="text-center topfields"><?php echo $this->lang->line('client'); ?></legend>
            <p class="text-center">
                <b class="text-uppercase"><a href="<?php echo base_url(); ?>owners/detail/<?php echo $owner['id']; ?>"><?php echo $owner['last_name'] ?> <?php echo $owner['first_name'] ?></a></b><br />
                <?php echo $owner['street'] . ' ' . $owner['nr'] . '<br/>' .  $owner['zip']. ' ' .  $owner['city']; ?><br>
            </p>
        </fieldset>
    </div>

    <div class="col-4">
        <fieldset class="border max">
            <legend class="text-center topfields"><?php echo $this->lang->line('pet_info'); ?></legend>
            <p class="text-center">
                <a href="<?php echo base_url(); ?>pets/fiche/<?php echo $pet['id']; ?>"><?php echo get_symbol($pet['type']); ?><?php echo $pet['name']; ?></a>
                <?php echo ($age < 30) ? '(' . $age . ')' : ''; ?>
                <br/>
                <?php echo get_gender($pet['gender']); ?>
                <?php if (isset($pet['breeds'])): ?>
                    <?php echo $pet['breeds']['name']; ?>
                <?php endif; ?>
                <br/>
            </p>
        </fieldset>
    </div>    
    
    <div class="col-4">
        <fieldset class="border max">
            <legend class="text-center topfields"><?php echo $this->lang->line('event'); ?></legend>
            <p class="text-center">
                <?php if($billing_info): ?>
                    <?php echo user_format_date($billing_info['created_at'], $user->user_date) ?> (<?php echo time_ago($billing_info['created_at']); ?>)
                    <br/>
                    <?php echo (isset($billing_info['location']['name'])) ? $billing_info['location']['name']: 'unknown'; ?>
                    <br/>
                    <?php echo $billing_info['total_brut'] . " &euro;"; ?>
                    (
                    <?php echo ($card != 0) ? "<i class='fab fa-cc-visa'></i> " . $card . "&euro; " : ""; ?>
                    <?php echo ($cash != 0) ? "<i class='fas fa-coins'></i> " . $cash . "&euro; " : ""; ?>
                    )
                <?php endif; ?>
                <br/>
            </p>
        </fieldset>
    </div>

</div>
<br/>