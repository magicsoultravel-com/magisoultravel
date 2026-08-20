/* magic soul travel — GPX track rendering on Leaflet.js (OpenStreetMap + satellite) */
/* Uses Leaflet.js with OpenStreetMap and Esri WorldImagery tiles. No API key needed. */

const MST_MAPS = {
  mapObjects: {},
  pendingTracks: [],
  leafletPromise: null,

  loadLeaflet() {
    return new Promise((resolve) => {
      if (window.L) {
        resolve();
        return;
      }

      if (this.leafletPromise) {
        this.leafletPromise.then(resolve);
        return;
      }

      this.leafletPromise = new Promise((res) => {
        // Load Leaflet CSS
        if (!document.querySelector('link[href*="leaflet"]')) {
          const link = document.createElement('link');
          link.rel = 'stylesheet';
          link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
          document.head.appendChild(link);
        }

        // Load Leaflet JS
        const script = document.createElement('script');
        script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
        script.onload = () => res();
        script.onerror = () => {
          document.querySelectorAll('.gpx-map').forEach(el => {
            el.innerHTML = '<p style="color:#e74c3c; padding:20px;">Failed to load maps. Check your connection.</p>';
          });
          res();
        };
        document.head.appendChild(script);
      });

      this.leafletPromise.then(resolve);
    });
  },

  async init() {
    await this.loadLeaflet();
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

    // If Leaflet not loaded yet, queue it
    if (!window.L) {
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
      const bounds = L.latLngBounds(
        [...trackPoints, ...waypoints].map(p => [p.lat, p.lng])
      );

      // Create map
      const map = L.map(mapDiv, {
        zoomControl: true,
        attributionControl: true
      });

      // Tile layers — map view (OpenStreetMap) and satellite view (Esri WorldImagery)
      const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
      });

      const satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.esri.com/">Esri</a> &mdash; Source: Esri, Earthstar Geographics'
      });

      // Add base layers and layer control (toggle between map and satellite)
      const baseLayers = {
        'Map': osmLayer,
        'Satellite': satelliteLayer
      };

      L.control.layers(baseLayers, {}, { collapsed: false, position: 'topright' }).addTo(map);

      // Default to map view
      osmLayer.addTo(map);

      // Fit bounds to show the full track
      map.fitBounds(bounds, { padding: [40, 40] });

      this.mapObjects[mapId] = map;

      // Add track polyline
      if (trackPoints.length > 0) {
        L.polyline(trackPoints.map(p => [p.lat, p.lng]), {
          color: '#FF0000',
          weight: 4,
          opacity: 1.0,
          smoothFactor: 1
        }).addTo(map);
      }

      // Add waypoint markers
      waypoints.forEach(wp => {
        L.marker([wp.lat, wp.lng])
          .bindPopup(wp.name)
          .addTo(map);
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
