jQuery(document).ready(function($) {
	setTimeout(() => {
		$('#containerReport').slideDown(1600, function() {
			let timeClose = $('#report').attr('data-time');
			if (timeClose != 'infinity') {
				setTimeout(() => {
					$('#containerReport').slideUp('slow');
				}, timeClose);
			}
		});
	}, 500);
});