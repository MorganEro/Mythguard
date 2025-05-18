class OpenStreetMap {
  constructor() {
    // Make this instance globally available
    window.openStreetMap = this;
    this.currentMap = null;
    this.routingControl = null;
    // Initialize marker icons
    this.markerIcons = {
      aetheric_spire: L.icon({
        iconUrl: `${mythguardData.theme_url}/images/markers/aetheric-spire.png`,
        shadowUrl:
          'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41],
      }),
      twinspire_hall: L.icon({
        iconUrl: `${mythguardData.theme_url}/images/markers/twinspire-hall.png`,
        shadowUrl:
          'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41],
      }),
      ironroot_bastion: L.icon({
        iconUrl: `${mythguardData.theme_url}/images/markers/ironroot-bastion.png`,
        shadowUrl:
          'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41],
      }),
    };

    // Find all map containers
    document.querySelectorAll('.acf-map').forEach(el => {
      this.new_map(el);
    });
  }

  new_map($el) {
    // Find all markers within this map
    const $markers = $el.querySelectorAll('.marker');

    // Create map in the container
    this.currentMap = L.map($el).setView([0, 0], 16);

    // Add Jawg Terrain style
    L.tileLayer(
      'https://tile.jawg.io/jawg-terrain/{z}/{x}/{y}{r}.png?access-token=rqimrYpFF2sqiWjuVYjMWOYOorJOwWhEgSbPodxG5B9CPbu7DVjFk3GfOFZzXAmL',
      {
        attribution:
          '<a href="https://www.jawg.io" target="_blank">&copy; Jawg</a> - <a href="https://www.openstreetmap.org" target="_blank">&copy; OpenStreetMap</a>',
        maxZoom: 22,
        subdomains: 'abcd',
      }
    ).addTo(this.currentMap);

    // Add all markers
    const bounds = [];
    $markers.forEach(marker => {
      this.add_marker(marker, this.currentMap, bounds);
    });

    // Center map to show all markers
    if (bounds.length) {
      this.currentMap.fitBounds(bounds, {
        padding: [50, 50],
        maxZoom: 13,
      });
    }
  }

  add_marker(marker, map, bounds) {
    const lat = parseFloat(marker.getAttribute('data-lat'));
    const lng = parseFloat(marker.getAttribute('data-lng'));
    const position = [lat, lng];

    // Create icon based on location type
    const locationType = marker.getAttribute('data-type');
    const icon = this.create_icon(locationType);

    // Add marker to map
    const leafletMarker = L.marker(position, { icon }).addTo(map);

    // Add tooltip that shows on hover
    const tooltipContent = marker.querySelector('.tooltip__content')?.innerHTML;
    if (tooltipContent) {
      leafletMarker.bindTooltip(tooltipContent, {
        direction: 'right',
        offset: L.point(20, 0),
        className: 'location-tooltip'
      });

      // Disable tooltip when popup is open
      leafletMarker.on('popupopen', () => {
        leafletMarker.unbindTooltip();
      });

      // Re-enable tooltip when popup is closed
      leafletMarker.on('popupclose', () => {
        leafletMarker.bindTooltip(tooltipContent, {
          direction: 'right',
          offset: L.point(20, 0),
          className: 'location-tooltip'
        });
      });
    }

    // Add popup with marker content
    const markerContent = marker.querySelector('.marker__content')?.innerHTML;
    const popupContent = `
      <div class="map-popup-content">
        ${markerContent || ''}
        <div class="directions-form">
          <input type="text" id="start-address" placeholder="Enter your starting point">
          <div class="button-group">
            <button onclick="window.openStreetMap.getDirectionsFromAddress(${lat}, ${lng})" class="get-directions">Get Directions</button>
            <button onclick="window.openStreetMap.getDirectionsFromCurrentLocation(${lat}, ${lng})" class="use-current-location">Use Current Location</button>
          </div>
        </div>
      </div>
    `;
    leafletMarker.bindPopup(popupContent);

    bounds.push(position);
  }

  create_icon(locationType) {
    return (
      this.markerIcons[locationType] ||
      L.icon({
        iconUrl: `${mythguardData.theme_url}/images/markers/default.png`,
        iconSize: [32, 32],
        iconAnchor: [16, 32],
        popupAnchor: [0, -32],
      })
    );
  }

  getDirectionsFromCurrentLocation(destLat, destLng) {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        position => {
          this.showRoute(
            position.coords.latitude,
            position.coords.longitude,
            destLat,
            destLng
          );
        },
        error => {
          console.error('Error getting location:', error);
          alert(
            'Could not get your location. Please enable location services.'
          );
        }
      );
    } else {
      alert('Geolocation is not supported by your browser.');
    }
  }

  getDirectionsFromAddress(destLat, destLng) {
    const startAddress = document.getElementById('start-address').value;
    if (!startAddress) {
      alert('Please enter a starting address');
      return;
    }

    const geocoder = L.Control.Geocoder.nominatim();
    geocoder.geocode(startAddress, results => {
      if (results && results.length > 0) {
        const { lat, lng } = results[0].center;
        this.showRoute(lat, lng, destLat, destLng);
      } else {
        alert('Could not find the address. Please try a different one.');
      }
    });
  }

  showRoute(startLat, startLng, destLat, destLng) {
    // Close any open popups
    this.currentMap.closePopup();

    // Remove existing route if any
    if (this.routingControl) {
      this.routingControl.remove();
      this.routingControl = null;
    }

    // Create routing control
    this.routingControl = L.Routing.control({
      waypoints: [L.latLng(startLat, startLng), L.latLng(destLat, destLng)],
      router: L.Routing.osrmv1({
        serviceUrl: 'https://router.project-osrm.org/route/v1',
      }),
      routeWhileDragging: true,
      lineOptions: {
        styles: [{ color: '#1B365D', weight: 4 }],
      },
      show: true,
      collapsible: true,
      showAlternatives: false,
      altLineOptions: {
        styles: [{ color: '#6FA1EC', opacity: 0.8, weight: 3 }],
      },
      createMarker: (i, wp) => {
        if (i === 0) {
          const greenIcon = new L.Icon({
            iconUrl:
              'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
            shadowUrl:
              'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41],
          });
          return L.marker(wp.latLng, { icon: greenIcon });
        }
        return null;
      },
      formatter: new L.Routing.Formatter({
        units: 'imperial',
        roundingSensitivity: 30,
      }),
    }).addTo(this.currentMap);

    // Add necessary classes
    const mapContainer = this.currentMap.getContainer();
    mapContainer.closest('.acf-map').classList.add('show-directions');
    // Listen for the collapse event from the Leaflet Routing plugin
    const observer = new MutationObserver(() => {
      const closeBtn = document.querySelector('.leaflet-routing-collapse-btn');
      if (closeBtn) {
        closeBtn.addEventListener('click', () => {
          this.closeDirections();
        });
        observer.disconnect();
      }
    });

    observer.observe(document.body, {
      childList: true,
      subtree: true,
    });

    // Update map size
    setTimeout(() => this.currentMap.invalidateSize(), 100);
  }

  closeDirections() {
    if (this.routingControl) {
      this.routingControl.remove();
      this.routingControl = null;
    }

    // Remove show-directions class
    const mapContainer = this.currentMap.getContainer();
    mapContainer.closest('.acf-map').classList.remove('show-directions');

    // Update map size
    setTimeout(() => this.currentMap.invalidateSize(), 100);

    // Reset map view to show all markers
    const bounds = [];
    document.querySelectorAll('.marker').forEach(marker => {
      const lat = parseFloat(marker.getAttribute('data-lat'));
      const lng = parseFloat(marker.getAttribute('data-lng'));
      if (lat && lng) bounds.push([lat, lng]);
    });
    if (bounds.length) this.currentMap.fitBounds(bounds);
  }
}

export default OpenStreetMap;
