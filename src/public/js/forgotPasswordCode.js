const loading = new Loading();

$('#sendConfirmMail').click(e => {
	$('.content-forgot').html('');
	loading.modelSpinner();
	loading.renderModel('.content-forgot');

	$.ajax({
		url: '/forgot-password-code/sendCodeMail',
		type: 'GET',
		dataType: 'json',
		success: dados => {
			if (dados.status == 'OK') {
				window.location.reload();
			}
		},
		error: error => {
			console.log(error);
		}
	});
});

function digitNumber(e, maxLength = 6) {
	let text = e.target.value;
	if (e.keyCode < 48 || e.keyCode > 57 || text.length >= maxLength) {
		e.preventDefault();
	}
}

$('.input-code-complete').keypress(e => {
	digitNumber(e);
});