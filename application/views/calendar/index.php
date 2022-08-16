<style>
.calendar_day {
    padding: 25px;
    margin : 5px;
    background-color: #fefefe;
}

.not_this_month {
    background-color: #efcece;
}
</style>
<?php
echo $cdate->format("Y-m-d")."<br>";
?>
<br/>
<a href="<?php echo base_url(); ?>calendar/index/<?php echo --$off; ?>">prev. month</a>
- 
<a href="<?php echo base_url(); ?>calendar/index/<?php echo $off+2; ?>">next month</a>
<br/>


<?php for($i = 0; $i < $ndays; $i++): ?>

<?php echo (($i % 7) == 0) ? '<div class="row">':'';?>

<div class="col calendar_day <?php echo ($cdate->format('m') != $this_month) ? 'not_this_month': ''; ?>">
    <?php echo $cdate->format('D'); ?><br/>
    <?php echo $cdate->format('m-d'); ?>
    <?php $cdate->modify('+1 day'); ?>
</div>

<?php echo ($i != 0 && (($i+1) % 7 == 0)) ? '</div>':'';?>

<?php endfor; ?>
