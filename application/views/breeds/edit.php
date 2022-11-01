<div class="row">
    <div class="col-6">
        <div class="card shadow mb-4">
            <div class="card-header d-flex flex-row align-items-center justify-content-between">
                <div>
                <a href="<?php echo base_url('breeds'); ?>"><?php echo $this->lang->line('breeds'); ?></a> / <?php echo $this->lang->line('edit'); ?>
                </div>
            </div>
            <div class="card-body">
                <form action="<?php echo base_url('breeds/edit/' . $id); ?>" method="post" autocomplete="off">
                        <?php if($update): ?>
                            <div class="alert alert-success" role="alert"><?php echo $this->lang->line('breed_updated'); ?></div>
                        <?php endif; ?>
                        <div class="form-row">
                            <div class="col">
                                <label for="exampleFormControlInput3"><?php echo $this->lang->line('breed_name'); ?></label>
                                <input type="text" class="form-control" name="name" value="<?php echo $breed['name'] ?>">
                            </div>
                            <div class="col">
                                <label for=""><?php echo $this->lang->line('type'); ?></label><br/>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="type" id="inlineRadio1" value="<?php echo DOG; ?>" <?php echo ($breed['type'] == DOG) ? "checked" : ""; ?>>
                                    <label class="form-check-label" for="inlineRadio1"><?php echo get_symbol(DOG, true); ?></label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="type" id="inlineRadio2" value="<?php echo CAT; ?>" <?php echo ($breed['type'] == CAT) ? "checked" : ""; ?>>
                                    <label class="form-check-label" for="inlineRadio2"><?php echo get_symbol(CAT, true); ?></label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="type" id="inlineRadio3" value="<?php echo HORSE; ?>" <?php echo ($breed['type'] == HORSE) ? "checked" : ""; ?>>
                                    <label class="form-check-label" for="inlineRadio3"><?php echo get_symbol(HORSE, true); ?></label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="type" id="inlineRadio4" value="<?php echo BIRD; ?>" <?php echo ($breed['type'] == BIRD) ? "checked" : ""; ?>>
                                    <label class="form-check-label" for="inlineRadio4"><?php echo get_symbol(BIRD, true); ?></label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="type" id="inlineRadio5" value="<?php echo RABBIT; ?>" <?php echo ($breed['type'] == RABBIT) ? "checked" : ""; ?>>
                                    <label class="form-check-label" for="inlineRadio5"><?php echo get_symbol(RABBIT, true); ?></label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="type" id="inlineRadio6" value="<?php echo OTHER; ?>" <?php echo ($breed['type'] == OTHER || $breed['type'] == '-1') ? "checked" : ""; ?>>
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
                                            <input type="text" class="form-control" name="male_min_weight" value="<?php echo $breed['male_min_weight'] ?>">
                                            <div class="input-group-append">
                                                <span class="input-group-text">kg (min)</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="male_max_weight" value="<?php echo $breed['male_max_weight'] ?>">
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
                                            <input type="text" class="form-control" name="female_min_weight" value="<?php echo $breed['female_min_weight'] ?>">
                                            <div class="input-group-append">
                                                <span class="input-group-text">kg (min)</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="female_max_weight" value="<?php echo $breed['female_max_weight'] ?>">
                                            <div class="input-group-append">
                                                <span class="input-group-text">kg (max)</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <button type="submit" name="submit" value="1" class="btn btn-primary"><?php echo $this->lang->line('edit'); ?></button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="card shadow mb-4">
            <div class="card-header d-flex flex-row align-items-center justify-content-between">
                <div>
                <a href="<?php echo base_url('breeds'); ?>"><?php echo $this->lang->line('breeds'); ?></a> / <?php echo $this->lang->line('breed_merge'); ?>
                </div>
            </div>
        <div class="card-body">
        <form method="post" action="<?php echo base_url('breeds/merge/' . $breed['id']); ?>">
            <div class="form-group">
                <label for="old_breed"><?php echo $this->lang->line('old_breed'); ?></label>
                <input type="text" name="old_breed" class="form-control" id="old_breed" value="<?php echo $breed['name'] ?>" readonly>
            </div>
            <div class="form-group">
                <label for="new_breed"><?php echo $this->lang->line('new_breed'); ?></label>
                    <?php if ($breeds): ?>
					<select name="new_breed" class="form-control" id="new_breed">
						<?php foreach ($breeds as $x): ?>
							<option value="<?php echo $x['id']; ?>"><?php echo $x['name']; ?></option>
						<?php endforeach; ?>
					</select>
					<?php endif; ?>
            </div>
            <input type="hidden" name="old_breed_id" id="old_breed_id" value="" />
            <button type="submit" name="submit" value="merge" class="btn btn-danger mb-2"><?php echo $this->lang->line('breed_merge'); ?></button>
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
    $('#new_breed').select2({theme: 'bootstrap4'});
});
</script>