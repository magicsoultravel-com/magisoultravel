/* magic soul travel — GPX track rendering on Google Maps */
/* Requires Google Maps JS API. Works standalone (no API key needed for static file fetching). */

const MST_MAPS = {
  apiKey: 'AIzaSyAibw0NuRheQo4Qv1mYcm5gN4LROaeWuCE',
  mapObjects: {},
  pendingTracks: [],

  loadAPI() {
    return new Promise((resolve) => {
      if (window.google && window.google.maps) {
        resolve();
        return;
      }

      // Script already loading?
      if (this.apiPromise) {
        this.apiPromise.then(resolve);
        return;
      }

      this.apiPromise = new Promise((res) => {
        window.initGPXMapsCallback = () => res();

        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=${this.apiKey}&callback=initGPXMapsCallback`;
        script.async = true;
        script.defer = true;
        script.onerror = () => {
          document.querySelectorAll('.gpx-map').forEach(el => {
            el.innerHTML = '<p style="color:#e74c3c; padding:20px;">Failed to load Google Maps. Check your connection.</p>';
          });
          res();
        };
        document.head.appendChild(script);
      });

      this.apiPromise.then(resolve);
    });
  },

  async init() {
    await this.loadAPI();
    // Render any queued tracks
    this.pendingTracks.forEach(track => this.renderTrack(track.mapId, track.gpxPath));
    this.pendingTracks = [];
  },

  parseGPX(xmlText) {
    const parser = new DOMParser();
    const xml = parser.parseFromString(xmlText, 'text/xml');

    const trackPoints = [];
    const segments = xml.getElementsByTagName('trkseg');
    for (const seg of segments) {
      const pts = seg.getElementsByTagName('trkpt');
      for (const pt of pts) {
        const lat = parseFloat(pt.getAttribute('lat'));
        const lon = parseFloat(pt.getAttribute('lon'));
        if (!isNaN(lat) && !isNaN(lon)) {
          trackPoints.push({ lat, lng: lon });
        }
      }
    }

    const waypoints = [];
    const wpts = xml.getElementsByTagName('wpt');
    for (const wpt of wpts) {
      const lat = parseFloat(wpt.getAttribute('lat'));
      const lon = parseFloat(wpt.getAttribute('lon'));
      const nameEl = wpt.getElementsByTagName('name')[0];
      const name = nameEl ? nameEl.textContent : 'Waypoint';
      if (!isNaN(lat) && !isNaN(lon)) {
        waypoints.push({ lat, lng: lon, name });
      }
    }

    return { trackPoints, waypoints };
  },

  async renderTrack(mapId, gpxPath) {
    const mapDiv = document.getElementById(mapId);
    if (!mapDiv) return;

    // If API not loaded yet, queue it
    if (!window.google || !window.google.maps) {
      this.pendingTracks.push({ mapId, gpxPath });
      return;
    }

    try {
      const response = await fetch(gpxPath);
      if (!response.ok) throw new Error(`HTTP ${response.status}`);
      const xmlText = await response.text();
      const { trackPoints, waypoints } = this.parseGPX(xmlText);

      if (trackPoints.length === 0 && waypoints.length === 0) {
        mapDiv.innerHTML = '<p style="color:#9da7b3; padding:20px;">No track data found.</p>';
        return;
      }

      // Determine bounds
      const bounds = new google.maps.LatLngBounds();
      [...trackPoints, ...waypoints].forEach(p => bounds.extend(p));

      const map = new google.maps.Map(mapDiv, {
        mapTypeId: google.maps.MapTypeId.HYBRID,
        disableDefaultUI: true,
        zoomControl: true
      });
      map.fitBounds(bounds, 40);

      this.mapObjects[mapId] = map;

      if (trackPoints.length > 0) {
        new google.maps.Polyline({
          path: trackPoints,
          geodesic: true,
          strokeColor: '#FF0000',
          strokeOpacity: 1.0,
          strokeWeight: 4,
          map
        });
      }

      waypoints.forEach(wp => {
        new google.maps.Marker({
          position: { lat: wp.lat, lng: wp.lng },
          map,
          title: wp.name
        });
      });
    } catch (err) {
      console.error(`Error rendering map ${mapId}:`, err);
      mapDiv.innerHTML = `<p style="color:#e74c3c; padding:20px;">Error loading track.</p>`;
    }
  }
};

// Auto-init on load
document.addEventListener('DOMContentLoaded', () => {
  MST_MAPS.init();
});

window.MST_MAPS = MST_MAPS;