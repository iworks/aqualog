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
	$(document).ready(function($) {
		$('#iworks-aqualog-dashboard-message').on('click', '.notice-dismiss', function(e) {
			e.preventDefault();
			$.ajax({
				url: window.aqualog.ajax_url,
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
})(jQuery);