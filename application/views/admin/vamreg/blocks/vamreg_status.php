<?php if($status == Vamreg::VAMREG_OK): ?>
	<div class="alert alert-success" role="alert">
		<i class="fa-solid fa-circle-check"></i>
		Declarations successfully sent to VAMREG.
	</div>
<?php elseif($status == Vamreg::VAMREG_ERR_VALID): ?>
	<div class="alert alert-danger" role="alert">
		<i class="fa-solid fa-triangle-exclamation"></i>
		Some declarations contained errors and were not sent. Please check the flagged declarations.
	</div>
<?php elseif($status == Vamreg::VAMREG_ERR_AUTH): ?>
	<div class="alert alert-danger" role="alert">
		<i class="fa-solid fa-triangle-exclamation"></i>
		Authentication error when communicating with VAMREG. Please check the API key and environment settings.
	</div>
<?php elseif($status == Vamreg::VAMREG_ERR_API): ?>
	<div class="alert alert-danger" role="alert">
		<i class="fa-solid fa-triangle-exclamation"></i>
		An error occurred while communicating with VAMREG. Please try again later.
	</div>
<?php elseif($status == Vamreg::VAMREG_NOTHING): ?>
	<div class="alert alert-info" role="alert">
		<i class="fa-solid fa-info-circle"></i>
		There are no new draft declarations to send for this quarter.
	</div>
<?php endif; ?>