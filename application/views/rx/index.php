<style>
  #sidebar {
    max-width: 300px;
    width: 15vh;
    height: 100vh;
    overflow-y: auto;
    background-color: #f8f9fa;
    margin-right: 10px;
    margin-left: -20px;
    padding: 10px;
  }

  #image-viewer {
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: #e9ecef;
    height: 85vh;
    width: 100%;
    position: relative;
  }

  #image-viewer img {
    display: block;
    max-width: 100%;
    max-height: 100%;
    border: 2px solid #343a40;
    object-fit: contain;
  }
</style>
<div class="row">
  <div class="col-lg-12 mb-4">
    <div class="card shadow mb-4">
      <div class="card-header d-flex flex-row align-items-center justify-content-between">
        <div>
        <?php echo $owner['last_name'] ?> / 
        <a href="<?php echo base_url('pets/fiche/' . $pet['id']) ?>"><?php echo $pet['name'] ?></a> / RX <small>(#<?php echo $pet['id']; ?>)</small>
        </div>
        <div class="dropdown no-arrow">
          <button id="inversion" class="btn btn-sm btn-outline-primary flipit"><i class="fa-solid fa-circle-half-stroke"></i></button>
          <button id="download" class="btn btn-sm btn-outline-success ml-4"><i class="fa-solid fa-download"></i></button>
        </div>
      </div>

			<div class="card-body">
        <?php if($data): ?>
          <div class="d-flex">
            
            <!-- Sidebar -->
            <div id="sidebar" class="bg-light border-right">

                <!-- Directory structure -->
                <?php $first = null; ?>
                <?php foreach ($data as $info): ?>
                  <strong><?php echo $info['study_date']; ?></strong>
                  <ul class="list-unstyled ml-1">
                    <?php
                      $studydescription = explode(',', $info['study_description']);
                      $description = explode(',', $info['description']);
                    ?>
                    <?php foreach(explode(',', $info['images']) as $index => $image): ?>
                      <?php 
                        $x = base_url() . $RX_DIR . $image. '.jpg'; 
                        $first = (is_null($first)) ? $x : $first;
                      ?>
                      <li><a href="#" class="image-link" data-image="<?php echo $x; ?>" alt="Image" class="img-fluid"><?php echo $description[$index]; ?></a></li>
                    <?php endforeach; ?>

                  </ul>
                <?php endforeach; ?>
                <!-- end loop -->

            </div> <!-- end sidebar -->

            <!-- Image Viewer -->
            <div id="image-viewer" class="image-viewer">
              <img src="<?php echo $first; ?>" id="image" alt="Image" >
            </div>
          </div>
        <?php else: ?>
          <div class="alert alert-warning" role="alert">
            No images found.
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>



<script src="<?php echo base_url('assets/js/rx.js'); ?>"></script>
