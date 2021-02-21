$(document).ready(() => {
	$('#userContentHeader').click(() => {
		$('.content-suport-user').slideToggle('slow');

		$('.icon-down').toggleClass('icon-select');
	});

	$(window).click(function(e) {
		let element = $(e.target);

		if (
			element.hasClass('content-suport-user') == false
			&&
			element.closest('.content-suport-user').length == 0
			&&
			(element.attr('id') != "userContentHeader" && element.closest("#userContentHeader").length == 0)
		) {
			$('.content-suport-user').slideUp('slow');
			$('.icon-down').removeClass('icon-select');
		}
	});

	$('#hamburguer').click(function() {
		let burguer = $('#hamburguer > .hamburguer');
		burguer.toggleClass('burguerActive');

		if (burguer.hasClass('burguerActive') && $('#menu').hasClass('show') ) {

			burguer.removeClass('burguerActive')
		} else if(burguer.hasClass('burguerActive') == false && $('#menu').hasClass('show') == false) {

			burguer.addClass('burguerActive');
		}
	});
});