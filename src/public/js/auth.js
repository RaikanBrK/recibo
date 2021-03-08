const icones = {
	success: {
		identificador: 'fa-check',
		classe: 'fas fa-check text-success',
	},
	warning: {
		identificador: 'fa-exclamation',
		classe: 'fas fa-exclamation text-danger warning-icon-input',
	},
}

class Controller {
	constructor() {
		this.nome;
		this.textNome;
		this.maxNumNome;

		this.email;
		this.textEmail;
		this.maxNumEmail;

		this.senha;
		this.textSenha;
		this.maxNumSenha;

		this.confirmSenha;
		this.confirmTextSenha;
		this.confirmMaxNumSenha;
	}

	createMensagemInput(seletor, text) {
		let input = $(seletor);
		let formGroup = input.closest('.form-group');
		let msg = formGroup.find('.msg-is-invalid');

		if (msg.length == 0) {
			formGroup.append(`
				<div class="msg-is-invalid">
					<p>${text}</p>
				</div>
			`);

			msg = formGroup.find('.msg-is-invalid');
			msg.slideDown('show');
		} else {
			msg.html(`
				<p>${text}</p>
			`);
		}
	}

	removeMensagemInput(seletor) {
		let input = $(seletor);
		let formGroup = input.closest('.form-group');
		let msg = formGroup.find('.msg-is-invalid');

		msg.slideUp('slow', () => {
			msg.remove();
		});
	}

	createIconValidated(seletor, icone = 'success') {
		let input = $(seletor);
		let formGroup = input.closest('.form-group');
		let validated = formGroup.find('.validated');

		if (validated.length == 0) {
			input.after(`
				<div class="input-group-icon validated">
					<i class="${icones[icone]['classe']}"></i>
				</div>
			`);
		} else {
			let icon = validated.find('.'+icones[icone]['identificador']);
			if (icon.length == 0) {
				validated.append(`<i class="${icones[icone]['classe']} ml-1"></i>`);
			}
		}
	}

	removeIconValidated(seletor, icone = 'all') {
		let input = $(seletor);
		let formGroup = input.closest('.form-group');
		let validated = formGroup.find('.validated');

		if (icone == 'all') {
			validated.remove();
		} else {
			let icon = validated.find('.'+icones[icone]['identificador']);
			icon.remove();
		}
	}

	newCiclo(attr, text, icone) {
		this.removeIconValidated(attr);
		this.createMensagemInput(attr, text);
		icone ? this.createIconValidated(attr, icone) : '';
	}

	// Nome
	__setNome(seletor) {
		this.nome = $(seletor);
	}

	__getNome() {
		return this.nome;
	}

	__setTextNome(text) {
		this.textNome = text;
	}

	__getTextNome() {
		return this.textNome;
	}

	__setMaxNumNome(num) {
		this.maxNumNome = num;
	}

	__getMaxNumNome() {
		return this.maxNumNome;
	}

	// Email
	__setEmail(seletor) {
		this.email = $(seletor);
	}

	__getEmail() {
		return this.email;
	}

	__setTextEmail(text) {
		this.textEmail = text;
	}

	__getTextEmail() {
		return this.textEmail;
	}

	__setMaxNumEmail(num) {
		this.maxNumEmail = num;
	}

	__getMaxNumEmail() {
		return this.maxNumEmail;
	}

	// Senha
	__setSenha(seletor) {
		this.senha = $(seletor);
	}

	__getSenha() {
		return this.senha;
	}

	__setTextSenha(text) {
		this.textSenha = text;
	}

	__getTextSenha() {
		return this.textSenha;
	}

	__setMaxNumSenha(num) {
		this.maxNumSenha = num;
	}

	__getMaxNumSenha() {
		return this.maxNumSenha;
	}

	// ConfirmSenha
	__setConfirmSenha(seletor) {
		this.confirmSenha = $(seletor);
	}

	__getConfirmSenha() {
		return this.confirmSenha;
	}

	__setTextConfirmSenha(text) {
		this.textConfirmSenha = text;
	}

	__getTextConfirmSenha() {
		return this.textConfirmSenha;
	}

	__setMaxNumConfirmSenha(num) {
		this.maxNumConfirmSenha = num;
	}

	__getMaxNumConfirmSenha() {
		return this.maxNumConfirmSenha;
	}
}

function capsLock(seletor) {
	function getAction(seletor) {
		let action = confirmSenha;
		if (seletor == '#senha') {
			action = senha;
		}
		return action;
	}

	var valid = false;
	let element = document.querySelector(seletor);
	element.addEventListener('keydown', function(event) {
		var flag = event.getModifierState && event.getModifierState('CapsLock');

		if (flag) {
			controller.createMensagemInput(getAction(seletor), 'O Caps Lock está ativo!');
			valid = true;
		} else if(valid == true) {
			controller.removeMensagemInput(getAction(seletor));
			valid = false;
		}
	});
}