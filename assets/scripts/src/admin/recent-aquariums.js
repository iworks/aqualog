/**
 * Recent Aquariums Dropdown Script
 * 
 * Handles dropdown menu functionality for recent aquariums in the admin dashboard.
 */
/* global window, jQuery, wp, document */

(function($) {
	'use strict';
	$(document).ready(function($) {
	$('.aqualog-dropdown-toggle').on('click', function(e) {
		e.preventDefault();
		e.stopPropagation();
		
		var $dropdown = $(this).closest('.aqualog-dropdown');
		var $menu = $dropdown.find('.aqualog-dropdown-menu');
		
		// Close other dropdowns
		$('.aqualog-dropdown-menu').not($menu).removeClass('is-open');
		
		// Toggle current dropdown
		$menu.toggleClass('is-open');
	});
	
	// Close dropdowns when clicking outside
	$(document).on('click', function() {
		$('.aqualog-dropdown-menu').removeClass('is-open');
	});
	
	// Prevent dropdown menu clicks from closing the menu
	$('.aqualog-dropdown-menu').on('click', function(e) {
		e.stopPropagation();
	});
});
})(jQuery);