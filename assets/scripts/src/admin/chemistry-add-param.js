/**
 * AquaLog Chemistry Add Parameter JavaScript
 *
 * Handles the chemistry form for adding/editing water chemistry parameters.
 * Uses WordPress wp.template for dynamic form rendering and AJAX submission.
 *
 * @package    iWorks
 * @subpackage AquaLog
 * @author     Marcin Pietrzak <marcin@iworks.pl>
 * @copyright  2026 Marcin Pietrzak
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 * @version    1.0.0
 * @since      1.0.0
 */

/* global window, jQuery, wp, document */

(function($) {
	'use strict';

	var field_value_class = '.aqualog-chemistry-item-body-scale-value';

	// Initialize chemistry form functionality
	window.aqualog = window.aqualog || {};
	window.aqualog.chemistry = window.aqualog.chemistry || {};

	/**
	 * Store form data for template rendering
	 */
	window.aqualog.chemistry.formData = {};

	/**
	 * Set form data and render the chemistry form
	 *
	 * @param {Object} data Form data including parameters, values, etc.
	 */
	window.aqualog.chemistry.setFormData = function(data) {
		window.aqualog.chemistry.formData = data;
		data.style = window.aqualog.chemistry.getScaleStyle();
		data.range_step = Math.min((data.range[1] - data.range[0]) / 50, 10);
		if (data.range_step > 0.05 && data.range_step < 0.5) {
			data.range_step = 0.1;
		} else if (data.range_step > 0.5 && data.range_step < 2) {
			data.range_step = 1;
		} else if (data.range_step > 2 && data.range_step < 5) {
			data.range_step = 2;
		} else if (data.range_step > 5 && data.range_step < 10) {
			data.range_step = 5;
		}
		data.range_step = Math.round(data.range_step * 100) / 100;
		data.step_big = data.range_step * 10;
		data.step_small = data.range_step * 2;
		window.aqualog.chemistry.formData = data;
	};

	window.aqualog.chemistry.scaleItem = function(range) {
		var args = window.aqualog.chemistry.formData;
		var min = args.range[0];
		var max = args.range[1];
		var length = (max - min) * 1000;
		var start = (args[range][0] - min) * 100000 / length;
		var end = (args[range][1] - min) * 100000 / length;
		return [start, end];
	};

	window.aqualog.chemistry.getScaleStyle = function() {
		var danger = window.aqualog.chemistry.scaleItem('danger');
		var safety = window.aqualog.chemistry.scaleItem('safety');
		var ideal = window.aqualog.chemistry.scaleItem('ideal');
		var style = 'background: linear-gradient(';
		style += 'to right,';
		style += 'var(--aqualog-settings-danger) ' + danger[0] + '% ' + safety[0] + '%,';
		style += 'var(--aqualog-settings-safety) ' + safety[0] + '% ' + ideal[0] + '%,';
		style += 'var(--aqualog-settings-ideal) ' + ideal[0] + '% ' + ideal[1] + '%,';
		style += 'var(--aqualog-settings-safety) ' + ideal[1] + '% ' + safety[1] + '%,';
		style += 'var(--aqualog-settings-danger) ' + safety[1] + '% 100%);';
		return style;
	};

	/**
	 * Render the chemistry form using wp.template
	 */
	window.aqualog.chemistry.renderForm = function() {
		if (!window.aqualog.chemistry.formData) {
			window.console.warn('AquaLog: No form data available for rendering');
			return;
		}
		var template = wp.template('aqualog-chemistry-form');
		var formHTML = template(
			window.aqualog.chemistry.formData
		);

		$('.aqualog-modal').html(formHTML);

		// Initialize form handlers
		window.aqualog.chemistry.initializeForm();
	};

	/**
	 * Initialize form event handlers and validation
	 */
	window.aqualog.chemistry.initializeForm = function() {
		var $form = $('#aqualog-chemistry-measurement-form');
		var args = window.aqualog.chemistry.formData;

		if (!$form.length) {
			return;
		}

		// Handle form submission
		$form.on('submit', function(e) {
			e.preventDefault();
			window.aqualog.chemistry.submitForm($(this));
		});

		// Clear error state on input
		$form.find(field_value_class).on('input', function() {
			$(this).removeClass('error');
		});
		/**
		 * set min/max
		 */
		$form.find(field_value_class)
			.val(args.value)
			.attr('min', args.range[0])
			.attr('max', args.range[1])
			.attr('step', args.range_step)
			.on('input change keyup keydown paste cut focus blur', function() {
				var value = $(this).val();
				$form.find('.aqualog-chemistry-item-body-scale-slider').slider('value', value);
			});
		/**
		 * Initialize slider
		 */
		if ($.fn.slider) {
			$form.find('.aqualog-chemistry-item-body-scale-slider').slider({
				min: args.range[0],
				max: args.range[1],
				step: args.range_step,
				value: args.value,
				change: function(event, ui) {
					$form.find(field_value_class).val(ui.value);
				}
			});
		}
		$('.aqualog-chemistry-item-body-scale-button').on('click', function() {
			var value = $(this).data('value');
			var current = parseFloat($form.find(field_value_class).val()) || 0;
			var new_value = current + parseFloat(value);
			$form.find(field_value_class).val(new_value);
			$form.find('.aqualog-chemistry-item-body-scale-slider').slider('value', new_value);
		});
	};

	/**
	 * Validate form inputs
	 *
	 * @param {jQuery} $form Form element
	 * @return {boolean} True if valid, false otherwise
	 */
	window.aqualog.chemistry.validateForm = function($form) {
		var isValid = true;

		$form.find(field_value_class).each(function() {
			var $input = $(this);
			var value = parseFloat($input.val());
			var min = parseFloat($input.data('range-min'));
			var max = parseFloat($input.data('range-max'));

			if ($input.val() && (isNaN(value) || value < min || value > max)) {
				$input.addClass('error');
				isValid = false;
			} else {
				$input.removeClass('error');
			}
		});

		return isValid;
	};

	/**
	 * Submit form via AJAX
	 *
	 * @param {jQuery} $form Form element
	 */
	window.aqualog.chemistry.submitForm = function($form) {
		var $submit = $form.find('.aqualog-form-submit');
		var originalText = $submit.html();

		// Validate form
		if (!window.aqualog.chemistry.validateForm($form)) {
			window.aqualog.showNotice('error', window.aqualog.i18n.messages.invalidValues);
			return;
		}

		// Show loading state
		$submit.prop('disabled', true)
			.html('<span class="dashicons dashicons-update spin"></span> ' + window.aqualog.i18n.messages.saving);

		// Submit via AJAX
		$.ajax({
			url: window.aqualog.ajax_url,
			type: 'POST',
			data: {
				action: 'aqualog_chemistry_add_param',
				_wpnonce: window.aqualog.nonces.chemistry.add_param,
				value: $form.find(field_value_class).val(),
				key: window.aqualog.chemistry.formData.key,
				id: $form.data('aquarium-id'),
			},
			success: function(response) {
				if (response.success) {
					// Show success message
					window.aqualog.showNotice('success', response.data.message);

					// Close form after delay
					window.setTimeout(function() {
						window.aqualog.chemistry.closeForm();
						// Reload page to show updated values
						window.location.reload();
					}, 1500);
				} else {
					// Show error message
					window.aqualog.showNotice('error', response.data.message);
				}
			},
			error: function() {
				window.aqualog.showNotice('error', window.aqualog.i18n.messages.saveError);
			},
			complete: function() {
				// Restore button state
				$submit.prop('disabled', false).html(originalText);
			}
		});
	};

	/**
	 * Close chemistry form
	 */
	window.aqualog.chemistry.closeForm = function() {
		// Check if form is in modal
		var $modal = $('.aqualog-modal');
		if ($modal.length) {
			// Close modal instead
			window.aqualog.chemistry.closeModal();
		} else {
			// Close standalone form
			$('.aqualog-chemistry-form').fadeOut(300, function() {
				$(this).remove();
			});
		}
	};

	/**
	 * Show admin notice
	 *
	 * @param {string} type Notice type (success, error, warning)
	 * @param {string} message Notice message
	 */
	window.aqualog.showNotice = function(type, message) {
		var noticeClass = 'notice-' + type;
		var $notice = $('<div class="notice is-dismissible ' + noticeClass + '"><p>' + message + '</p></div>');

		// Insert notice at the top of the form or page
		var $target = $('.aqualog-chemistry-form').find('.aqualog-card-header');
		if ($target.length) {
			$('.aqualog-chemistry-form').find('.notice').detach();
			$notice.insertAfter($target);
		} else {
			$notice.insertBefore('.wrap h1');
		}

		// Auto-dismiss after 5 seconds for success notices
		if (type === 'success') {
			window.setTimeout(function() {
				$notice.fadeOut(function() {
					$notice.remove();
				});
			}, 5000);
		}
	};

	/**
	 * Show chemistry form as modal
	 */
	window.aqualog.chemistry.showModal = function() {
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
		window.aqualog.chemistry.renderForm();

		// Move form into modal
		$('.aqualog-chemistry-form').appendTo($modal);

		// Show modal with animation
		$overlay.fadeIn(300);
		$modal.fadeIn(300);

		// Handle overlay click to close
		$overlay.on('click', function() {
			window.aqualog.chemistry.closeModal();
		});

		// Handle escape key to close
		$(document).on('keydown.aqualog-modal', function(e) {
			if (e.keyCode === 27) { // Escape key
				window.aqualog.chemistry.closeModal();
			}
		});
	};

	/**
	 * Close chemistry form modal
	 */
	window.aqualog.chemistry.closeModal = function() {
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
		// Bind click handler to chemistry items
		$(document).on('click', '.aqualog-chemistry-item', function(e) {
			e.preventDefault();
			e.stopPropagation();

			window.aqualog.chemistry.setFormData(
				window.aqualog.chemistry.params[$(this).data('key')]
			);

			// Show modal with form
			window.aqualog.chemistry.showModal();
		});
	});

})(jQuery);