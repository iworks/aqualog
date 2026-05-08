/* global window, document */
window.aqualog = window.aqualog || {};
window.aqualog.chemistry = window.aqualog.chemistry || {};
window.aqualog.chemistry.params = window.aqualog.chemistry.params || {};

/**
 * Set session cookie for aquarium selection
 */
window.aqualog.setAquariumCookie = function(aquariumId) {
	document.cookie = 'aquarium_id=' + aquariumId + '; path=/; max-age=3600'; // 1 hour session
};

/**
 * Initialize aquarium item click handlers
 */
window.aqualog.initAquariumItems = function() {
	var aquariumItems = document.querySelectorAll('a.aqualog-aquarium-item');

	aquariumItems.forEach(function(item) {
		item.addEventListener('click', function(e) {
			var aquariumId = this.getAttribute('data-aquarium-id');
			if (aquariumId) {
				window.aqualog.setAquariumCookie(aquariumId);
			}
		});
	});
};

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
	window.aqualog.initAquariumItems();
});