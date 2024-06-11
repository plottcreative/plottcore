<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo $GLOBALS['googleMapsKey']; ?>"></script>
<script type="text/javascript">
	// Renders a Google Map onto the selected element
	function initMap(el) {
		// Find marker elements within map.
		const markers = el.querySelectorAll('.marker');

		// Create generic map.
		const mapArgs = {
			zoom: 17,
			mapTypeId: google.maps.MapTypeId.ROADMAP,
		};

		const map = new google.maps.Map(el, mapArgs);

		// Add markers.
		map.markers = [];
		markers.forEach(function(markerEl) {
			initMarker(markerEl, map);
		});

		// Center map based on markers.
		centerMap(map);

		// Return map instance.
		return map;
	}

	function initMarker(markerEl, map) {
		// Get position from marker.
		const lat = parseFloat(markerEl.getAttribute('data-lat'));
		const lng = parseFloat(markerEl.getAttribute('data-lng'));
		const latLng = {
			lat: lat,
			lng: lng
		};

		// Create marker instance.
		const marker = new google.maps.Marker({
			position: latLng,
			map: map,
			icon: '<?php echo get_template_directory_uri(); ?>/assets/dist/img/contact/map-marker.svg'
		});

		map.markers.push(marker);

		// If marker contains HTML, add it to an infoWindow.
		if (markerEl.innerHTML) {
			const infowindow = new google.maps.InfoWindow({
				content: markerEl.innerHTML
			});

			marker.addListener('click', function() {
				infowindow.open(map, marker);
			});
		}
	}

	function centerMap(map) {
		const bounds = new google.maps.LatLngBounds();
		map.markers.forEach(function(marker) {
			bounds.extend({
				lat: marker.position.lat(),
				lng: marker.position.lng()
			});
		});

		if (map.markers.length === 1) {
			map.setCenter(bounds.getCenter());
		} else {
			map.fitBounds(bounds);
		}
	}

	document.addEventListener('DOMContentLoaded', function() {
		const mapElements = document.querySelectorAll('.acf-map');
		mapElements.forEach(function(mapEl) {
			initMap(mapEl);
		});
	});

</script>
