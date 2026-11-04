/**
 * Aqualog Calendar JavaScript
 *
 * Handles the calendar entry details preview. Clicking an event chip
 * opens a read-only details modal (rendered from a wp.template); for
 * tasks it offers an "Edit Task" button that delegates to the
 * maintenance edit flow.
 *
 * @package    iWorks
 * @subpackage Aqualog
 * @author     Marcin Pietrzak <marcin@iworks.pl>
 * @copyright  2026 Marcin Pietrzak
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 * @version    1.0.0
 * @since      1.0.0
 */

/* global window, jQuery, wp, document */

(function($) {
	'use strict';

	// Initialize calendar functionality
	window.iworks_aqualog = window.iworks_aqualog || {};
	window.iworks_aqualog.calendar = window.iworks_aqualog.calendar || {};

	/**
	 * Currently previewed entry
	 */
	window.iworks_aqualog.calendar.currentEntry = null;

	/**
	 * Render the details modal content using wp.template
	 *
	 * @param {Object} entry Entry data.
	 */
	window.iworks_aqualog.calendar.renderDetails = function(entry) {
		var data = $.extend({}, entry);
		if (data.kind === 'reminder' && data.interval > 0) {
			data.repeat_label = (window.iworks_aqualog.calendar.everyDays || 'Every %d days').replace('%d', data.interval);
		}
		var template = wp.template('aqualog-calendar-event-details');
		$('.aqualog-modal').html(template(data));
	};

	/**
	 * Show entry details as modal
	 *
	 * @param {Object} entry Entry data from the chip's data-entry attribute.
	 */
	window.iworks_aqualog.calendar.showDetails = function(entry) {
		window.iworks_aqualog.calendar.currentEntry = entry;

		/**
		 * Remove any existing modals to prevent duplicates
		 */
		$('.aqualog-modal-overlay, .aqualog-modal').remove();

		// Create modal overlay
		var $overlay = $('<div class="aqualog-modal-overlay"></div>');
		var $modal = $('<div class="aqualog-modal"></div>');

		// Append to body
		$('body').append($overlay).append($modal);

		// Render details inside modal
		window.iworks_aqualog.calendar.renderDetails(entry);

		// Show modal with animation
		$overlay.fadeIn(300);
		$modal.fadeIn(300);

		// Handle overlay click to close
		$overlay.on('click', function() {
			window.iworks_aqualog.calendar.closeDetails();
		});

		// Handle escape key to close
		$(document).on('keydown.aqualog-calendar-modal', function(e) {
			if (e.keyCode === 27) { // Escape key
				window.iworks_aqualog.calendar.closeDetails();
			}
		});
	};

	/**
	 * Close entry details modal
	 */
	window.iworks_aqualog.calendar.closeDetails = function() {
		var $overlay = $('.aqualog-modal-overlay');
		var $modal = $('.aqualog-modal');

		if ($overlay.length || $modal.length) {
			// Fade out and remove
			$overlay.fadeOut(300, function() {
				$(this).remove();
			});
			$modal.fadeOut(300, function() {
				$(this).remove();
			});

			// Remove event listeners
			$(document).off('keydown.aqualog-calendar-modal');
		}
	};

	// Initialize when DOM is ready
	$(document).ready(function() {
		// Bind click handler to the calendar event chips
		$(document).on('click', '.aqualog-calendar-event', function(e) {
			e.preventDefault();
			e.stopPropagation();

			var entry = $(this).data('entry');
			if (entry) {
				window.iworks_aqualog.calendar.showDetails(entry);
			}
		});

		// "Edit Task" button inside the details modal
		$(document).on('click', '.aqualog-calendar-details-edit', function(e) {
			e.preventDefault();
			e.stopPropagation();

			var entry = window.iworks_aqualog.calendar.currentEntry;
			if (!entry || entry.kind !== 'task') {
				return;
			}

			// Close the details modal, then open the edit modal
			window.iworks_aqualog.calendar.closeDetails();
			window.iworks_aqualog.maintenance.showModal({
				task: {
					id: entry.id,
					aquarium_id: entry.aquarium_id,
					type: entry.type,
					title: entry.title,
					description: entry.description,
					status: entry.status,
					date: entry.date_key + (entry.time ? 'T' + entry.time : '')
				}
			});
		});

		// "Close" button inside the details modal
		$(document).on('click', '.aqualog-calendar-details-close', function(e) {
			e.preventDefault();
			e.stopPropagation();
			window.iworks_aqualog.calendar.closeDetails();
		});
	});

})(jQuery);
