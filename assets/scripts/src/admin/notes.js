/**
 * @file
 * Notes Admin Script
 * 
 * @since 1.0.0
 */

jQuery(document).ready(function($) {
	// Modal open/close
	$('#aqualog-add-note-trigger').on('click', function(e) {
		e.preventDefault();
		$('#aqualog-add-note-modal').show();
	});

	$('.aqualog-modal-close, .aqualog-modal-cancel').on('click', function() {
		$('#aqualog-add-note-modal').hide();
	});

	$(window).on('click', function(e) {
		if ($(e.target).hasClass('aqualog-modal')) {
			$('#aqualog-add-note-modal').hide();
		}
	});

	// Form submission
	$('#aqualog-add-note-form').on('submit', function(e) {
		e.preventDefault();

		var formData = $(this).serialize();
		formData += '&post_type=iw_note';

		$.ajax({
			url: aqualog.ajax_url,
			type: 'POST',
			data: formData,
			success: function(response) {
				if (response.success) {
					$('#aqualog-add-note-modal').hide();
					$('#aqualog-add-note-form')[0].reset();
					alert('<?php esc_html_e( 'Note added successfully!', 'PLUGIN_NAME' ); ?>');
					// Reload page to show new note
					location.reload();
				} else {
					alert('<?php esc_html_e( 'Error adding note. Please try again.', 'PLUGIN_NAME' ); ?>');
				}
			},
			error: function() {
				alert('<?php esc_html_e( 'Error adding note. Please try again.', 'PLUGIN_NAME' ); ?>');
			}
		});
	});
});
