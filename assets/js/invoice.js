
function store_msg()
{
	$.ajax({
			method: 'POST',
			url: URL_STORE_MESSAGE_API,
			data: {
				msg: $("#msg").val(),
				msg_invoice: $("#msg_invoice").val(),
			}
		});
}

document.addEventListener("DOMContentLoaded", function(){

	$("#invoice").addClass('active');

	$("#select_card").click(function(){
		$("#card_selector").prop("checked", true);
		$("#card_value").val($("#total_brut").val());
		$("#cash_value").val("");
		$("#transfer_value").val("");
	});
    
	$("#select_cash").click(function(){
		$("#cash_selector").prop("checked", true);
		$("#cash_value").val($("#total_brut").val());
		$("#card_value").val("");
		$("#transfer_value").val("");
	});

	$("#select_transfer").click(function(){
		$("#cash_selector").prop("checked", true);
		$("#transfer_value").val($("#total_brut").val());
		$("#cash_value").val("");
		$("#card_value").val("");
	});

	$("#calculate").click(function() {
		var cash = parseFloat($("#cash_value").val());
		var card = parseFloat($("#card_value").val());
		var transfer = parseFloat($("#transfer_value").val());
		var total = parseFloat($("#total_brut").val());

		if (isNaN(cash)) {cash = 0.0}
		if (isNaN(card)) {card = 0.0}
		if (isNaN(transfer)) {transfer = 0.0}
		if (isNaN(total)) {return false;}
		var total_in = cash+card+transfer;

		$("#payment_info").val(Math.round((total-total_in)*100)/100);
	});

	$("#store_messages").click(function() {
		$("#store_messages").html('<i class="fas fa-sync fa-spin"></i> Loading');
		store_msg();
		setTimeout(function() { $("#store_messages").html('<i class="fa-solid fa-floppy-disk"></i> Stored !'); }, 1000);
	});


	// store message before we send on
	$("#bill_unpay").click(function() {
		$("#bill_unpay").html('<i class="fas fa-sync fa-spin"></i> Loading');
		store_msg();
		window.location.href = this.href;
	});


	$("#sendmail").click(function() {
		// confirm sending mail
		Swal.fire({
			title: 'Send mail ?',
			showCancelButton: true,
			confirmButtonText: 'Send',
		}).then((result) => {
			if (result.isConfirmed) {
				$.ajax({
					method: 'POST',
					url: URL_SENDMAIL_API
				}).done(function() {
					$("#sendmail").html('<i class="fas fa-paper-plane"></i> Send!').addClass("btn-secondary disabled");
				});
				Swal.fire(`email sent!`);
			} 
		});
	});

	$("#get_mail").click(async function() {
		const { value: email } = await Swal.fire({
			title: 'Input email address',
			input: 'email',
			inputLabel: 'Your email address',
			inputPlaceholder: 'Enter your email address'
		});

		if (email) {
			// store email
			$.ajax({
				method: 'POST',
				url: URL_STORE_MAIL,
				data: { email: email }
			});
			
			// send mail
			$.ajax({
				method: 'POST',
				url: URL_SENDMAIL_API,
			}).done(function() {
				$("#sendmail").html('<i class="fas fa-paper-plane"></i> Send!').addClass("btn-secondary disabled");
			});

			Swal.fire(`email sent!`);
		}
		});
});