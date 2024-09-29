<?php
$header = array (
        "owner_id",
        "first_name",
        "last_name",
        "street",
        "nr",
        "city",
        "province",
        "zip",

        "product_name",
        "disease",

        "injection_date",
        "redo_date",
        
        "pet_name",
        "pet_type",

        "owner_mail",
        "last_bill",
        "debts",

        "vet_name"
    );

    $pet_type_map = array(
        DOG     => $this->lang->line('dog'),
        CAT     => $this->lang->line('cat'),
        HORSE   => $this->lang->line('horse'),
        BIRD    => $this->lang->line('bird'),
        OTHER   => $this->lang->line('other'),
        RABBIT  => $this->lang->line('rabbit')
    );

?>
<?php echo sprintf('"%s"', implode('","', $header)); ?>

<?php foreach($expiring_vacs as $v): ?>
<?php
if (isset($pet_type_map[$v['pet_type']])) {
    $v['pet_type'] = $pet_type_map[$v['pet_type']];
}
?>
<?php echo sprintf('"%s"', implode('","', $v)); ?>

<?php endforeach; ?>