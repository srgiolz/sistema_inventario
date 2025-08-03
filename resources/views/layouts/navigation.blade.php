<nav class="bg-white shadow-md h-screen p-4 overflow-y-auto">
    <ul class="space-y-2">

        <!-- Panel principal -->
        <li>
            <a href="{{ route('panel.index') }}" class="block px-3 py-2 rounded hover:bg-gray-200">
                🏠 Panel Principal
            </a>
        </li>

        <!-- Productos con submenú -->
        <li>
            <details class="group">
                <summary class="cursor-pointer px-3 py-2 rounded hover:bg-gray-200">
                    📦 Productos
                </summary>
                <ul class="ml-4 mt-2 space-y-1 transition-all duration-200 ease-in-out">
                    <li>
                        <a href="{{ route('productos.index') }}" class="block px-3 py-1 hover:bg-gray-100">
                            🧾 Ver productos
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('entradas.index') }}" class="block px-3 py-1 hover:bg-gray-100">
                            📥 Entradas
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('salidas.index') }}" class="block px-3 py-1 hover:bg-gray-100">
                            📤 Salidas
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('traspasos.index') }}" class="block px-3 py-1 hover:bg-gray-100">
                            🔄 Traspasos
                        </a>
                    </li>
                </ul>
            </details>
        </li>

        <!-- Clientes -->
        <li>
            <a href="{{ route('clientes.index') }}" class="block px-3 py-2 rounded hover:bg-gray-200">
                👤 Clientes
            </a>
        </li>

        <!-- Ventas -->
        <li>
            <a href="{{ route('ventas.index') }}" class="block px-3 py-2 rounded hover:bg-gray-200">
                💰 Ventas
            </a>
        </li>

        <!-- Panel de decisiones -->
        <li>
            <a href="{{ route('panel-decisiones') }}" class="block px-3 py-2 rounded hover:bg-gray-200">
                📊 Panel de Decisiones
            </a>
        </li>

    </ul>
</nav>
