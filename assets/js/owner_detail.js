document.addEventListener("DOMContentLoaded", function(){
	$("#home").addClass('active');

	$('.add_weight').on('click', function () {
		let pet_id = $(this).data('pet-id');

		Swal.fire({
		input: 'text',
		inputLabel: LANG_WEIGHT,
		inputPlaceholder: LANG_ADD_WEIGHT,
		showLoaderOnConfirm: true,
		showCancelButton: true,
			preConfirm: (data_field_input) => {
				$.ajax({
					method: 'POST',
					url: URL_PUSH_WEIGHT + '/' + pet_id,
					data: {
						weight: `${data_field_input}`,
					},
					error: function(xhr, status, error) {
					// handle error
					Swal.fire({
						title: 'Error',
						text: 'An error occurred.',
						icon: 'error'
					});
					}
				});
			},
		}).then((result) => {
			// only when confirm, if cancel then do nothing
			if (result.isConfirmed) {
				$("#weight_" + pet_id).html(result.value + ' kg <i class="fa-solid fa-check" style="color: #63E6BE;"></i>');
			}
		}); // end swal
	});  // end event
});