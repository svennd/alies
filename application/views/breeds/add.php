<div class="row">
    <div class="col-6">
        <div class="card shadow mb-4">
            <div class="card-header d-flex flex-row align-items-center justify-content-between">
                <div>
                <a href="<?php echo base_url('breeds'); ?>"><?php echo $this->lang->line('breeds'); ?></a> / <?php echo $this->lang->line('add'); ?>
                </div>
            </div>
            <div class="card-body">
                <form action="<?php echo base_url('breeds/add'); ?>" method="post" autocomplete="off">
                        <div class="form-row">
                            <div class="col">
                                <label for="exampleFormControlInput3"><?php echo $this->lang->line('breed_name'); ?></label>
                                <input type="text" class="form-control" name="name" value="">
                            </div>
                            <div class="col">
                                <label for=""><?php echo $this->lang->line('type'); ?></label><br/>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="type" id="inlineRadio1" value="<?php echo DOG; ?>">
                                    <label class="form-check-label" for="inlineRadio1"><?php echo get_symbol(DOG, true); ?></label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="type" id="inlineRadio2" value="<?php echo CAT; ?>">
                                    <label class="form-check-label" for="inlineRadio2"><?php echo get_symbol(CAT, true); ?></label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="type" id="inlineRadio3" value="<?php echo HORSE; ?>">
                                    <label class="form-check-label" for="inlineRadio3"><?php echo get_symbol(HORSE, true); ?></label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="type" id="inlineRadio4" value="<?php echo BIRD; ?>">
                                    <label class="form-check-label" for="inlineRadio4"><?php echo get_symbol(BIRD, true); ?></label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="type" id="inlineRadio5" value="<?php echo RABBIT; ?>">
                                    <label class="form-check-label" for="inlineRadio5"><?php echo get_symbol(RABBIT, true); ?></label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="type" id="inlineRadio6" value="<?php echo OTHER; ?>">
                                    <label class="form-check-label" for="inlineRadio6"><?php echo get_symbol(OTHER, true); ?></label>
                                </div>
                            </div>
                        </div>
                        <div class="form-row my-3">
                            <div class="col">
                                <label for="location"><?php echo $this->lang->line('weight_male'); ?></label>
                                <div class="form-row">
                                    <div class="col">
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="male_min_weight" value="">
                                            <div class="input-group-append">
                                                <span class="input-group-text">kg (min)</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="male_max_weight" value="">
                                            <div class="input-group-append">
                                                <span class="input-group-text">kg (max)</span>
                                            </div>
                                        </div>
                                    </div>
                            </div>
                            </div>
                            <div class="col">
                                <label for="location"><?php echo $this->lang->line('weight_female'); ?></label>
                                <div class="form-row">
                                    <div class="col">
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="female_min_weight" value="">
                                            <div class="input-group-append">
                                                <span class="input-group-text">kg (min)</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="female_max_weight" value="">
                                            <div class="input-group-append">
                                                <span class="input-group-text">kg (max)</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <button type="submit" name="submit" value="1" class="btn btn-primary"><?php echo $this->lang->line('add'); ?></button>
                </form>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#adminmgm").show();
	$("#admin").addClass('active');
	$("#adminbreed").addClass('active');
});
</script>