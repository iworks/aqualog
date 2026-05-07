jQuery(document).ready(function($) {
	$('#iworks-aqualog-dashboard-message').on('click', '.notice-dismiss', function(e) {
		e.preventDefault();
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'iworks_aqualog_dismiss_message',
				_wpnonce: window.aqualog.nonces.dismiss_message
			},
			success: function(response) {
				$('#iworks-aqualog-dashboard-message').fadeOut();
			}
		});
	});
});