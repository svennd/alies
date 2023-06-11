<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <title>Login</title>

  <link href="<?php echo base_url(); ?>assets/css/sb-admin-2.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
	

</head>

<body class="bg-gradient-primary">

  <div class="container">

    <!-- Outer Row -->
    <div class="row justify-content-center">

      <div class="col-xl-10 col-lg-12 col-md-9">

        <div class="card o-hidden border-0 shadow-lg my-5">
          <div class="card-body p-0">
            <!-- Nested Row within Card Body -->
            <div class="row">
              <div class="col-lg-6 d-none d-lg-block bg-login-image"></div>
              <div class="col-lg-6">
                <div class="p-5">
                  <div class="text-center">
                    <h1 class="h4 text-gray-900 mb-4">Welcome Back!</h1>
                  </div>
                  <?php echo form_open("auth/login");?>
                    <div class="form-group">
                      <input type="email" name="identity" class="form-control form-control-user" id="email" aria-describedby="emailHelp" placeholder="Enter Email Address...">
                    </div>
                    <div class="form-group">
                    <div class="input-group">
                      <input type="password" name="password" class="form-control form-control-user" id="password" placeholder="Password" required>
                      <div class="input-group-append">
                        <button class="btn btn-outline-info" type="button" id="togglePassword">Show</button>
                      </div>
                    </div>
                    </div>
                    <div class="form-group">
                      <div class="custom-control custom-checkbox small">
                        <input type="checkbox" name="password" class="custom-control-input" id="customCheck">
                        <label class="custom-control-label" for="customCheck">Remember Metest</label>
                      </div>
                    </div>
					<input type="submit" class="btn btn-primary btn-user btn-block" name="submit" value="Login">
                  </form>
                  <hr>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

    </div>

  </div>

  <script>
    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
      var passwordField = document.getElementById('password');
      
      if (passwordField.type === 'password') {
        passwordField.type = 'text';
        this.textContent = 'Hide';
      } else {
        passwordField.type = 'password';
        this.textContent = 'Show';
      }
    });
  </script>
</body>

</html>
