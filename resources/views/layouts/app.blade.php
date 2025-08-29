<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Inventario</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap + Iconos -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        html, body {
            height: 100%;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
        }

        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            color: white;
            padding-top: 20px;
            width: 240px;
            flex-shrink: 0;
        }

        .sidebar .nav-link {
            color: #ccc;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: #495057;
            color: #fff;
        }

        .accordion-button {
            background-color: #343a40;
            color: white;
        }

        .accordion-button:not(.collapsed) {
            background-color: #495057;
            color: white;
        }

        .accordion-item {
            background-color: #343a40;
            border: none;
        }

        /* Encabezado del sidebar */
        .sidebar-header {
            padding-left: 10px;   /* margen uniforme a la izquierda */
            line-height: 1.2;     /* separación entre título y subtítulo */
        }
        .sidebar-header h4 {
    margin: 0;
    font-weight: 400;   /* normal */
    font-size: 2rem;  /* más grande que el default */
        }
        .sidebar-header small {
            display: block;
            color: #bbb;
        }

        /* Select2 estilos */
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            white-space: normal !important;
            word-wrap: break-word;
            font-size: 0.9rem;
            line-height: 1.2rem;
        }

        .select2-results__option {
            white-space: normal !important;
            font-size: 0.9rem;
        }

        .compacto-input {
            font-size: 0.85rem;
            padding: 0.3rem 0.4rem;
        }

        .select2-container .select2-selection--single {
            height: auto !important;
            min-height: 38px;
        }

        .select2-selection__rendered {
            padding-top: 0.4rem !important;
        }
    </style>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<div class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar p-3">
        <div class="sidebar-header mb-3">
            <h4 class="mb-0">SINVARIS</h4>
            <small>Gestión con conocimiento</small>
        </div>

        <nav class="nav flex-column mt-3">
            <!-- 1. Panel Principal -->
            <a href="{{ route('panel.index') }}" class="nav-link {{ request()->routeIs('panel.index') ? 'active' : '' }}">
                <i class="bi bi-speedometer2 me-2"></i> Panel
            </a>

            <!-- 2. Gestión Comercial -->
            <div class="text-white-50 text-uppercase small mt-3 ms-1">Gestión Comercial</div>
            <a href="{{ route('ventas.create') }}" class="nav-link {{ request()->routeIs('ventas.create') ? 'active' : '' }}">
                <i class="bi bi-cart-plus me-2"></i> Nueva Venta
            </a>
            <a href="{{ route('ventas.index') }}" class="nav-link {{ request()->routeIs('ventas.index') ? 'active' : '' }}">
                <i class="bi bi-clock-history me-2"></i> Historial de Ventas
            </a>
            <a href="{{ route('clientes.index') }}" class="nav-link {{ request()->routeIs('clientes.*') ? 'active' : '' }}">
                <i class="bi bi-people me-2"></i> Clientes
            </a>

            <!-- 3. Gestión de Inventario -->
            <div class="text-white-50 text-uppercase small mt-3 ms-1">Inventario</div>
            <a href="{{ route('productos.index') }}" class="nav-link {{ request()->routeIs('productos.index') ? 'active' : '' }}">
                <i class="bi bi-box me-2"></i> Ver Productos
            </a>
            <a href="{{ route('productos.inventario') }}" class="nav-link {{ request()->routeIs('productos.inventario') ? 'active' : '' }}">
                <i class="bi bi-boxes me-2"></i> Stock Actual
            </a>
            <a href="{{ route('entradas.index') }}" class="nav-link {{ request()->routeIs('entradas.*') ? 'active' : '' }}">
                <i class="bi bi-arrow-down-circle me-2"></i> Entradas
            </a>
            <a href="{{ route('salidas.index') }}" class="nav-link {{ request()->routeIs('salidas.*') ? 'active' : '' }}">
                <i class="bi bi-arrow-up-circle me-2"></i> Salidas
            </a>
            <a href="{{ route('traspasos.index') }}" class="nav-link {{ request()->routeIs('traspasos.*') ? 'active' : '' }}">
                <i class="bi bi-arrow-left-right me-2"></i> Traspasos
            </a>

            <!-- 4. Reportes -->
            <div class="text-white-50 text-uppercase small mt-3 ms-1">Reportes</div>
            <a href="{{ route('panel-decisiones') }}" class="nav-link {{ request()->routeIs('panel-decisiones') ? 'active' : '' }}">
                <i class="bi bi-graph-up-arrow me-2"></i> Reportes
            </a>
            <a href="{{ route('kardex.index') }}" class="nav-link {{ request()->routeIs('kardex.*') ? 'active' : '' }}">
                <i class="bi bi-journal-text me-2"></i> Kardex
            </a>

            <!-- 5. Admin -->
            @if(Auth::user() && Auth::user()->role == 'admin')
                <div class="text-white-50 text-uppercase small mt-3 ms-1">Administración</div>
                <a href="{{ route('usuarios.index') }}" class="nav-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}">
                    <i class="bi bi-person-gear me-2"></i> Usuarios
                </a>
                <a href="{{ route('auditoria.index') }}" class="nav-link {{ request()->routeIs('auditoria.*') ? 'active' : '' }}">
                    <i class="bi bi-shield-check me-2"></i> Auditoría
                </a>
                <a href="{{ route('configuracion.index') }}" class="nav-link {{ request()->routeIs('configuracion.*') ? 'active' : '' }}">
                    <i class="bi bi-sliders me-2"></i> Configuración
                </a>
            @endif

            <!-- Perfil -->
            <div class="text-white-50 text-uppercase small mt-3 ms-1">Cuenta</div>
            <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                <i class="bi bi-person-circle me-2"></i> Perfil
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="d-block w-100 text-start py-2 px-3 btn btn-link text-white">
                    <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                </button>
            </form>
        </nav>
    </div>

    <!-- Contenido -->
    <div class="flex-grow-1 p-4">

        {{-- Mensajes flash globales --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {!! session('success') !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {!! session('error') !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <strong>Revisa los campos:</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $msg)
                        <li>{{ $msg }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        @yield('content')
    </div>
</div>

<!-- Bootstrap Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Auto-cierre de alertas -->
<script>
  setTimeout(() => {
    document.querySelectorAll('.alert.show .btn-close').forEach(btn => btn.click());
  }, 4000);
</script>

@stack('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>
</html>
