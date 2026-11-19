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

(function ($) {
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
	window.iworks_aqualog.calendar.renderDetails = function (entry) {
		var data = $.extend({}, entry);
		// Keep the badge label in sync with the (possibly changed) status.
		if (data.status) {
			data.status_label = data.status
				.replace(/_/g, ' ')
				.replace(/^./, function (c) {
					return c.toUpperCase();
				});
		}
		if (data.interval > 0) {
			data.repeat_label = (
				window.iworks_aqualog.calendar.everyDays || 'Every %d days'
			).replace('%d', data.interval);
		}
		var template = wp.template('aqualog-calendar-event-details');
		$('.aqualog-modal').html(template(data));
	};

	/**
	 * Show entry details as modal
	 *
	 * @param {Object} entry Entry data from the chip's data-entry attribute.
	 */
	window.iworks_aqualog.calendar.showDetails = function (entry) {
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
		$overlay.on('click', function () {
			window.iworks_aqualog.calendar.closeDetails();
		});

		// Handle escape key to close
		$(document).on('keydown.aqualog-calendar-modal', function (e) {
			if (e.keyCode === 27) {
				// Escape key
				window.iworks_aqualog.calendar.closeDetails();
			}
		});
	};

	/**
	 * Close entry details modal
	 */
	window.iworks_aqualog.calendar.closeDetails = function () {
		var $overlay = $('.aqualog-modal-overlay');
		var $modal = $('.aqualog-modal');

		if ($overlay.length || $modal.length) {
			// Fade out and remove
			$overlay.fadeOut(300, function () {
				$(this).remove();
			});
			$modal.fadeOut(300, function () {
				$(this).remove();
			});

			// Remove event listeners
			$(document).off('keydown.aqualog-calendar-modal');
		}
	};

	// Initialize when DOM is ready
	$(document).ready(function () {
		// Bind click handler to the calendar event chips
		$(document).on('click', '.aqualog-calendar-event', function (e) {
			e.preventDefault();
			e.stopPropagation();

			var entry = $(this).data('entry');
			if (entry) {
				window.iworks_aqualog.calendar.showDetails(entry);
			}
		});

		// "Edit Task" button inside the details modal
		$(document).on('click', '.aqualog-calendar-details-edit', function (e) {
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
					full_day: entry.full_day ? 1 : 0,
					interval: entry.interval || 0,
					date:
						entry.date_key +
						(entry.full_day
							? ''
							: entry.time
								? 'T' + entry.time
								: ''),
				},
			});
		});

		// "Completed" button inside the details modal — one click marks the
		// event as done (task or reminder alike).
		$(document).on(
			'click',
			'.aqualog-calendar-details-complete',
			function (e) {
				e.preventDefault();
				e.stopPropagation();

				var entry = window.iworks_aqualog.calendar.currentEntry;
				if (!entry) {
					return;
				}
				var $button = $(this);

				// Show loading state
				$button
					.prop('disabled', true)
					.html(
						'<span class="dashicons dashicons-update spin"></span> ' +
							(window.iworks_aqualog.i18n.messages.saving ||
								'Saving…'),
					);

				$.ajax({
					url: window.iworks_aqualog.ajax_url,
					type: 'POST',
					data: {
						action: 'aquarium_log_calendar_event_status',
						_wpnonce: window.iworks_aqualog.nonces.maintenance.save,
						kind: entry.kind,
						event_id: entry.id,
						status: 'completed',
						date: entry.date_key || '',
					},
					success: function (response) {
						if (response.success) {
							// Update only the clicked chip in the grid and
							// re-render modal state.
							$('.aqualog-calendar-event[data-entry]').each(
								function () {
									var chip = $(this).data('entry');
									if (
										chip &&
										chip.kind === entry.kind &&
										chip.id === entry.id &&
										chip.date_key === entry.date_key
									) {
										chip.status = 'completed';
										$(this).data('entry', chip);
										$(this)
											.removeClass(
												'task-pending task-in_progress task-completed',
											)
											.addClass('task-completed');
									}
								},
							);
							entry.status = 'completed';
							window.iworks_aqualog.showNotice(
								'success',
								response.data.message,
							);
							window.iworks_aqualog.calendar.renderDetails(entry);
						} else {
							window.iworks_aqualog.showNotice(
								'error',
								(response.data && response.data.message) ||
									window.iworks_aqualog.i18n.messages
										.saveError,
							);
						}
					},
					error: function () {
						window.iworks_aqualog.showNotice(
							'error',
							window.iworks_aqualog.i18n.messages.saveError,
						);
					},
					complete: function () {
						$button.prop('disabled', false);
					},
				});
			},
		);

		// "Delete" button inside the details modal. For repeating tasks a
		// confirmation asks whether future occurrences should be deleted too.
		$(document).on(
			'click',
			'.aqualog-calendar-details-delete',
			function (e) {
				e.preventDefault();
				e.stopPropagation();

				var entry = window.iworks_aqualog.calendar.currentEntry;
				if (!entry) {
					return;
				}
				/**
				 * Occurrences are materialized rows: series members carry
				 * series_id instead of a repeat interval, but they are part
				 * of a repeating series all the same.
				 */
				var repeating =
					parseInt(entry.interval, 10) > 0 ||
					parseInt(entry.series_id, 10) > 0;

				var doDelete = function (withFuture) {
					var $button = $('.aqualog-calendar-details-delete');
					$button
						.prop('disabled', true)
						.html(
							'<span class="dashicons dashicons-update spin"></span> ' +
								(window.iworks_aqualog.i18n.messages.saving ||
									'Saving…'),
						);

					$.ajax({
						url: window.iworks_aqualog.ajax_url,
						type: 'POST',
						data: {
							action: 'aquarium_log_maintenance_delete_task',
							_wpnonce:
								window.iworks_aqualog.nonces.maintenance.save,
							task_id: entry.id,
							occurrence_date: entry.date_key || '',
							with_future: withFuture ? 1 : 0,
						},
						success: function (response) {
							if (response.success) {
								window.iworks_aqualog.showNotice(
									'success',
									response.data.message,
								);
								window.iworks_aqualog.calendar.closeDetails();
								window.location.reload();
							} else {
								$button
									.prop('disabled', false)
									.html(
										'<span class="dashicons dashicons-trash"></span> ' +
											(window.iworks_aqualog.i18n.messages
												.delete || 'Delete'),
									);
								window.iworks_aqualog.showNotice(
									'error',
									(response.data && response.data.message) ||
										window.iworks_aqualog.i18n.messages
											.saveError,
								);
							}
						},
						error: function () {
							$button
								.prop('disabled', false)
								.html(
									'<span class="dashicons dashicons-trash"></span> ' +
										(window.iworks_aqualog.i18n.messages
											.delete || 'Delete'),
								);
							window.iworks_aqualog.showNotice(
								'error',
								window.iworks_aqualog.i18n.messages.saveError,
							);
						},
					});
				};

				if (!repeating) {
					// Non-repeating task: simple browser confirm.
					if (
						window.confirm(
							window.iworks_aqualog.i18n.messages
								.deleteTaskConfirm || 'Delete this task?',
						)
					) {
						doDelete(false);
					}
					return;
				}

				// Repeating task: custom modal asking about future occurrences.
				var $overlay = $('<div class="aqualog-modal-overlay"></div>');
				var $modal = $(
					'<div class="aqualog-modal aqualog-modal-delete-confirm"></div>',
				);
				var i18n = window.iworks_aqualog.i18n;
				$modal.html(
					'<button type="button" class="aqualog-modal-close aqualog-delete-cancel" aria-label="' +
						(i18n.messages.cancel || 'Cancel') +
						'"><span class="dashicons dashicons-no-alt"></span></button>' +
						'<div class="aqualog-card"><div class="aqualog-card-header"><h2>' +
						(i18n.messages.deleteFutureTitle || 'Delete task') +
						'</h2></div>' +
						'<p>' +
						(i18n.messages.deleteFutureQuestion ||
							'Delete all future occurrences as well?') +
						'</p>' +
						'<div class="aqualog-form-actions">' +
						'<button type="button" class="button button-primary aqualog-delete-future">' +
						(i18n.messages.deleteFutureYes ||
							'Yes, delete future too') +
						'</button>' +
						'<button type="button" class="button button-primary aqualog-delete-single">' +
						(i18n.messages.deleteFutureNo || 'No, only this one') +
						'</button>' +
						'</div></div>',
				);
				$('body').append($overlay).append($modal);
				$overlay.fadeIn(300);
				$modal.fadeIn(300);

				$modal.on('click', '.aqualog-delete-future', function () {
					$overlay.remove();
					$modal.remove();
					doDelete(true);
				});
				$modal.on('click', '.aqualog-delete-single', function () {
					$overlay.remove();
					$modal.remove();
					doDelete(false);
				});
				$modal.on('click', '.aqualog-delete-cancel', function () {
					$overlay.fadeOut(150, function () {
						$(this).remove();
					});
					$modal.fadeOut(150, function () {
						$(this).remove();
					});
				});
				$overlay.on('click', function () {
					$overlay.remove();
					$modal.remove();
				});
			},
		);

		// "Close" button inside the details modal
		$(document).on(
			'click',
			'.aqualog-calendar-details-close',
			function (e) {
				e.preventDefault();
				e.stopPropagation();
				window.iworks_aqualog.calendar.closeDetails();
			},
		);
	});
})(jQuery);
