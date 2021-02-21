jQuery(document).ready(function($) {
	setTimeout(() => {
		$('#report').slideDown('slow', function() {
			let timeClose = $('#report').attr('data-time');
			if (timeClose != 'infinity') {
				setTimeout(() => {
					$('#report').slideUp('slow', function() {
						$('#containerReport').hide();
					});
				}, timeClose);
			}
		});
	}, 500);
});