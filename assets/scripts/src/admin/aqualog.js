/* global window, document */
window.iworks_aqualog = window.iworks_aqualog || {};
window.iworks_aqualog.chemistry = window.iworks_aqualog.chemistry || {};
window.iworks_aqualog.chemistry.params = window.iworks_aqualog.chemistry.params || {};

/**
 * Set session cookie for aquarium selection
 */
window.iworks_aqualog.setAquariumCookie = function(aquariumId) {
	document.cookie = 'aquarium_id=' + aquariumId + '; path=/; max-age=3600'; // 1 hour session
};

/**
 * Initialize aquarium item click handlers
 */
window.iworks_aqualog.initAquariumItems = function() {
	var aquariumItems = document.querySelectorAll('a.aqualog-aquarium-item');

	aquariumItems.forEach(function(item) {
		item.addEventListener('click', function(e) {
			var aquariumId = this.getAttribute('data-aquarium-id');
			if (aquariumId) {
				window.iworks_aqualog.setAquariumCookie(aquariumId);
			}
		});
	});
};

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
	window.iworks_aqualog.initAquariumItems();
});