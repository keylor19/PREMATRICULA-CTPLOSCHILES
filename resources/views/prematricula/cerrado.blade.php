<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Prematrícula
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-8 border border-gray-200 text-center">
                <div class="text-5xl mb-4">🔒</div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Prematrícula no disponible</h3>
                <p class="text-sm text-gray-500">{{ $mensaje }}</p>
                <p class="text-xs text-gray-400 mt-4">Si tenés dudas, contactá al personal del colegio.</p>
            </div>
        </div>
    </div>
</x-app-layout>