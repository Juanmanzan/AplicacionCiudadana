// public/js/historial.js
document.addEventListener('DOMContentLoaded', () => {
  const modalEl = document.getElementById('detalleModal');
  if (!modalEl) return;

  // Leaflet refs
  let leafletMap = null;
  let leafletMarker = null;

  // =========================
  // Helpers UI (Badges/Iconos)
  // =========================
  function setEstadoBadge(el, estadoLabel, estadoRaw) {
    if (!el) return;
    el.classList.remove('text-bg-primary', 'text-bg-success', 'text-bg-danger', 'text-bg-secondary');

    let cls = 'text-bg-secondary';
    if (estadoRaw === 'CONFIRMADA') cls = 'text-bg-primary';
    if (estadoRaw === 'ATENDIDA') cls = 'text-bg-success';
    if (estadoRaw === 'FALSA_ALARMA') cls = 'text-bg-danger';

    el.classList.add(cls);
    el.textContent = estadoLabel || '-';
  }

  function setTipoBadge(el, tipoText) {
    if (!el) return;
    el.classList.remove('text-bg-danger', 'text-bg-warning', 'text-bg-info', 'text-bg-primary', 'text-bg-secondary');
    // Por ahora neutro (si quieres, lo mapeamos por nombre luego)
    el.classList.add('text-bg-secondary');
    el.textContent = tipoText || '-';
  }

  // ✅ Icono en header según tipo (Bootstrap Icons)
  function setHeaderIconByTipo(tipoText) {
    const iconWrap = document.getElementById('m_iconWrap');
    const icon = document.getElementById('m_icon');
    if (!iconWrap || !icon) return;

    const t = (tipoText || '').toLowerCase();

    // reset clases
    iconWrap.classList.remove('icon-medical', 'icon-fire', 'icon-assault', 'icon-traffic');
    icon.className = 'bi bi-question-circle';
    icon.style.fontSize = '20px';

    // medico
    if (t.includes('méd') || t.includes('medic') || t.includes('medico')) {
        iconWrap.classList.add('icon-medical');
        icon.className = 'bi bi-heart-pulse';
        return;
    }

    // incendio
    if (t.includes('incend') || t.includes('fire')) {
        iconWrap.classList.add('icon-fire');
        icon.className = 'bi bi-fire';
        return;
    }

    // asalto
    if (t.includes('asal') || t.includes('robo') || t.includes('assault')) {
        iconWrap.classList.add('icon-assault');
        icon.className = 'bi bi-exclamation-triangle';
        return;
    }

    // transito
    if (t.includes('trán') || t.includes('transit') || t.includes('siniestro') || t.includes('accident')) {
        iconWrap.classList.add('icon-traffic');
        icon.className = 'bi bi-car-front';
        return;
    }
    }

  // =========================
  // Leaflet Map
  // =========================
  function ensureLeafletMap() {
    const mapDiv = document.getElementById('m_map');
    if (!mapDiv) return;

    if (!leafletMap) {
      leafletMap = L.map(mapDiv, {
        zoomControl: true,
        attributionControl: true
      });

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
      }).addTo(leafletMap);
    }
  }

  function renderMap(lat, lng) {
    ensureLeafletMap();
    if (!leafletMap) return;

    const zoom = 17; // ✅ zoom cercano para ver bien el punto
    leafletMap.setView([lat, lng], zoom);

    if (leafletMarker) {
      leafletMarker.setLatLng([lat, lng]);
    } else {
      leafletMarker = L.marker([lat, lng]).addTo(leafletMap);
    }

    // ✅ Leaflet en modal requiere invalidateSize
    setTimeout(() => {
      leafletMap.invalidateSize();
      leafletMap.setView([lat, lng], zoom);
    }, 200);
  }

  // =========================
  // Modal: llenar datos
  // =========================
  modalEl.addEventListener('show.bs.modal', (event) => {
    const btn = event.relatedTarget;
    if (!btn) return;

    const id = btn.dataset.id;
    const codigo = btn.dataset.codigo;
    const tipo = btn.dataset.tipo;
    const fecha = btn.dataset.fecha;
    const hora = btn.dataset.hora;
    const usuario = btn.dataset.usuario;
    const genero = btn.dataset.genero;
    const descripcion = btn.dataset.descripcion;
    const ubicacion = btn.dataset.ubicacion;
    const estadoRaw = btn.dataset.estado;
    const estadoLabel = btn.dataset.estadoLabel;

    const latStr = btn.dataset.lat;
    const lngStr = btn.dataset.lng;
    const lat = latStr ? parseFloat(latStr) : null;
    const lng = lngStr ? parseFloat(lngStr) : null;

    // Pintar campos
    const elCodigo = document.getElementById('m_codigo');
    const elFecha = document.getElementById('m_fecha');
    const elHora = document.getElementById('m_hora');
    const elUsuario = document.getElementById('m_usuario');
    const elGenero = document.getElementById('m_genero');
    const elDesc = document.getElementById('m_descripcion');
    const elUbic = document.getElementById('m_ubicacion');

    if (elCodigo) elCodigo.textContent = '#' + (codigo || '0000');
    if (elFecha) elFecha.textContent = fecha || '-';
    if (elHora) elHora.textContent = hora || '-';
    if (elUsuario) elUsuario.textContent = usuario || '-';
    if (elGenero) elGenero.textContent = genero || '-';
    if (elDesc) elDesc.textContent = descripcion || '-';
    if (elUbic) elUbic.textContent = ubicacion || '-';

    setTipoBadge(document.getElementById('m_tipoBadge'), tipo);
    setEstadoBadge(document.getElementById('m_estadoBadge'), estadoLabel, estadoRaw);

    // ✅ Icono según tipo
    setHeaderIconByTipo(tipo);

    // Botón atender (solo CONFIRMADA)
    const formAtender = document.getElementById('m_formAtender');
    const btnAtender = document.getElementById('m_btnAtender');

    if (estadoRaw === 'CONFIRMADA') {
      if (formAtender) formAtender.style.display = 'block';
      if (btnAtender) btnAtender.style.display = 'inline-block';
      if (formAtender) formAtender.action = `/historial/${id}/atender`;
    } else {
      if (formAtender) formAtender.style.display = 'none';
      if (btnAtender) btnAtender.style.display = 'none';
      if (formAtender) formAtender.action = '#';
    }

    // Mapa
    if (lat !== null && lng !== null && !Number.isNaN(lat) && !Number.isNaN(lng)) {
      renderMap(lat, lng);
    } else {
      ensureLeafletMap();
      if (leafletMap) setTimeout(() => leafletMap.invalidateSize(), 200);
    }
  });

  // Extra: al estar visible el modal, recalcular tamaño del mapa
  modalEl.addEventListener('shown.bs.modal', () => {
    if (leafletMap) {
      setTimeout(() => leafletMap.invalidateSize(), 50);
    }
  });
  
});



