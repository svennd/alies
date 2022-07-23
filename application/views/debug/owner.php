<div class="row">
	<div class="col-lg-12 mb-4">
      <div class="card shadow mb-4">
		<div class="card-header">
			<a href="<?php echo base_url(); ?>owners/detail/<?php echo $owner['id']; ?>"><?php echo $owner['last_name'] ?></a> / <span style="color:red;">debug</span>
			</div>
            <div class="card-body">
                <p>These functions should not be used, unless some unrecoverable error happened.</p>
                <br/>
                <br/>
                <br/>
                <hr />
                <a class="btn btn-outline-danger" href="<?php echo base_url() . 'debug/events/' . $owner['id']; ?>"><i class="fas fa-bug"></i> close bills/events</a>
                <br/>
                <br/>
                <p>
                    This function will attempt to close all events & close all bills. (payed or not)<br/>
                    <ul>
                        <li>Open events (everywhere) will be closed & considered finished</li>
                        <li>Open bills (or sub-bills) will be closed & considered payed</li>
                        <li>A record will be made in the logs to mark this event.</li>
                    </ul>
                    (after this has happened you will return to this page.)
                </p>
                <hr />
                <a class="btn btn-outline-danger" href="<?php echo base_url() . 'debug/delete_owner/' . $owner['id']; ?>"><i class="fas fa-bug"></i> delete owner/client</a>
                <br/>
                <br/>
                <p>
                    This function will delete the client + all his pets. <strong>bills will NOT be deleted (but will contain errors)</strong><br/>
                    <ul>
                        <li>This function will lookup all pets, then remove them. (if you want to keep the pets, transfer ownership first to a temporary host)</li>
                        <li>Then the owner will be deleted</li>
                    </ul>
                    note : soft delete is on, so the owners & pets can be retrieved. (but only after manual database recovery)<br/>
                    (<strong>after this has happened you will return to the INDEX.</strong>)
                </p>
			</div>
	  </div>
	</div>
</div>