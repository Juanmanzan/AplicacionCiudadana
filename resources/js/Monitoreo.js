// resources/js/Monitoreo.js
import './bootstrap';

let map;
let riobambaBounds;
let emergenciasLayer;

// ✅ Spiderfy instance
let oms = null;

const markersById = {};      // id_emergencia -> marker (real)
const groupMarkers = {};     // "lat,lng" -> groupMarker (visual)
const groupChildren = {};    // "lat,lng" -> [markers...]
const markerToGroupKey = new Map(); // marker -> groupKey

let searchMarker = null;
let searchPopup = null;
let suggestBox = null;

let searchDebounce = null;
let lastQuery = '';

// ✅ vista inicial
let initialView = null;

// =========================
// CONFIG (TIPOS / ESTADOS)
// =========================
const tipoConfig = {
  1: { key: 'medical',  bi: 'bi-heart-pulse',          color: '#ef4444', label: 'Emergencia médica' },
  2: { key: 'fire',     bi: 'bi-fire',                 color: '#f97316', label: 'Incendio' },
  3: { key: 'assault',  bi: 'bi-exclamation-triangle', color: '#eab308', label: 'Asalto' },
  4: { key: 'traffic',  bi: 'bi-car-front',            color: '#3b82f6', label: 'Siniestro de tránsito' },
};

const estadosCierre = new Set(['ATENDIDA', 'FALSA_ALARMA']);
const ACTIVE_ENDPOINT = '/emergencias/activas';

// =========================
// HELPERS UI (ICON / POPUP)
// =========================
function cfgTipo(tipoId) {
  return tipoConfig[tipoId] ?? {
    key: 'default',
    bi: 'bi-geo-alt',
    color: '#64748b',
    label: 'Emergencia',
  };
}

function escapeHtml(str) {
  return String(str ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}

function formatFecha(fechaIso) {
  if (!fechaIso) return '';
  const d = new Date(fechaIso);
  if (Number.isNaN(d.getTime())) return String(fechaIso);
  const yyyy = d.getFullYear();
  const mm = String(d.getMonth() + 1).padStart(2, '0');
  const dd = String(d.getDate()).padStart(2, '0');
  const hh = String(d.getHours()).padStart(2, '0');
  const mi = String(d.getMinutes()).padStart(2, '0');
  return `${yyyy}-${mm}-${dd} ${hh}:${mi}`;
}

function prettyGenero(g) {
  const s = String(g ?? '').trim().toUpperCase();
  if (s === 'M' || s === 'MASCULINO') return 'Masculino';
  if (s === 'F' || s === 'FEMENINO') return 'Femenino';
  return g ?? 'No registrado';
}

// ✅ icono base (el que ya tienes con tu CSS)
function buildIcon(tipoId) {
  const cfg = cfgTipo(tipoId);

  const html = `
    <div class="flat-marker" style="--c:${cfg.color}">
      <i class="bi ${cfg.bi}"></i>
    </div>
  `;

  return L.divIcon({
    className: 'flat-marker-wrap',
    html,
    iconSize: [32, 32],
    iconAnchor: [16, 16],
    popupAnchor: [0, -18],
  });
}

// ✅ icono "agrupado" con numerito
function buildGroupIcon(tipoId, count) {
  const cfg = cfgTipo(tipoId);

  const html = `
    <div class="flat-marker is-group" style="--c:${cfg.color}; position:relative;">
      <i class="bi ${cfg.bi}"></i>
      <span style="
        position:absolute;
        top:-8px;
        right:-8px;
        min-width:18px;
        height:18px;
        padding:0 5px;
        border-radius:999px;
        background:#0f172a;
        color:#fff;
        font-size:11px;
        font-weight:700;
        display:flex;
        align-items:center;
        justify-content:center;
        box-shadow:0 6px 14px rgba(0,0,0,.25);
        border:2px solid #fff;
      ">${count}</span>
    </div>
  `;

  return L.divIcon({
    className: 'flat-marker-wrap',
    html,
    iconSize: [32, 32],
    iconAnchor: [16, 16],
    popupAnchor: [0, -18],
  });
}

/**
 * ✅ Popup tipo tarjeta (como tu 2da imagen)
 * Requiere que el endpoint /emergencias/activas devuelva:
 * nombres, apellidos, genero, edad, cedula_usuario (además de lat/lng/estado/etc)
 */
function popupHtml(e) {
  const tipo = cfgTipo(e.id_tipo_emergencia);
  const estado = (e.estado ?? 'PENDIENTE').toUpperCase();

  const nombres = e.nombres ?? 'Sin nombres';
  const apellidos = e.apellidos ?? '';
  const cedula = e.cedula_usuario ?? e.cedula ?? '---';
  const genero = prettyGenero(e.genero);
  const edad = (e.edad ?? '') !== '' ? `${e.edad} años` : 'No registrada';

  const lat = (e.lat != null) ? Number(e.lat).toFixed(5) : '---';
  const lng = (e.lng != null) ? Number(e.lng).toFixed(5) : '---';

  const fecha = formatFecha(e.fecha_hora);

  // color cabecera según tipo
  const headerColor = tipo.color;

  // ✅ Si quieres, aquí puedes esconder coordenadas y solo mostrarlas en detalle.
  return `
    <div class="emg-popup">
      <div class="emg-head" style="--head:${headerColor}">
        <div class="emg-title">
          <i class="bi ${tipo.bi}"></i>
          <span>${escapeHtml(tipo.label)}</span>
        </div>
        <div class="emg-badges">
          <span class="emg-chip">${escapeHtml(estado)}</span>
          <span class="emg-chip emg-id">#${escapeHtml(e.id_emergencia)}</span>
        </div>
      </div>

      <div class="emg-body">
        <div class="emg-desc">
          ${escapeHtml(e.descripcion ?? 'Sin descripción')}
        </div>

        <div class="emg-row">
          <i class="bi bi-person"></i>
          <div><b>${escapeHtml(nombres)} ${escapeHtml(apellidos)}</b> <span class="emg-muted">(${escapeHtml(cedula)})</span></div>
        </div>

        <div class="emg-row">
          <i class="bi bi-gender-ambiguous"></i>
          <div>${escapeHtml(genero)} <span class="emg-muted">•</span> ${escapeHtml(edad)}</div>
        </div>

        <div class="emg-row">
          <i class="bi bi-geo-alt"></i>
          <div>${lat}, ${lng}</div>
        </div>

        <div class="emg-row">
          <i class="bi bi-calendar-event"></i>
          <div>${escapeHtml(fecha)}</div>
        </div>

        <button class="emg-btn" type="button" data-emg-id="${escapeHtml(e.id_emergencia)}">
          <i class="bi bi-box-arrow-up-right"></i>
          Ver detalle
        </button>
      </div>
    </div>
  `;
}

// =========================
// SIDEBAR
// =========================
function safeSetText(id, text) {
  const el = document.getElementById(id);
  if (el) el.textContent = String(text);
}

function updateSidebarCounts(items) {
  const counts = { medical: 0, fire: 0, assault: 0, traffic: 0 };

  for (const e of items) {
    const cfg = cfgTipo(e.id_tipo_emergencia);
    if (counts[cfg.key] !== undefined) counts[cfg.key] += 1;
  }

  const total = counts.medical + counts.fire + counts.assault + counts.traffic;

  safeSetText('count-medical', counts.medical);
  safeSetText('count-fire', counts.fire);
  safeSetText('count-assault', counts.assault);
  safeSetText('count-traffic', counts.traffic);

  safeSetText('totalActivos', total);
  safeSetText('eventsCount', `${total} eventos activos`);
}

// =========================
// AGRUPACIÓN POR COORDENADA
// =========================
function roundCoord(n, decimals = 6) {
  const p = Math.pow(10, decimals);
  return Math.round(Number(n) * p) / p;
}

function getGroupKey(lat, lng) {
  const la = roundCoord(lat, 6);
  const lo = roundCoord(lng, 6);
  return `${la},${lo}`;
}

function getDominantTipoId(markers) {
  const priority = [3, 2, 4, 1]; // assault > fire > traffic > medical
  for (const t of priority) {
    if (markers.some(m => m.__tipoId === t)) return t;
  }
  return markers[0]?.__tipoId ?? 1;
}

function rebuildGroups() {
  // 1) limpiar groupMarkers viejos del mapa
  Object.keys(groupMarkers).forEach((k) => {
    emergenciasLayer?.removeLayer(groupMarkers[k]);
    delete groupMarkers[k];
  });
  Object.keys(groupChildren).forEach((k) => delete groupChildren[k]);
  markerToGroupKey.clear();

  // 2) armar children por coordenada
  for (const idStr of Object.keys(markersById)) {
    const m = markersById[idStr];
    const ll = m.getLatLng();
    const key = getGroupKey(ll.lat, ll.lng);

    if (!groupChildren[key]) groupChildren[key] = [];
    groupChildren[key].push(m);
    markerToGroupKey.set(m, key);
  }

  // 3) crear groupMarker si hay >1 en el mismo punto
  for (const key of Object.keys(groupChildren)) {
    const arr = groupChildren[key];
    if (arr.length <= 1) continue;

    // ocultar markers reales
    arr.forEach(m => {
      if (emergenciasLayer.hasLayer(m)) emergenciasLayer.removeLayer(m);
    });

    const [latStr, lngStr] = key.split(',');
    const lat = Number(latStr);
    const lng = Number(lngStr);

    const tipoId = getDominantTipoId(arr);

    const gm = L.marker([lat, lng], { icon: buildGroupIcon(tipoId, arr.length) });
    gm.__groupKey = key;

    gm.on('click', () => {
      const children = groupChildren[key] || [];
      if (children.length < 2) return;

      // 1) mostrar markers reales
      children.forEach(m => {
        if (!emergenciasLayer.hasLayer(m)) emergenciasLayer.addLayer(m);
      });

      // 2) quitar el marker agrupado
      if (emergenciasLayer.hasLayer(gm)) emergenciasLayer.removeLayer(gm);

      // 3) disparar spiderfy (OMS se activa con click del marker)
      requestAnimationFrame(() => {
        children[0].fire('click');
      });
    });

    gm.addTo(emergenciasLayer);
    groupMarkers[key] = gm;
  }
}

// =========================
// MARKERS (UPSERT / REMOVE)
// =========================
function removeMarker(id) {
  const m = markersById[id];
  if (!m) return;

  // ✅ quitar de OMS
  if (oms) oms.removeMarker(m);

  // ✅ quitar del layer (si está)
  emergenciasLayer?.removeLayer(m);

  delete markersById[id];
}

/**
 * ✅ NUEVO: bind para botón "Ver detalle" dentro del popup
 */
function bindPopupButton(marker) {
  marker.on('popupopen', (ev) => {
    const root = ev.popup.getElement();
    if (!root) return;

    const btn = root.querySelector('.emg-btn');
    if (!btn) return;

    // Para evitar duplicados si abres/cierra varias veces
    if (btn.__bound) return;
    btn.__bound = true;

    btn.addEventListener('click', () => {
      const id = btn.getAttribute('data-emg-id');
      window.location.href = `/historial?emergencia=${encodeURIComponent(id)}`;
    });
  });
}

function upsertMarker(e) {
  const id = e?.id_emergencia;
  if (!id) return;

  const estado = (e.estado ?? '').toUpperCase();

  // si ya cerró/invalidó: eliminar del mapa
  if (estadosCierre.has(estado)) {
    removeMarker(id);
    return;
  }

  if (e.lat == null || e.lng == null) return;

  // crear
  if (!markersById[id]) {
    const marker = L.marker([e.lat, e.lng], { icon: buildIcon(e.id_tipo_emergencia) })
      .bindPopup(popupHtml(e), {
        closeButton: true,
        autoPan: true,
        maxWidth: 340,
      });

    // guardamos tipo para agrupación
    marker.__tipoId = Number(e.id_tipo_emergencia);

    // ✅ bind del botón dentro del popup
    bindPopupButton(marker);

    marker.addTo(emergenciasLayer);
    markersById[id] = marker;

    if (oms) oms.addMarker(marker);
    return;
  }

  // actualizar
  const m = markersById[id];
  m.setLatLng([e.lat, e.lng]);
  m.setIcon(buildIcon(e.id_tipo_emergencia));
  m.setPopupContent(popupHtml(e));
  m.__tipoId = Number(e.id_tipo_emergencia);
}

// =========================
// REHIDRATACIÓN
// =========================
async function loadEmergenciasActivas() {
  try {
    const res = await fetch(ACTIVE_ENDPOINT, {
      headers: { Accept: 'application/json' },
      cache: 'no-store',
      credentials: 'same-origin',
    });

    if (!res.ok) {
      console.warn('No se pudo cargar emergencias activas', res.status);
      return;
    }

    const items = await res.json();

    // sync: eliminar los que ya no existen
    const alive = new Set(items.map(x => Number(x.id_emergencia)));
    Object.keys(markersById).forEach((idStr) => {
      if (!alive.has(Number(idStr))) removeMarker(idStr);
    });

    // upsert activos
    items.forEach(upsertMarker);

    // sidebar
    updateSidebarCounts(items);

    // ✅ reconstruir agrupación (badge)
    rebuildGroups();

    console.log('✅ Emergencias activas sincronizadas:', items.length);
  } catch (err) {
    console.error('Error cargando emergencias activas:', err);
  }
}

// =========================
// BUSCADOR OSM
// =========================
async function searchInRiobamba(query) {
  const q = (query ?? '').trim();
  if (!q || !riobambaBounds) return [];

  const b = riobambaBounds;
  const sw = b.getSouthWest();
  const ne = b.getNorthEast();

  const url = new URL('https://nominatim.openstreetmap.org/search');
  url.searchParams.set('q', q);
  url.searchParams.set('format', 'json');
  url.searchParams.set('addressdetails', '1');
  url.searchParams.set('limit', '6');
  url.searchParams.set('bounded', '1');
  url.searchParams.set('viewbox', `${sw.lng},${ne.lat},${ne.lng},${sw.lat}`);
  url.searchParams.set('countrycodes', 'ec');

  const res = await fetch(url.toString(), {
    headers: {
      Accept: 'application/json',
      'User-Agent': 'AplicacionCiudadana/1.0 (Monitoreo)',
    },
  });

  if (!res.ok) return [];
  return await res.json();
}

function goToInitialView() {
  if (!map) return;

  if (initialView?.bounds) {
    map.fitBounds(initialView.bounds, { padding: initialView.padding ?? [12, 12] });
    return;
  }

  if (riobambaBounds) map.fitBounds(riobambaBounds, { padding: [12, 12] });
}

function resetSearch(alsoRecenter = true) {
  if (searchMarker) {
    map.removeLayer(searchMarker);
    searchMarker = null;
  }
  if (searchPopup) {
    map.closePopup(searchPopup);
    searchPopup = null;
  }
  renderSuggestions([]);
  if (alsoRecenter) goToInitialView();
}

function showNoResults(msg) {
  renderSuggestions([], msg || 'Sin resultados dentro de Riobamba.');
}

function showSearchResult(r) {
  if (!r) return;

  const lat = parseFloat(r.lat);
  const lng = parseFloat(r.lon);
  if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;

  if (riobambaBounds && !riobambaBounds.contains([lat, lng])) {
    showNoResults('Búsqueda fuera de rango (Riobamba).');
    return;
  }

  if (!searchMarker) {
    searchMarker = L.marker([lat, lng]).addTo(map);
  } else {
    searchMarker.setLatLng([lat, lng]);
  }

  map.setView([lat, lng], Math.max(map.getZoom(), 16), { animate: true });

  searchPopup = L.popup({ closeButton: true })
    .setLatLng([lat, lng])
    .setContent(r.display_name)
    .openOn(map);
}

// =========================
// SUGGEST BOX
// =========================
function ensureSuggestBox(inputEl) {
  if (suggestBox) return suggestBox;

  const parent = inputEl.closest('.search-field') || inputEl.parentElement;
  suggestBox = document.createElement('div');
  suggestBox.id = 'searchSuggestions';
  suggestBox.style.position = 'absolute';
  suggestBox.style.left = '0';
  suggestBox.style.right = '0';
  suggestBox.style.top = 'calc(100% + 6px)';
  suggestBox.style.background = '#fff';
  suggestBox.style.border = '1px solid rgba(0,0,0,.08)';
  suggestBox.style.borderRadius = '12px';
  suggestBox.style.boxShadow = '0 12px 30px rgba(0,0,0,.12)';
  suggestBox.style.overflow = 'hidden';
  suggestBox.style.zIndex = '9999';
  suggestBox.style.display = 'none';

  if (parent && getComputedStyle(parent).position === 'static') {
    parent.style.position = 'relative';
  }

  parent.appendChild(suggestBox);

  document.addEventListener('click', (ev) => {
    if (!suggestBox) return;
    if (ev.target === inputEl || suggestBox.contains(ev.target)) return;
    suggestBox.style.display = 'none';
  });

  return suggestBox;
}

function shortName(displayName) {
  if (!displayName) return 'Resultado';
  const p = String(displayName).split(',')[0]?.trim();
  return p || 'Resultado';
}

function renderSuggestions(results, emptyMessage = '') {
  if (!suggestBox) return;

  // ✅ si no hay resultados
  if (!results || results.length === 0) {
    if (!emptyMessage) {
      suggestBox.style.display = 'none';
      suggestBox.innerHTML = '';
      return;
    }

    suggestBox.style.display = 'block';
    suggestBox.innerHTML = `
      <div style="padding:12px 14px; font-size:13px; color:#64748b;">
        ${escapeHtml(emptyMessage)}
      </div>
    `;
    return;
  }

  suggestBox.style.display = 'block';
  suggestBox.innerHTML = results.map((r, idx) => `
    <div data-idx="${idx}" style="
      padding:12px 14px;
      cursor:pointer;
      font-size:13px;
      border-top:${idx === 0 ? 'none' : '1px solid rgba(0,0,0,.06)'};
      display:flex;
      gap:10px;
      align-items:flex-start;
    ">
      <i class="bi bi-geo-alt" style="margin-top:2px;color:#0ea5e9;"></i>
      <div style="line-height:1.2;">
        <div style="font-weight:600; color:#0f172a;">${escapeHtml(shortName(r.display_name))}</div>
        <div style="color:#64748b; margin-top:2px;">${escapeHtml(r.display_name)}</div>
      </div>
    </div>
  `).join('');

  suggestBox.querySelectorAll('[data-idx]').forEach(el => {
    el.addEventListener('click', () => {
      const idx = Number(el.getAttribute('data-idx'));
      const r = results[idx];
      showSearchResult(r);
      suggestBox.style.display = 'none';
    });
  });
}

function bindSearchBox() {
  const input = document.querySelector('.search-input');
  if (!input) return;

  ensureSuggestBox(input);

  input.addEventListener('keydown', async (ev) => {
    if (ev.key === 'Escape') {
      input.value = '';
      resetSearch(true);
      return;
    }

    if (ev.key !== 'Enter') return;
    ev.preventDefault();

    const q = (input.value ?? '').trim();
    if (!q) {
      resetSearch(true);
      return;
    }

    const results = await searchInRiobamba(q);
    if (!results.length) {
      showNoResults('Búsqueda fuera de rango / sin resultados.');
      return;
    }

    renderSuggestions(results);
    showSearchResult(results[0]);
  });

  input.addEventListener('input', () => {
    clearTimeout(searchDebounce);

    const q = (input.value ?? '').trim();

    if (!q) {
      lastQuery = '';
      resetSearch(true);
      return;
    }

    if (q.length < 3) {
      renderSuggestions([], 'Escribe al menos 3 letras...');
      return;
    }

    searchDebounce = setTimeout(async () => {
      if (q === lastQuery) return;
      lastQuery = q;

      const results = await searchInRiobamba(q);

      if (!results.length) {
        showNoResults('Búsqueda fuera de rango / sin resultados.');
        return;
      }

      renderSuggestions(results);
    }, 450);
  });
}

// =========================
// MAP HELPERS (MASK HOLES)
// =========================
function geojsonToLeafletHoles(featureCollection) {
  const holes = [];
  const features = featureCollection?.features || [];

  for (const f of features) {
    const g = f?.geometry;
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

// =========================
// MAP INIT
// =========================
async function initializeMapLeaflet() {
  const mapEl = document.getElementById('map');
  if (!mapEl) return;

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
    zoomDelta: 1,
  });

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors',
    maxZoom: 19,
  }).addTo(map);

  const res = await fetch('/geo/riobamba_limite.geojson', { cache: 'no-store' });
  if (!res.ok) throw new Error('No se pudo cargar el GeoJSON');

  const geo = await res.json();

  const limiteLayer = L.geoJSON(geo, {
    style: { color: '#2563eb', weight: 2, fillOpacity: 0.06 },
    interactive: false,
  }).addTo(map);

  riobambaBounds = limiteLayer.getBounds();

  map.fitBounds(riobambaBounds, { padding: [12, 12] });
  initialView = { bounds: riobambaBounds, padding: [12, 12] };

  map.setMaxBounds(riobambaBounds.pad(0.15));
  map.options.maxBoundsViscosity = 1.0;

  const minZ = map.getBoundsZoom(riobambaBounds, true) - 2;
  map.setMinZoom(Math.max(9, minZ));
  map.setMaxZoom(19);

  map.on('drag', () => map.panInsideBounds(riobambaBounds, { animate: false }));

  map.createPane('maskPane');
  map.getPane('maskPane').style.zIndex = 200;

  const world = [[-90, -180], [-90, 180], [90, 180], [90, -180]];
  const holes = geojsonToLeafletHoles(geo);

  const mask = L.polygon([world, ...holes], {
    pane: 'maskPane',
    stroke: false,
    fillColor: '#1f7a3b',
    fillOpacity: 0.22,
    interactive: false,
    bubblingMouseEvents: false,
  }).addTo(map);

  mask.bringToBack();
  limiteLayer.bringToFront();

  // ✅ Spiderfy init
  if (window.OverlappingMarkerSpiderfier) {
    oms = new window.OverlappingMarkerSpiderfier(map, {
      keepSpiderfied: true,
      nearbyDistance: 50,
      legWeight: 2,
    });

    oms.addListener('click', (marker) => {
      marker.openPopup();
    });

    // cuando se "unspiderfy" volvemos a reconstruir grupos (para que regrese el badge)
    oms.addListener('unspiderfy', () => {
      rebuildGroups();
    });
  } else {
    console.warn('⚠️ OMS no está cargado. Revisa el <script src=".../oms.js">');
  }

  // ✅ BOTÓN CENTRAR
  document.getElementById('btnCenter')?.addEventListener('click', () => {
    const input = document.querySelector('.search-input');
    if (input) input.value = '';
    resetSearch(true);
  });

  const zoomLabel = document.getElementById('zoomLabel');
  const updateZoomLabel = () => {
    if (zoomLabel) zoomLabel.textContent = `Zoom ${map.getZoom()}`;
  };
  map.on('zoomend', updateZoomLabel);
  updateZoomLabel();

  document.getElementById('zoomIn')?.addEventListener('click', () => map.zoomIn());
  document.getElementById('zoomOut')?.addEventListener('click', () => map.zoomOut());

  setTimeout(() => {
    map.invalidateSize();
    goToInitialView();
  }, 150);
}

// =========================
// WS LISTENERS
// =========================
let refreshTimer = null;

function scheduleRefresh() {
  clearTimeout(refreshTimer);
  refreshTimer = setTimeout(() => loadEmergenciasActivas(), 250);
}

function bindWsDebug() {
  try {
    const conn = window.Echo?.connector?.pusher?.connection;
    if (!conn) return;
    conn.bind('connected', () => console.log('✅ WS conectado'));
    conn.bind('error', (err) => console.log('❌ WS error', err));
  } catch (_) {}
}

function listenEmergenciasAdmin() {
  if (!window.Echo) {
    console.error('Echo no está disponible.');
    return;
  }

  window.Echo.private('emergencias.admin')
    .subscribed(() => console.log('✅ Suscrito a emergencias.admin'))
    .error((err) => console.error('❌ Error suscripción canal', err))
    .listen('.emergencia.creada', () => scheduleRefresh())
    .listen('.emergencia.actualizada', () => scheduleRefresh());
}

// =========================
// BOOT
// =========================
document.addEventListener('DOMContentLoaded', async () => {
  bindDetalleDelegation();
  if (typeof L === 'undefined') {
    console.error('Leaflet no está cargado. Revisa el orden de scripts.');
    return;
  }

  try {
    await initializeMapLeaflet();
    emergenciasLayer = L.layerGroup().addTo(map);

    bindSearchBox();
    await loadEmergenciasActivas();

    bindWsDebug();
    listenEmergenciasAdmin();
  } catch (e) {
    console.error('Error inicializando mapa:', e);
  }
});

function bindDetalleDelegation() {
  // evita registrar dos veces
  if (window.__bindDetalleDelegationDone) return;
  window.__bindDetalleDelegationDone = true;

  document.addEventListener('click', (ev) => {
    const btn = ev.target.closest('.emg-btn');
    if (!btn) return;

    ev.preventDefault();
    ev.stopPropagation();

    const id = btn.getAttribute('data-emg-id');
    if (!id) return;

    // ✅ navegar a historial filtrado
    window.location.assign(`/historial?emergencia=${encodeURIComponent(id)}`);
  }, true); // <- capture true ayuda bastante con Leaflet
}

