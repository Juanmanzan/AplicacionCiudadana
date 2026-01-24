@extends('layouts.Layout_Admin')

@section('fullWidth', 'true')
@section('title', 'Monitoreo')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/Monitoreo.css') }}">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
      crossorigin="">
@endpush

@section('content')
<div class="container-fluid p-0">
  <div class="monitoring-page">

    <!-- ✅ MAPA (IZQUIERDA) -->
    <section class="map-card">
      <div class="map-toolbar">
        <div class="search-field">
          <i class="bi bi-search"></i>
          <input type="text" class="search-input" placeholder="Buscar dirección o sector..." />
        </div>

        <button class="btn-center" type="button" id="btnCenter">
          <i class="bi bi-crosshair"></i>
          Centrar en Riobamba
        </button>
      </div>

      <div class="map-stage">
        <div id="map" class="leaflet-map"></div>

        <div class="map-controls">
          <button class="ctrl-btn" type="button" id="zoomIn"><i class="bi bi-plus"></i></button>
          <div class="ctrl-label" id="zoomLabel">Zoom</div>
          <button class="ctrl-btn" type="button" id="zoomOut"><i class="bi bi-dash"></i></button>
        </div>
      </div>
    </section>

    <!-- ✅ PANEL DERECHO (TIPOS) -->
    <aside class="side-card">
      <div class="side-header">
        <h3 class="side-title">Tipos de siniestros</h3>
        <p class="events-count">40 eventos activos</p>
      </div>

      <div class="incident-list">
        <div class="incident-item" data-type="medical">
          <div class="incident-left">
            <div class="incident-icon medical"><i class="bi bi-heart-pulse"></i></div>
            <div class="incident-name">Emergencia médica</div>
          </div>
          <div class="incident-badge medical">12</div>
        </div>

        <div class="incident-item" data-type="fire">
          <div class="incident-left">
            <div class="incident-icon fire"><i class="bi bi-fire"></i></div>
            <div class="incident-name">Incendio</div>
          </div>
          <div class="incident-badge fire">5</div>
        </div>

        <div class="incident-item" data-type="assault">
          <div class="incident-left">
            <div class="incident-icon assault"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="incident-name">Asalto</div>
          </div>
          <div class="incident-badge assault">8</div>
        </div>

        <div class="incident-item" data-type="accident">
          <div class="incident-left">
            <div class="incident-icon accident"><i class="bi bi-car-front"></i></div>
            <div class="incident-name">Siniestro de tránsito</div>
          </div>
          <div class="incident-badge accident">15</div>
        </div>
      </div>

      <div class="side-footer">
        <div class="total-row">
          <span class="total-label">Total activos</span>
          <span class="total-value">40</span>
        </div>

        <div class="total-bars">
          <span class="bar medical"></span>
          <span class="bar fire"></span>
          <span class="bar assault"></span>
          <span class="bar accident"></span>
        </div>
      </div>
    </aside>

  </div>
</div>
@endsection

{{-- ✅ ORDEN CORRECTO: primero Leaflet, luego tu JS --}}
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>
<script src="{{ asset('js/Monitoreo.js') }}"></script>
@endpush
