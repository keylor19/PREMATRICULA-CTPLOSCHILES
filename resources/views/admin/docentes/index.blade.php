<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gestión de usuarios
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Crear cuenta --}}
            <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-200">
                <h3 class="text-base font-semibold text-gray-900 mb-4">Crear cuenta de usuario</h3>
                <form method="POST" action="{{ route('admin.docentes.store') }}">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre completo *</label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico *</label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña *</label>
                            <input type="password" name="password" required minlength="8"
                                class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar contraseña *</label>
                            <input type="password" name="password_confirmation" required
                                class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de cuenta *</label>
                            <select name="rol" required class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="docente">Docente — Solo puede registrar prematrículas</option>
                                <option value="admin">Administrador — Acceso completo al sistema</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit"
                        class="bg-blue-700 hover:bg-blue-800 text-white text-sm font-medium px-5 py-2 rounded-md">
                        Crear cuenta
                    </button>
                </form>
            </div>

            {{-- Lista de usuarios --}}
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                <div class="p-4 border-b border-gray-100">
                    <h3 class="text-base font-semibold text-gray-900">Usuarios registrados</h3>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Correo</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Creado</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($usuarios as $usuario)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $usuario->name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $usuario->email }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @if ($usuario->rol === 'admin')
                                        <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium bg-purple-50 text-purple-700 border border-purple-200">
                                            Administrador
                                        </span>
                                    @else
                                        <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                            Docente
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-400">{{ $usuario->created_at->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-right">
                                    @if ($usuario->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.docentes.destroy', $usuario) }}"
                                            onsubmit="return confirm('¿Seguro que querés eliminar esta cuenta?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-medium">
                                                Eliminar
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-gray-300">Tu cuenta</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-400">
                                    No hay usuarios registrados todavía.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>