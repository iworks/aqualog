/**
 * Recent Aquariums Dropdown Script
 * 
 * Handles dropdown menu functionality for recent aquariums in the admin dashboard.
 */
/* global window, jQuery, wp, document */

(function($) {
	'use strict';
	$(document).ready(function($) {
	$('.aquarium-log-dropdown-toggle').on('click', function(e) {
		e.preventDefault();
		e.stopPropagation();
		
		var $dropdown = $(this).closest('.aquarium-log-dropdown');
		var $menu = $dropdown.find('.aquarium-log-dropdown-menu');
		
		// Close other dropdowns
		$('.aquarium-log-dropdown-menu').not($menu).removeClass('is-open');
		
		// Toggle current dropdown
		$menu.toggleClass('is-open');
	});
	
	// Close dropdowns when clicking outside
	$(document).on('click', function() {
		$('.aquarium-log-dropdown-menu').removeClass('is-open');
	});
	
	// Prevent dropdown menu clicks from closing the menu
	$('.aquarium-log-dropdown-menu').on('click', function(e) {
		e.stopPropagation();
	});
});
})(jQuery);