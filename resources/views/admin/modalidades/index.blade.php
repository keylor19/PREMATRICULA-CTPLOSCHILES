<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Modalidades de prematrícula
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

            {{-- Asignar docente a modalidad --}}
            <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-200">
                <h3 class="text-base font-semibold text-gray-900 mb-4">Asignar docente a modalidad</h3>
                <form method="POST" action="{{ route('admin.modalidades.asignar') }}">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Docente *</label>
                            <select name="user_id" required class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Seleccionar docente</option>
                                @foreach ($docentes as $docente)
                                    <option value="{{ $docente->id }}">{{ $docente->name }} — {{ $docente->email }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Modalidad *</label>
                            <select name="modalidad_id" required class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Seleccionar modalidad</option>
                                @foreach ($modalidades as $modalidad)
                                    <option value="{{ $modalidad->id }}">{{ $modalidad->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="bg-blue-700 hover:bg-blue-800 text-white text-sm font-medium px-5 py-2 rounded-md">
                        Asignar modalidad
                    </button>
                </form>
            </div>

            {{-- Modalidades con sus docentes --}}
            @foreach ($modalidades as $modalidad)
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                    <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">{{ $modalidad->nombre }}</h3>
                            @if ($modalidad->descripcion)
                                <p class="text-xs text-gray-400">{{ $modalidad->descripcion }}</p>
                            @endif
                        </div>
                        <span class="inline-flex px-2 py-1 rounded-full text-xs font-medium {{ $modalidad->activa ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-gray-100 text-gray-500' }}">
                            {{ $modalidad->activa ? 'Activa' : 'Inactiva' }}
                        </span>
                    </div>

                    <div class="p-4">
                        <p class="text-xs font-medium text-gray-500 uppercase mb-3">Docentes asignados</p>
                        @if ($modalidad->docentes->count() > 0)
                            <div class="space-y-2">
                                @foreach ($modalidad->docentes as $docente)
                                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded-md border border-gray-100">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $docente->name }}</p>
                                            <p class="text-xs text-gray-400">{{ $docente->email }}</p>
                                        </div>
                                        <form method="POST" action="{{ route('admin.modalidades.quitar') }}">
                                            @csrf
                                            <input type="hidden" name="user_id" value="{{ $docente->id }}">
                                            <input type="hidden" name="modalidad_id" value="{{ $modalidad->id }}">
                                            <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-medium">
                                                Quitar
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-400">No hay docentes asignados a esta modalidad.</p>
                        @endif
                    </div>
                </div>
            @endforeach

        </div>
    </div>
</x-app-layout>