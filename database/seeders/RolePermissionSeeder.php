<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permisos = [
            'poliza.ver',
            'poliza.crear',
            'poliza.editar',
            'poliza.enviar_revision',

            'poliza.observar',
            'poliza.validar',
            'poliza.bloquear',

            'documento.ver',
            'documento.subir',
            'documento.descargar',
            'documento.eliminar',

            'historial.ver',

            'reporte.ver',

            'usuarios.administrar',
            'roles.administrar',
            'catalogos.administrar',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate([
                'name' => $permiso,
                'guard_name' => 'web',
            ]);
        }

        $servicioExterior = Role::firstOrCreate([
            'name' => 'SERVICIO_EXTERIOR',
            'guard_name' => 'web',
        ]);

        $activosFijos = Role::firstOrCreate([
            'name' => 'ACTIVOS_FIJOS',
            'guard_name' => 'web',
        ]);

        $administrador = Role::firstOrCreate([
            'name' => 'ADMINISTRADOR',
            'guard_name' => 'web',
        ]);

        $servicioExterior->syncPermissions([
            'poliza.ver',
            'poliza.crear',
            'poliza.editar',
            'poliza.enviar_revision',

            'documento.ver',
            'documento.subir',
            'documento.descargar',

            'historial.ver',
        ]);

        $activosFijos->syncPermissions([
            'poliza.ver',
            'poliza.crear',
            'poliza.editar',
            'poliza.observar',
            'poliza.validar',
            'poliza.bloquear',

            'documento.ver',
            'documento.subir',
            'documento.descargar',

            'historial.ver',

            'reporte.ver',
        ]);

        $administrador->syncPermissions(
            Permission::all()
        );
    }
}