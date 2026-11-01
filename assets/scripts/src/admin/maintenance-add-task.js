/**
 * Aqualog Maintenance Add Task JavaScript
 *
 * Handles the maintenance form for adding tasks.
 * Uses WordPress wp.template for dynamic form rendering and AJAX submission,
 * the same way the chemistry "add parameter" form works.
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

	// Initialize maintenance functionality
	window.iworks_aqualog = window.iworks_aqualog || {};
	window.iworks_aqualog.maintenance = window.iworks_aqualog.maintenance || {};

	/**
	 * Render the maintenance form using wp.template
	 */
	window.iworks_aqualog.maintenance.renderForm = function() {
		var template = wp.template('aqualog-maintenance-form');
		$('.aqualog-modal').html(
			template({
				aquarium_id: window.iworks_aqualog.maintenance.aquarium_id || 0
			})
		);
		window.iworks_aqualog.maintenance.initializeForm();
	};

	/**
	 * Initialize form event handlers
	 */
	window.iworks_aqualog.maintenance.initializeForm = function() {
		var $form = $('#aqualog-maintenance-task-form');
		if (!$form.length) {
			return;
		}

		/**
		 * prefill title with the task type title when left empty
		 */
		$form.find('.aqualog-maintenance-task-type').on('change', function() {
			var key = $(this).val();
			var tasks = window.iworks_aqualog.maintenance.tasks || {};
			if (tasks[key]) {
				$form.find('.aqualog-maintenance-task-title').attr('placeholder', tasks[key].title);
			}
		});

		$form.on('submit', function(e) {
			e.preventDefault();
			window.iworks_aqualog.maintenance.submitForm($(this));
		});
	};

	/**
	 * Validate form inputs
	 *
	 * @param {jQuery} $form Form element
	 * @return {boolean} True if valid, false otherwise
	 */
	window.iworks_aqualog.maintenance.validateForm = function($form) {
		var isValid = true;
		var $type = $form.find('.aqualog-maintenance-task-type');
		if (!$type.val()) {
			$type.addClass('error');
			isValid = false;
		} else {
			$type.removeClass('error');
		}
		return isValid;
	};

	/**
	 * Submit form via AJAX
	 *
	 * @param {jQuery} $form Form element
	 */
	window.iworks_aqualog.maintenance.submitForm = function($form) {
		var $submit = $form.find('.aqualog-form-submit');
		var originalText = $submit.html();

		// Validate form
		if (!window.iworks_aqualog.maintenance.validateForm($form)) {
			window.iworks_aqualog.showNotice('error', window.iworks_aqualog.i18n.messages.invalidValues);
			return;
		}

		// Show loading state
		$submit.prop('disabled', true)
			.html('<span class="dashicons dashicons-update spin"></span> ' + window.iworks_aqualog.i18n.messages.saving);

		// Submit via AJAX
		$.ajax({
			url: window.iworks_aqualog.ajax_url,
			type: 'POST',
			data: {
				action: 'aquarium_log_maintenance_add_task',
				_wpnonce: window.iworks_aqualog.nonces.maintenance.add,
				id: $form.data('aquarium-id'),
				type: $form.find('.aqualog-maintenance-task-type').val(),
				title: $form.find('.aqualog-maintenance-task-title').val(),
				description: $form.find('.aqualog-maintenance-task-description').val(),
				status: $form.find('.aqualog-maintenance-task-status').val(),
				date: $form.closest('.aqualog-card').find('input[name="date"]').val()
			},
			success: function(response) {
				if (response.success) {
					// Show success message
					window.iworks_aqualog.showNotice('success', response.data.message);

					// Close form after delay
					window.setTimeout(function() {
						window.iworks_aqualog.maintenance.closeForm();
						// Reload page to show updated values
						window.location.reload();
					}, 1500);
				} else {
					// Show error message
					window.iworks_aqualog.showNotice('error', response.data.message);
				}
			},
			error: function() {
				window.iworks_aqualog.showNotice('error', window.iworks_aqualog.i18n.messages.saveError);
			},
			complete: function() {
				// Restore button state
				$submit.prop('disabled', false).html(originalText);
			}
		});
	};

	/**
	 * Close maintenance form
	 */
	window.iworks_aqualog.maintenance.closeForm = function() {
		window.iworks_aqualog.maintenance.closeModal();
	};

	/**
	 * Show maintenance form as modal
	 */
	window.iworks_aqualog.maintenance.showModal = function() {
		// Create modal overlay
		var $overlay = $('<div class="aqualog-modal-overlay"></div>');
		var $modal = $('<div class="aqualog-modal"></div>');

		/**
		 * Remove any existing modals to prevent duplicates
		 */
		$('.aqualog-modal-overlay, .aqualog-modal').remove();

		// Append to body
		$('body').append($overlay).append($modal);

		// Render form inside modal
		window.iworks_aqualog.maintenance.renderForm();

		// Show modal with animation
		$overlay.fadeIn(300);
		$modal.fadeIn(300);

		// Handle overlay click to close
		$overlay.on('click', function() {
			window.iworks_aqualog.maintenance.closeModal();
		});

		// Handle escape key to close
		$(document).on('keydown.aqualog-modal', function(e) {
			if (e.keyCode === 27) { // Escape key
				window.iworks_aqualog.maintenance.closeModal();
			}
		});
	};

	/**
	 * Close maintenance form modal
	 */
	window.iworks_aqualog.maintenance.closeModal = function() {
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
			$(document).off('keydown.aqualog-modal');
		}
	};

	// Initialize when DOM is ready
	$(document).ready(function() {
		// Bind click handler to the "Add Task" quick action
		$(document).on('click', '#aqualog-add-task-trigger', function(e) {
			e.preventDefault();
			e.stopPropagation();

			// Show modal with form
			window.iworks_aqualog.maintenance.showModal();
		});
	});

})(jQuery);
