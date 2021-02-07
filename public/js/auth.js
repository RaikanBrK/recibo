function controllerFocusInputAuth(controller, classe = 'focusAuthInput') {
	const input = $(event.target);
	const inputGroup = $(input.closest('.input-group'));

	if (controller) {
		inputGroup.addClass(classe);
	} else {
		inputGroup.removeClass(classe);
	}
}


$('.input-auth').focus(() => {controllerFocusInputAuth(true)});
$('.input-auth').blur(() => {controllerFocusInputAuth(false)});