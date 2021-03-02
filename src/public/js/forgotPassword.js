const controller = new Controller();

$('#email').blur(e => {
	let element = $(e.target);
	let text = element.val();

	if (text.length > 1) {
		$.ajax({
			url: '/email_exist',
			type: 'GET',
			dataType: 'json',
			data: {email: text},
			success: data => {
				if (data.status == 'ERROR') {
					controller.createMensagemInput($('#email'), data.msg);
				} else if(data.status == 'OK') {
					controller.removeMensagemInput($('#email'));
				}
			},
			error: $error => {
				console.log($error);
			}
		});
	}
});

$('#email').focus(e => {
	controller.removeMensagemInput($('#email'));
});

$('.submit-next').click(e => {
	var error = 0;

	if ($('#email').length != 0) {
		$('#email').val().length > 2 ? '' : error++;
	}

	if (error > 0 ) {
		e.preventDefault();
	}
});