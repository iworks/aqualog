
jQuery(document).ready(function ($) {
    $('#iworks-aqualog-dashboard-message').on('click', '.notice-dismiss', function (e) {
        e.preventDefault();
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'iworks_aqualog_dismiss_message',
                nonce: '<?php echo wp_create_nonce( 'iworks_aqualog_dismiss_message' ); ?>'
					},
            success: function (response) {
                $('#iworks-aqualog-dashboard-message').fadeOut();
            }
        });
    });
});