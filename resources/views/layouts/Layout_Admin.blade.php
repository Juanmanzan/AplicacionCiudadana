<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>@yield('title', 'Aplicación Ciudadana - Riobamba')</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

  <style>
    :root{
      --bg: #ffffff;
      --border: #e9ecef;
      --text: #111827;
      --muted: #6b7280;
      --primary: #0d6efd;
      --primary-soft: rgba(13, 110, 253, .10);
    }

    body{
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      background: #f8f9fa;
    }

    .topbar{
      background: var(--bg);
      border-bottom: 1px solid var(--border);
      position: sticky;
      top: 0;
      z-index: 1000;
    }

    .topbar .navbar{ padding: .6rem 0; }

    .brand-title{
      display: inline-flex;
      align-items: center;
      gap: .55rem;
      font-weight: 700;
      color: var(--text);
      text-decoration: none;
      font-size: 1.05rem;
      white-space: nowrap;
    }

    .brand-icon{
      width: 34px;
      height: 34px;
      border-radius: 999px;
      display: grid;
      place-items: center;
      background: var(--primary-soft);
      color: var(--primary);
      border: 1px solid rgba(13,110,253,.15);
      flex: 0 0 auto;
    }

    .topbar .nav-pills .nav-link{
      color: var(--muted);
      font-weight: 600;
      padding: .45rem 1rem;
      border-radius: .55rem;
      transition: .15s ease;
    }

    .topbar .nav-pills .nav-link:hover{
      background: #f1f3f5;
      color: #374151;
    }

    .topbar .nav-pills .nav-link.active{
      background: var(--primary);
      color: #fff;
      box-shadow: 0 6px 14px rgba(13,110,253,.18);
    }

    .user-chip{
      display: flex;
      align-items: center;
      gap: .6rem;
      padding-left: .25rem;
    }

    .user-avatar{
      width: 38px;
      height: 38px;
      border-radius: 999px;
      background: var(--primary-soft);
      color: var(--primary);
      display: grid;
      place-items: center;
      font-weight: 800;
      border: 1px solid rgba(13,110,253,.15);
      flex: 0 0 auto;
    }

    .user-name{
      font-weight: 700;
      color: var(--text);
      line-height: 1.05;
      font-size: .95rem;
    }

    .user-email{
      color: var(--muted);
      font-size: .82rem;
      line-height: 1.05;
    }

    .main-footer{
      background: var(--bg);
      border-top: 1px solid var(--border);
      margin-top: auto;
      padding: 14px 0;
    }

    .footer-logo{
      font-weight: 700;
      color: var(--text);
      font-size: .95rem;
      margin-bottom: 2px;
    }

    .footer-text{
      color: var(--muted);
      font-size: .85rem;
    }
  </style>

  @stack('styles')
</head>

<body>
  <!-- Header -->
  <header class="topbar">
    <nav class="navbar navbar-expand-lg navbar-light">
      <div class="container">

        <div class="d-flex align-items-center gap-3">
          <a class="brand-title" href="{{ url('/') }}">
            <span class="brand-icon">
              <i class="bi bi-shield-fill"></i>
            </span>
            Aplicación Ciudadana
          </a>

          <!-- Tabs desktop -->
          <ul class="nav nav-pills d-none d-lg-flex gap-2">
            <li class="nav-item">
              <a class="nav-link {{ request()->is('monitoreo') ? 'active' : '' }}" href="{{ url('/monitoreo') }}">
                Monitoreo
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link {{ request()->is('historial') ? 'active' : '' }}" href="{{ url('/historial') }}">
                Historial
              </a>
            </li>
          </ul>
        </div>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTop">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarTop">
          <!-- Tabs móvil -->
          <ul class="nav nav-pills gap-2 mt-3 d-lg-none">
            <li class="nav-item">
              <a class="nav-link {{ request()->is('monitoreo') ? 'active' : '' }}" href="{{ url('/monitoreo') }}">
                Monitoreo
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link {{ request()->is('historial') ? 'active' : '' }}" href="{{ url('/historial') }}">
                Historial
              </a>
            </li>
          </ul>

          <div class="d-flex align-items-center ms-lg-auto mt-3 mt-lg-0">
            <div class="user-chip">
              <div class="user-avatar">
                @php
                  $nombreCompleto = trim(($admin->nombres ?? '') . ' ' . ($admin->apellidos ?? ''));
                  $partes = preg_split('/\s+/', $nombreCompleto);

                  $inicialNombre = strtoupper(substr($partes[0] ?? 'A', 0, 1));
                  $inicialApellido = strtoupper(substr($partes[1] ?? 'D', 0, 1));

                  $initials = $inicialNombre . $inicialApellido;
                @endphp
                {{ $initials }}
              </div>

              <div class="d-flex flex-column">
                <div class="user-name">Administrador</div>
                <div class="user-email">{{ $admin->correo_electronico }}</div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </nav>
  </header>

  
  @php
    $fullWidth = trim($__env->yieldContent('fullWidth')) === 'true';
  @endphp

  <main class="{{ $fullWidth ? 'container-fluid p-0' : 'container py-4' }}">
    @yield('content')
  </main>

  <footer class="main-footer">
    <div class="container d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
      <div class="footer-logo">Aplicación Ciudadana</div>
      <div class="footer-text">© {{ date('Y') }} Aplicación Ciudadana. Todos los derechos reservados.</div>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

  @stack('scripts')
</body>
</html>
