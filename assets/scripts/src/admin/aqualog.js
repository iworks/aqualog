/* global window, document */
window.iworks_aquarium_log = window.iworks_aquarium_log || {};
window.iworks_aquarium_log.chemistry = window.iworks_aquarium_log.chemistry || {};
window.iworks_aquarium_log.chemistry.params = window.iworks_aquarium_log.chemistry.params || {};

/**
 * Set session cookie for aquarium selection
 */
window.iworks_aquarium_log.setAquariumCookie = function(aquariumId) {
	document.cookie = 'aquarium_id=' + aquariumId + '; path=/; max-age=3600'; // 1 hour session
};

/**
 * Initialize aquarium item click handlers
 */
window.iworks_aquarium_log.initAquariumItems = function() {
	var aquariumItems = document.querySelectorAll('a.aquarium-log-aquarium-item');

	aquariumItems.forEach(function(item) {
		item.addEventListener('click', function(e) {
			var aquariumId = this.getAttribute('data-aquarium-id');
			if (aquariumId) {
				window.iworks_aquarium_log.setAquariumCookie(aquariumId);
			}
		});
	});
};

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
	window.iworks_aquarium_log.initAquariumItems();
});