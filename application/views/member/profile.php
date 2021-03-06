<div class="row">
	<div class="col-lg-4">
		<div class="card mb-4">
			<div class="card-header">Profile Picture</div>
			
			<div class="card-body">
			<?php if (isset($new_image)): ?>
				<div class="form-group">
					<div class="alert alert-info" style="display:none" id="alert_to_large" role="alert">Large images, might not upload; Consider offline reducing file size</div>	
					<form action="<?php echo base_url(); ?>vet/profile" id="profile_picture_accept" method="post" accept-charset="utf-8">
					<?php echo (isset($new_image)) ? "<img src='data:image/png;base64,".base64_encode($new_image)."' />" : ''; ?>
						<input type="hidden" id="timetag" name="timetag" value="<?php echo $time; ?>" />
						<input type="hidden" id="imagestore" name="imagestore" value="1" />
						<br/>
						<br/>
						<input type="submit" name="submit" value="Accept" class="btn btn-primary btn-user" />
						<input type="submit" name="submit" value="Deny" class="btn btn-primary btn-user" />
					</form>
				</div>
			
			<?php else : ?>
				<div class="form-group">
						<?php if (isset($image_updated_info)): ?>
							<div class="alert alert-info" role="alert"><?php echo $image_updated_info; ?></div>
						<?php endif; ?>
						<form action="<?php echo base_url(); ?>vet/profile" id="profile_form" method="post" accept-charset="utf-8">
						<div class="actions">
							<a class="btn file-btn">
								<span>Upload</span>
								<input type="file" id="upload" value="Choose a file" accept="image/*" />
							</a>
							<?php if (empty($user->image)): ?>
								<img class="my-image" id="upload-demo" src="<?php echo base_url(); ?>assets/public/unknown.jpg" />
							<?php else : ?>
								<?php if (isset($uploaded_image)): ?>
									<img class="my-image" id="upload-demo" src="<?php echo base_url() . 'assets/public/' . $uploaded_image; ?>" />
								<?php else: ?>
									<img class="my-image" id="upload-demo" src="<?php echo base_url() . 'assets/public/' . $user->image; ?>" />
								<?php endif; ?>
							<?php endif; ?>
						</div>
						<input type="hidden" id="imagebase64" name="imagebase64">
						</form>	
				</div>
				<button class="form_submit btn btn-primary">Store</button>
				<button class="vanilla-rotate btn btn-info" data-deg="-90">Rotate Left</button>
				<button class="vanilla-rotate btn btn-info" data-deg="90">Rotate Right</button>			
			<?php endif; ?>
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="card mb-4">
			<div class="card-header">
				Profile Settings
			</div>
			
			<div class="card-body">
				<div class="alert alert-info" style="display:none" id="alert_to_large" role="alert">Large images, might not upload; Consider offline reducing file size</div>	
				<form action="<?php echo base_url(); ?>vet/profile_change" method="post" accept-charset="utf-8">
					<div class="form-group">
						<label for="email">Email</label>
						<input type="mail" name="email" class="form-control" id="email" value="<?php echo $user->email; ?>" disabled>
					</div>	 
					<div class="form-row">
						<div class="form-group col-md-6">
							<label for="first_name">First Name</label>
							<input type="text" name="first_name" class="form-control" id="first_name" value="<?php echo $user->first_name; ?>">
						</div>
						<div class="form-group col-md-6">
							<label for="last_name">Last Name</label>
							<input type="text" name="last_name" class="form-control" id="last_name" value="<?php echo $user->last_name; ?>">
						</div>
					</div>
					<div class="form-group">
						<label for="phone">Phone</label>
						<input type="text" name="phone" class="form-control" id="phone" value="<?php echo $user->phone; ?>">
					</div>
					<div class="form-group">
						<label for="head">SideBar</label>
						<input type="color" id="head" class="form-control" name="color" value="<?php echo $user->sidebar; ?>">
					</div>
					<br/>
					<button type="submit" name="submit" value="Accept" class="btn btn-primary">Store</button>
				</form>
			</div>
		</div>
	</div>
</div>


<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
    var $uploadCrop = $('#upload-demo');

	// read file
    function readFile(input) {
        if (input.files && input.files[0]) {
			console.log(Math.ceil(input.files[0].size/1024/1024));
			if (Math.ceil(input.files[0].size/1024/1024) > 8) {
				$("#alert_to_large").show();
				console.log("TODO : show warning");
			}
            var reader = new FileReader();          
            reader.onload = function (e) {
                $uploadCrop.croppie('bind', {
                    url: e.target.result
                });
                $('.upload-demo').addClass('ready');
            }           
            reader.readAsDataURL(input.files[0]);
        }
    }
	
	// rotate hack
	$(function() {
	  $('.vanilla-rotate').on('click', function(ev) {
			console.log("fire");
			$uploadCrop.croppie('rotate', parseInt($(this).data('deg')));
		});
	});

	// croppie settings
    $uploadCrop.croppie({
        viewport: {
            width: 250,
            height: 250,
            type: 'square'
        },
        boundary: {
            width: 300,
            height: 300
        },
		enableOrientation: true
    });

    $('#upload').on('change', function () { readFile(this); });
    $('.form_submit').on('click', function (ev) {
        $uploadCrop.croppie('result', {
            type: 'canvas',
            size: 'original'
        }).then(function (resp) {
            $('#imagebase64').val(resp);
           $('#profile_form').submit();
        });
	return false;
    });
	
});
</script>
  
