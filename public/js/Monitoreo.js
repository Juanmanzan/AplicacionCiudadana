let map;
let riobambaBounds;

async function initializeMapLeaflet() {
  const mapEl = document.getElementById('map');
  if (!mapEl) return;

  // Si ya existe, destrúyelo (evita duplicación / bugs)
  if (map) {
    map.remove();
    map = null;
  }

  map = L.map('map', {
    zoomControl: false,
    preferCanvas: true,
    inertia: false,
    worldCopyJump: true,
    zoomSnap: 1,
    zoomDelta: 1
  });

  // ✅ fuerza cálculo de tamaño
  setTimeout(() => map.invalidateSize(), 0);

  // Tiles OSM
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors',
    maxZoom: 19
  }).addTo(map);

  // Cargar GeoJSON
  const res = await fetch('/geo/riobamba_limite.geojson', { cache: 'no-store' });
  if (!res.ok) {
    console.error('No se pudo cargar el GeoJSON');
    return;
  }
  const geo = await res.json();

  const limiteLayer = L.geoJSON(geo, {
    style: { color: '#2563eb', weight: 2, fillOpacity: 0.06 },
    interactive: false
  }).addTo(map);

  riobambaBounds = limiteLayer.getBounds();
  map.fitBounds(riobambaBounds, { padding: [12, 12] });

  // ✅ limita navegación
  map.setMaxBounds(riobambaBounds.pad(0.15));
  map.options.maxBoundsViscosity = 1.0;

  // ✅ limita zoom hacia atrás (alejar)
  const minZ = map.getBoundsZoom(riobambaBounds, true) - 2;
  map.setMinZoom(Math.max(9, minZ));
  map.setMaxZoom(19);

  // ✅ mantener dentro en drag
  map.on('drag', () => {
    map.panInsideBounds(riobambaBounds, { animate: false });
  });

  // Pane para máscara
  map.createPane('maskPane');
  map.getPane('maskPane').style.zIndex = 200;

  const world = [
    [-90, -180],
    [-90,  180],
    [ 90,  180],
    [ 90, -180]
  ];

  const holes = geojsonToLeafletHoles(geo);

  const mask = L.polygon([world, ...holes], {
    pane: 'maskPane',
    stroke: false,
    fillColor: '#1f7a3b',
    fillOpacity: 0.22,
    interactive: false,
    bubblingMouseEvents: false
  }).addTo(map);

  mask.bringToBack();
  limiteLayer.bringToFront();

  // ✅ doble invalidate para evitar “mapa gris”
  setTimeout(() => {
    map.invalidateSize();
    map.fitBounds(riobambaBounds, { padding: [12, 12] });
  }, 200);

  // Botón centrar
  document.getElementById('btnCenter')?.addEventListener('click', () => {
    map.fitBounds(riobambaBounds, { padding: [12, 12] });
  });

  // Zoom controls
  document.getElementById('zoomIn')?.addEventListener('click', () => map.zoomIn());
  document.getElementById('zoomOut')?.addEventListener('click', () => map.zoomOut());

  const zoomLabel = document.getElementById('zoomLabel');
  const updateZoomLabel = () => {
    if (zoomLabel) zoomLabel.textContent = `Zoom ${map.getZoom()}`;
  };
  map.on('zoomend', updateZoomLabel);
  updateZoomLabel();
}

function geojsonToLeafletHoles(featureCollection) {
  const holes = [];
  const features = featureCollection.features || [];

  for (const f of features) {
    const g = f.geometry;
    if (!g) continue;

    if (g.type === 'Polygon') {
      const outerRing = g.coordinates[0];
      holes.push(outerRing.map(([lng, lat]) => [lat, lng]));
    } else if (g.type === 'MultiPolygon') {
      for (const poly of g.coordinates) {
        const outerRing = poly[0];
        holes.push(outerRing.map(([lng, lat]) => [lat, lng]));
      }
    }
  }
  return holes;
}

// ✅ ARRANQUE
document.addEventListener('DOMContentLoaded', async () => {
  if (typeof L === 'undefined') {
    console.error('Leaflet no está cargado. Revisa el orden de scripts.');
    return;
  }

  try {
    await initializeMapLeaflet();
  } catch (e) {
    console.error('Error inicializando mapa:', e);
  }
});
