@extends('layouts.Layout_Admin')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<link rel="stylesheet" href="{{ asset('css/Historial.css') }}">
@endpush

@section('title', 'Historial')

@section('content')

{{-- ✅ Leaflet CSS (puedes moverlo al Layout si deseas) --}}


<div class="container py-4">

  {{-- Encabezado --}}
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <h1 class="h4 mb-0">Historial</h1>

    <div class="d-flex gap-2">
      <a class="btn btn-outline-primary btn-sm" href="{{ route('historial', request()->query()) }}">
        Actualizar
      </a>
    </div>
  </div>

  {{-- Filtros avanzados --}}
  <div class="card mb-3">
    <div class="card-body">
      <div class="d-flex align-items-center gap-2 mb-3">
        <strong>Filtros avanzados</strong>
      </div>

      <form method="GET" action="{{ route('historial') }}">
        <div class="row g-3">

          <div class="col-12 col-md-3">
            <label class="form-label mb-1">Género</label>
            <select name="genero" class="form-select">
              <option value="" {{ empty($filters['genero']) ? 'selected' : '' }}>Todos los géneros</option>
              <option value="M" {{ ($filters['genero'] ?? null) === 'M' ? 'selected' : '' }}>Femenino</option>
              <option value="H" {{ ($filters['genero'] ?? null) === 'H' ? 'selected' : '' }}>Masculino</option>
            </select>
          </div>

          <div class="col-12 col-md-3">
            <label class="form-label mb-1">Fecha desde</label>
            <input type="date" name="fecha_desde" class="form-control" value="{{ $filters['fecha_desde'] ?? '' }}">
          </div>

          <div class="col-12 col-md-3">
            <label class="form-label mb-1">Fecha hasta</label>
            <input type="date" name="fecha_hasta" class="form-control" value="{{ $filters['fecha_hasta'] ?? '' }}">
          </div>

          <div class="col-12 col-md-3">
            <label class="form-label mb-1">Estado</label>
            <select name="estado" class="form-select">
              <option value="" {{ empty($filters['estado']) ? 'selected' : '' }}>Todos</option>
              <option value="CONFIRMADA" {{ ($filters['estado'] ?? null) === 'CONFIRMADA' ? 'selected' : '' }}>Confirmada</option>
              <option value="ATENDIDA" {{ ($filters['estado'] ?? null) === 'ATENDIDA' ? 'selected' : '' }}>Atendida</option>
              <option value="FALSA_ALARMA" {{ ($filters['estado'] ?? null) === 'FALSA_ALARMA' ? 'selected' : '' }}>Falsa alarma</option>
            </select>
          </div>

          <div class="col-12 col-md-3">
            <label class="form-label mb-1">Hora desde</label>
            <input type="time" name="hora_desde" class="form-control" value="{{ $filters['hora_desde'] ?? '' }}">
          </div>
        </div>

        {{-- Tipos --}}
        <div class="mt-3">
          <label class="form-label mb-2">Tipo de emergencia</label>

          <div class="row g-2">
            @php
              $tiposSeleccionados = $filters['tipos'] ?? [];
            @endphp

            @forelse($tiposCatalogo as $t)
              <div class="col-12 col-md-3">
                <div class="form-check">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    name="tipos[]"
                    id="tipo_{{ $t->id }}"
                    value="{{ $t->id }}"
                    {{ in_array((int)$t->id, array_map('intval', $tiposSeleccionados)) ? 'checked' : '' }}
                  >
                  <label class="form-check-label" for="tipo_{{ $t->id }}">
                    {{ $t->nombre }}
                  </label>
                </div>
              </div>
            @empty
              <div class="col-12">
                <div class="alert alert-warning mb-0">
                  No se encontró catálogo de tipos de emergencia.
                </div>
              </div>
            @endforelse
          </div>
        </div>

        <div class="d-flex flex-wrap gap-2 mt-3">
          <button type="submit" class="btn btn-primary">Aplicar filtros</button>
          <a href="{{ route('historial') }}" class="btn btn-outline-secondary">Limpiar</a>
        </div>
      </form>

    </div>
  </div>

  {{-- Tabla --}}
  <div class="card">
    <div class="card-body">

      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div class="text-muted small">
          Mostrando {{ $rows->firstItem() ?? 0 }} - {{ $rows->lastItem() ?? 0 }} de {{ $rows->total() }} resultados
        </div>

        <form method="GET" action="{{ route('historial') }}" class="d-flex align-items-center gap-2">
          <input type="hidden" name="genero" value="{{ $filters['genero'] ?? '' }}">
          <input type="hidden" name="fecha_desde" value="{{ $filters['fecha_desde'] ?? '' }}">
          <input type="hidden" name="fecha_hasta" value="{{ $filters['fecha_hasta'] ?? '' }}">
          <input type="hidden" name="hora_desde" value="{{ $filters['hora_desde'] ?? '' }}">
          <input type="hidden" name="estado" value="{{ $filters['estado'] ?? '' }}">
          @foreach(($filters['tipos'] ?? []) as $tid)
            <input type="hidden" name="tipos[]" value="{{ $tid }}">
          @endforeach

          <label class="text-muted small mb-0">Filas:</label>
          <select name="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
            @foreach([10,15,20,30,50] as $n)
              <option value="{{ $n }}" {{ (int)($filters['per_page'] ?? 10) === $n ? 'selected' : '' }}>
                {{ $n }}
              </option>
            @endforeach
          </select>
        </form>
      </div>

      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th style="white-space:nowrap;">Fecha</th>
              <th style="white-space:nowrap;">Hora</th>
              <th style="white-space:nowrap;">Tipo</th>
              <th>Descripción</th>
              <th style="white-space:nowrap;">Usuario</th>
              <th style="white-space:nowrap;">Género</th>
              <th>Ubicación</th>
              <th style="white-space:nowrap;">Estado</th>
              <th class="text-end" style="white-space:nowrap;">Acción</th>
            </tr>
          </thead>

          <tbody>
          @forelse($rows as $r)
            @php
              $dt = \Carbon\Carbon::parse($r->fecha_hora);

              $usuario = trim(($r->nombres ?? '').' '.($r->apellidos ?? ''));
              $usuario = $usuario !== '' ? $usuario : 'N/D';

              $g = strtoupper(trim($r->genero ?? ''));
              $generoTxt = ($g === 'H') ? 'Masculino' : (($g === 'M') ? 'Femenino' : 'N/D');


              $lat = $r->lat ?? null;
              $lng = $r->lng ?? null;

              $ubicacionTxt = ($lat !== null && $lng !== null)
                ? number_format((float)$lat, 6).', '.number_format((float)$lng, 6)
                : 'N/D';

              $tipoBadge = 'text-bg-secondary';
              switch ((int)$r->id_tipo_emergencia) {
                case 1: $tipoBadge = 'text-bg-danger'; break;
                case 2: $tipoBadge = 'text-bg-warning'; break;
                case 3: $tipoBadge = 'text-bg-info'; break;
                case 4: $tipoBadge = 'text-bg-primary'; break;
              }

              $estado = $r->estado ?? 'N/D';
              $estadoBadge = 'text-bg-secondary';
              if ($estado === 'CONFIRMADA') $estadoBadge = 'text-bg-primary';
              if ($estado === 'ATENDIDA') $estadoBadge = 'text-bg-success';
              if ($estado === 'FALSA_ALARMA') $estadoBadge = 'text-bg-danger';
              $estadoLabel = ($estado !== 'N/D') ? str_replace('_', ' ', $estado) : 'N/D';

              $codigo = str_pad((int)$r->id_emergencia, 4, '0', STR_PAD_LEFT);
            @endphp

            <tr>
              <td>{{ $dt->format('Y-m-d') }}</td>
              <td>{{ $dt->format('H:i') }}</td>
              <td>
                <span class="badge {{ $tipoBadge }}">
                  {{ $tipoLabels[(int)$r->id_tipo_emergencia] ?? 'Emergencia' }}
                </span>
              </td>
              <td class="text-truncate" style="max-width: 260px;">{{ $r->descripcion ?? 'Sin descripción' }}</td>
              <td>{{ $usuario }}</td>
              <td><span class="badge text-bg-light border">{{ $generoTxt }}</span></td>
              <td class="text-truncate" style="max-width: 260px;">{{ $ubicacionTxt }}</td>
              <td><span class="badge {{ $estadoBadge }}">{{ $estadoLabel }}</span></td>

              <td class="text-end">
                <button type="button"
                    class="btn btn-sm btn-outline-primary btn-ver"
                    data-bs-toggle="modal"
                    data-bs-target="#detalleModal"
                    data-id="{{ $r->id_emergencia }}"
                    data-codigo="{{ $codigo }}"

                    {{-- ✅ CAMBIO: tipo desde catálogo fijo --}}
                    data-tipo="{{ $tipoLabels[(int)$r->id_tipo_emergencia] ?? 'Emergencia' }}"

                    data-fecha="{{ $dt->format('Y-m-d') }}"
                    data-hora="{{ $dt->format('H:i') }}"
                    data-usuario="{{ $usuario }}"
                    data-genero="{{ $generoTxt }}"
                    data-descripcion="{{ $r->descripcion ?? 'Sin descripción' }}"
                    data-ubicacion="{{ $ubicacionTxt }}"
                    data-estado="{{ $estado }}"
                    data-estado-label="{{ $estadoLabel }}"
                    data-origen="{{ strtoupper($r->origen ?? 'REPORTE') }}"


                    {{-- ✅ NUEVO: coordenadas reales para el mapa --}}
                    data-lat="{{ $r->lat }}"
                    data-lng="{{ $r->lng }}"
                  >
                    Ver
                </button>

              </td>
            </tr>
          @empty
            <tr>
              <td colspan="9" class="text-center text-muted py-4">
                No hay registros con los filtros actuales.
              </td>
            </tr>
          @endforelse
          </tbody>
        </table>
      </div>

      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
        <div class="text-muted small">
          Mostrando {{ $rows->firstItem() ?? 0 }} - {{ $rows->lastItem() ?? 0 }} de {{ $rows->total() }} resultados
        </div>

        <div>
          {{ $rows->links() }}
        </div>
      </div>

    </div>
  </div>

</div>

{{-- ✅ MODAL DETALLE --}}
<div class="modal fade" id="detalleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 720px;">
    <div class="modal-content" style="border-radius: 14px;">
      <div class="modal-body p-4">

        <div class="d-flex justify-content-between align-items-start mb-3">
          <div class="d-flex align-items-center gap-3">
            <div id="m_iconWrap"
                class="rounded-3 d-flex align-items-center justify-content-center"
                style="width:44px;height:44px;background:#f1f3f5;">
              <i id="m_icon" class="bi bi-question-circle" style="font-size:20px;"></i>
            </div>

            <div>
              <h5 class="mb-1">Detalle del Siniestro</h5>
              <div class="d-flex flex-wrap align-items-center gap-2">
                <span id="m_tipoBadge" class="badge text-bg-secondary">Tipo</span>
                <span class="text-muted" id="m_codigo">#0000</span>
                <span id="m_estadoBadge" class="badge text-bg-secondary">ESTADO</span>
              </div>
            </div>
          </div>

          <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">✕</button>
        </div>

        <hr class="my-3">

        <div class="row g-3 mb-3">
          <div class="col-12 col-md-4">
            <div class="text-muted small mb-1">Fecha</div>
            <div class="fw-semibold" id="m_fecha">-</div>
          </div>
          <div class="col-12 col-md-4">
            <div class="text-muted small mb-1">Hora</div>
            <div class="fw-semibold" id="m_hora">-</div>
          </div>
          <div class="col-12 col-md-4">
            <div class="text-muted small mb-1">Ubicación</div>
            <div class="fw-semibold" id="m_ubicacion">-</div>
          </div>
        </div>

        <div class="mb-3">
          <div class="text-muted small mb-1">Reportado por</div>
          <div class="fw-semibold" id="m_usuario">-</div>
          <div class="text-muted small" id="m_genero">-</div>
          <div class="text-muted small" id="m_origen">-</div>
        </div>

        <hr class="my-3">

        <div class="mb-3">
          <div class="text-muted small mb-1">Descripción</div>
          <div class="border rounded-3 p-3 bg-light" id="m_descripcion">-</div>
        </div>

        {{-- ✅ MAPA REAL (Leaflet) --}}
        <div class="mb-4">
          <div class="border rounded-3 overflow-hidden" style="height: 220px;">
            <div id="m_map" style="height: 220px; width: 100%;"></div>
          </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            Cerrar
          </button>

          <form id="m_formAtender" method="POST" action="#" style="display:none;">
            @csrf
            <button id="m_btnAtender" type="submit" class="btn btn-success">
              Confirmar atención
            </button>
          </form>
        </div>

      </div>
    </div>
  </div>
</div>

{{-- ✅ Leaflet JS --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

{{-- ✅ Tu JS externo --}}
<script src="{{ asset('js/historial.js') }}"></script>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const openId = @json($openId ?? null);
    if (!openId) return;

    const btn = document.querySelector(`.btn-ver[data-id="${openId}"]`);
    if (btn) btn.click();
  });
</script>

@endsection
