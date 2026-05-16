/* global jQuery, document, window */
jQuery(document).ready(function($) {
	var $container = $('#size');
	$('input', $container).on('keyup keypress blur change', function() {
		var $capacity = $('input[name="_iw_size_capacity"]', $container);
		if (!$capacity.val()) {
			$capacity.data('autocalc', true);
		}
		if ($capacity.data('autocalc')) {
			$capacity.val(
				parseInt(
					parseInt($('input[name="_iw_size_depth"]', $container).val()) *
					parseInt($('input[name="_iw_size_width"]', $container).val()) *
					parseInt($('input[name="_iw_size_height"]', $container).val()) /
					1000
				)
			);
		}
	});
});