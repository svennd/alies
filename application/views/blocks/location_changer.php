<?php foreach ($all_locations as $location):
    if ($location['id'] == $clocation) {
        $loc = $location;
        break;
    }
    endforeach;
?>
<div class="dropdown show">
    <a class=" dropdown-toggle btn btn-outline-success btn-sm" style="color:#1cc88a;background-color:rgba(0,0,0,0);" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    <i class="fa-solid fa-fw fa-location-dot" style="color:<?php echo $loc['color'] ?>"></i> <?php echo $loc['name']; ?>
    </a>
    <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
    <?php foreach ($all_locations as $location): if ($location['name'] == $loc['name']) { continue; } ?>
        <a class="dropdown-item" href="<?php echo base_url('welcome/change_location/' . $location['id']); ?>"><i class="fa-solid fa-fw fa-location-dot" style="color:<?php echo $location['color']; ?>"></i><?php echo $location['name']; ?></a>
    <?php endforeach; ?>
    </div>
</div>