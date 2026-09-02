<?php

namespace Database\Seeders;

use App\Models\Modalidad;
use Illuminate\Database\Seeder;

class ModalidadSeeder extends Seeder
{
    /**
     * Crea las 3 modalidades fijas del colegio.
     * Usa updateOrCreate para que sea seguro correrlo varias veces
     * (por nombre, no duplica si ya existen).
     */
    public function run(): void
    {
        $modalidades = ['Diurna', 'Nocturna', 'Plan Nacional'];

        foreach ($modalidades as $nombre) {
            $modalidad = Modalidad::updateOrCreate(
                ['nombre' => $nombre],
                ['activa' => true]
            );

            $this->command->info("Modalidad lista: {$modalidad->nombre} (id: {$modalidad->id})");
        }
    }
}