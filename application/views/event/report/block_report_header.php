<?php 
    $age = timespan(strtotime($pet['birth']), time(), 1); 
    $total = floatval($billing_info['total_brut']);
    $cash = floatval($billing_info['cash']);
    $card = floatval($billing_info['card']);
    $transfer = floatval($billing_info['transfer']);
?>

<div class="card shadow mb-4">
    <div class="card-body">
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
                            <?php if($total == $card): ?>
                                <?php echo "<i class='fab fa-cc-visa 'style='color:blue'></i> " . $total . "&euro; " ?>
                            <?php elseif($total == $cash): ?>
                                <?php echo "<i class='fa-solid fa-money-bill' style='color:green'></i> " . $total . "&euro; " ?>
                            <?php elseif($total == $transfer): ?>
                                <?php echo "<i class='fa-solid fa-fw fa-money-bill-transfer' style='color:tomato'></i> " . $total . "&euro; " ?>
                            <?php else: ?>
                                <?php echo $total . " &euro;"; ?>
                                (
                                <?php echo ($card != 0) ? "<i class='fab fa-cc-visa' style='color:blue'></i> " . $card . "&euro; " : ""; ?>
                                <?php echo ($cash != 0) ? "<i class='fa-solid fa-money-bill' style='color:green'></i> " . $cash . "&euro; " : ""; ?>
                                <?php echo ($transfer != 0) ? "<i class='fa-solid fa-fw fa-money-bill-transfer' style='color:tomato'></i> " . $transfer . "&euro; " : ""; ?>
                                )
                            <?php endif; ?>
                        <?php endif; ?>
                        <br/>
                    </p>
                </fieldset>
            </div>
        </div>
    </div>
</div>