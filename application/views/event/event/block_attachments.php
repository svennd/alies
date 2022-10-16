<?php echo $this->lang->line('attachments'); ?> :
<div class="form-row py-2">

    <?php if($event_uploads): ?>
    <div class="col">
        <?php foreach($event_uploads as $upload): ?>
            <div id="upload_<?php echo $upload['id']; ?>">
                <a href="<?php echo base_url(); ?>files/get_file/<?php echo $upload['id']; ?>"><?php echo $upload['filename']; ?></a>
                <a href="#" class="file_line btn btn-sm btn-outline-danger" id="del_<?php echo $upload['id']; ?>"><i class="fas fa-trash-alt"></i></a>
                <br/>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>