const controller = new Controller();

/*
  *** Nome
*/
controller.__setNome('#nome');
const nome = controller.__getNome();

nome.keypress(e => {
	controller.__setTextNome(e.target.value.trim());
	let text = controller.__getTextNome();
	let textSplit = text.split(' ');

	if (e.keyCode == 32 && textSplit.length > 1) {
		e.preventDefault();
		controller.newCiclo(
			nome,
			'Digite somente o nome e sobrenome'
		);
	}

	controller.__setMaxNumNome(40);
	num = controller.__getMaxNumNome();
	if (text.length >= num) {
		controller.newCiclo(
			nome,
			`Você não pode ultrapassar o limite de ${num} letras`
		);
		e.preventDefault();
	}
});

function blurNome(send = false) {
	controller.__setTextNome(nome.val().trim());
	let text = controller.__getTextNome();

	controller.removeMensagemInput(nome);

	if (text.length < 5 && (text.length > 0 || send) ) {
		controller.newCiclo(nome, 'Nome muito curto', 'warning');

	} else if(text.length > controller.__getMaxNumNome()) {
		controller.newCiclo(
			nome,
			`Não é permitido nome com mais de ${controller.__getMaxNumNome()} letras`
		);
	} else if(text.length >= 5) {
		controller.removeMensagemInput(nome);
		controller.removeIconValidated(nome, 'warning');
		controller.createIconValidated(nome, 'success');
		return true;
	} else {
		controller.removeIconValidated(nome);
	}
	return false;
}

nome.blur(e => {
	blurNome();
});

/*
  *** Email
*/
controller.__setEmail('#email');

const email = controller.__getEmail();

email.keypress(e => {
	controller.__setTextEmail(e.target.value.trim());
	let text = controller.__getTextEmail();

	if (e.keyCode == 32) {
		controller.newCiclo(
			email,
			'Não é permitido espaços'
		);
		e.preventDefault();
	}

	controller.__setMaxNumEmail(100);
	let num = controller.__getMaxNumEmail();
	if (text.length >= num) {
		controller.newCiclo(
			email,
			`Não é permitido email com mais de ${num} letras`,
		);
		e.preventDefault();
	}
});

function blurEmail(send = false) {
	controller.__setTextEmail(email.val().trim());
	let text = controller.__getTextEmail();

	controller.removeMensagemInput(email);

	if (
		text.length < 5 && (text.length > 0 || send)
	) {
		controller.newCiclo(email, 'Email muito curto', 'warning');
	} else if( (text.indexOf('@') === -1 || text.indexOf('.') === -1) && text.length > 0) {
		controller.newCiclo(email, 'Algo parece errado, verifique seu email', 'warning');
	} else if(text.length > 5) {
		controller.removeMensagemInput(email);
		controller.removeIconValidated(email, 'warning');
		controller.createIconValidated(email, 'success');
		return true;
	} else {
		controller.removeIconValidated(email);
	}

	return false;
}

email.blur(e => {
	blurEmail();
});

/*
  *** Senha
*/
controller.__setSenha('#senha');

const senha = controller.__getSenha();

capsLock('#senha');

controller.__setMaxNumSenha(50);
senha.keypress(e => {
	controller.__setTextSenha(e.target.value.trim());
	let text = controller.__getTextSenha();

	let num = controller.__getMaxNumSenha();
	if (text.length >= num) {
		controller.newCiclo(
			senha,
			`Não é permitido senha com mais de ${num} caracteres`,
		);
		e.preventDefault();
	}
});

function blurSenha(send = false) {
	controller.__setTextSenha(senha.val().trim());
	let text = controller.__getTextSenha();
	controller.removeMensagemInput(senha);

	if (
		text.length < 8 && (text.length > 0 || send)
	) {
		controller.newCiclo(senha, 'Senha muito curta', 'warning');
	} else if(text.length >= 8) {
		controller.removeMensagemInput(senha);
		controller.removeIconValidated(senha, 'warning');
		controller.createIconValidated(senha, 'success');
		return true;
	} else {
		controller.removeIconValidated(senha);
	}
	return false;
}

senha.blur(e => {
	blurSenha();
});

/*
  *** ConfirmSenha
*/
controller.__setConfirmSenha('#confirmSenha');

const confirmSenha = controller.__getConfirmSenha();

capsLock('#confirmSenha');

confirmSenha.keypress(e => {
	controller.__setTextConfirmSenha(e.target.value.trim());
	let text = controller.__getTextConfirmSenha();

	let num = controller.__getMaxNumSenha();
	if (text.length >= num) {
		controller.newCiclo(
			confirmSenha,
			`Não é permitido senha com mais de ${num} caracteres`,
		);
		e.preventDefault();
	}
});

function blurConfirmSenha(send = false) {
	controller.__setTextConfirmSenha(confirmSenha.val().trim());
	let text = controller.__getTextConfirmSenha();

	controller.removeMensagemInput(confirmSenha);

	if (
		text != controller.__getTextSenha() && (text.length > 0 || send)
	) {
		controller.newCiclo(confirmSenha, 'As senhas não coincidem', 'warning');
	} else if(text.length >= 8) {
		controller.removeMensagemInput(confirmSenha);
		controller.removeIconValidated(confirmSenha, 'warning');
		controller.createIconValidated(confirmSenha, 'success');
		return true;
	} else {
		controller.removeIconValidated(confirmSenha);
	}
	return false;
}

confirmSenha.blur(e => {
	blurConfirmSenha();
});

$('#btn-auth').click(e => {
	let validNome = blurNome(true);
	let validEmail = blurEmail(true);
	let validSenha = blurSenha(true);
	let validConfirmSenha = blurConfirmSenha(true);
	let validChecked = $("#confirmTerms").is(":checked");
	let result = validNome && validEmail && validSenha && validConfirmSenha && validChecked ? true : e.preventDefault();

	if (validChecked == false) {
		controller.createMensagemInput('#confirmTerms', 'Você deve aceitar os termos para criar sua conta.');
	}
});

$('#confirmTerms').change(function() {
	let validChecked = $(this).is(":checked");

	if (validChecked) {
		controller.removeMensagemInput('#confirmTerms');
	}
});

$(jQuery(document).ready(function($) {
	if ($('.verifiqueInput').length != 0) {
		blurNome(true);
		blurEmail(true);
	}
}));