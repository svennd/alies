<style>
  #sidebar {
    max-width: 300px;
    height: 100vh;
    overflow-y: auto;
    background-color: #f8f9fa;
    margin-right: 40px;
  }

  #image-viewer {
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: #e9ecef;
    height: 80vh;
    position: relative;
  }

  #image-viewer img {
    display: block;
    max-width: 100%;
    max-height: 100%;
    border: 2px solid #343a40;
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
              <div class="list-group list-group-flush">

                <!-- Directory structure -->
                <div class="list-group-item">
                  <!-- <strong>22/08/2024</strong> -->
                  <ul class="list-unstyled ml-3">
                  <?php foreach ($data as $image): ?>
                    <?php $x =  base_url() . htmlspecialchars($image, ENT_QUOTES, 'UTF-8'); ?>
                    <li><a href="#" class="image-link" data-image="<?php echo $x; ?>" alt="Image" class="img-fluid"><?php echo basename($x); ?></a></li>
                  <?php endforeach; ?>
                  </ul>
                </div>
                <!-- end loop -->

              </div>
            </div> <!-- end sidebar -->

            <!-- Image Viewer -->
            <div id="image-viewer" class="image-viewer">
              <img src="<?php echo $x; ?>" id="image" alt="Image" >
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
