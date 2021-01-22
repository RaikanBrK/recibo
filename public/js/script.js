$(document).ready(() => {
	$('#userContentHeader').click(() => {
		$('.content-suport-user').slideToggle('slow');
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
		}
	})

});