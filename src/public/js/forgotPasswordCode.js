const loading = new Loading();

function sendCodeMail() {
	$('.content-forgot').html('');
	loading.modelSpinner();
	loading.renderModel('.content-forgot');

	$.ajax({
		url: '/forgot-password-code/sendCodeMail',
		type: 'GET',
		dataType: 'json',
		success: dados => {
			console.log(dados);
			if (dados.status == 'OK') {
				window.location.reload();
			} else {
				$('.content-forgot').html(`
					<span class="muted">${dados.msg}</span>
					${dados.dados.msgAdd}
				`);
			}
		},
		error: error => {
			console.log(error);
		}
	});
}

$('#sendConfirmMail').click(e => {
	sendCodeMail();
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

function cronometro(dataTime, prefix, callback) {
	if (dataTime == 'limite') {
		dataTime = 0;
		var controller = false;	
	} else {
		var controller = true;
	}
	if (dataTime > 0) {
		var cronometro = setInterval(() => {
			let minutos = Math.floor(dataTime / 60);
			let segundos = (dataTime - (minutos * 60));
			segundos = segundos < 10 ? '0'+segundos : segundos;

			$(`#${prefix}Minutos`).html(minutos+':');
			$(`#${prefix}Segundos`).html(segundos);

			if (minutos > 0) {
				$(`#${prefix}Prefix`).html('minutos');
			} else {
				$(`#${prefix}Minutos`).hide();
				$(`#${prefix}Prefix`).html('segundos');
			}

			dataTime -= 1;

			if (dataTime < 1) {
				callback(controller);
				clearInterval(cronometro);
			}
		}, 1000);
		setTimeout(() => {
			$(`#${prefix}ContentCron`).slideDown();
		}, 1000);
	} else {
		callback(controller);
	}
}

const divRepeat = $('#repeatContentCron');
let dataTime = divRepeat.attr('data-time');

function repeatCode(controller) {
	if (controller) {
		divRepeat.html(`
			<button type="button" id="confirmRepeatCode" tabindex="9">Pedir outro código</button>
		`);
	} else {
		divRepeat.html(`
			<span class="muted">Limite de 3 códigos no período de 2 horas. Tente novamente mais tarde</span>
		`);
	}
	divRepeat.slideDown();
}

$('body').on('click', '#confirmRepeatCode', function(event) {
	sendCodeMail();
});

const divExpired = $('#expiredContentCron');
let dataTimeExpired = divExpired.attr('data-time');

function timeExpired() {
	$('.time-expired-code').html(`
		Seu código expirou!
	`);
	divExpired.slideDown();
}

cronometro(dataTime, 'repeat', repeatCode);
cronometro(dataTimeExpired, 'expired', timeExpired);