<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'rol-listar',
            'rol-crear',
            'rol-editar',
            'rol-eliminar',
            'usuarios-listar',
            'usuarios-crear',
            'usuarios-editar',
            'usuarios-eliminar'
        ];

        $permissions4 = [
            'registrados-listar',
            'registrados-editar',
            
        ];

        $permissions5 = [
            'tipos-listar',
            'tipos-crear',
            'tipos-editar',
            'tipos-eliminar'
        ];

        $permissions6 = [
            'votos-listar',
            'votos-crear',
        ];

        $permissions7 = [
            'candidatos-listar',
            'candidatos-crear',
            'candidatos-editar',
            'candidatos-eliminar',
        ];

        $permissions8 = [
            'analisis-listar',
            'analisis-crear',
            'analisis-editar',
            'analisis-eliminar',
        ];

        $permissions9 = [
            'eventos-listar',
            'eventos-crear',
            'eventos-editar',
            'eventos-eliminar',
        ];

        foreach ($permissions9 as $permission) {
            Permission::create(['name' => $permission]);
        }
    }
}
