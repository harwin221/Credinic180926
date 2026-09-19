<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class userAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'username' => 'administrador',
            'nombres' => 'Administrador',
            'apellidos' => 'Administrador',
            'direccion' => 'n-a',
            'cedula' => '000-000000-0000X',
            'estado' => '1',
            'email' => 'admin@finanmas.com',
            'tipo_usuario' => 1,
            'created_user_id' => 1,
            'updated_user_id' => 1,
            'password' => \Hash::make('demo2023')
        ])->assignRole('Administrador');

        User::create([
            'username' => 'administrativo',
            'nombres' => 'Administrativo',
            'apellidos' => 'Administrativo',
            'direccion' => 'n-a',
            'cedula' => '000-000000-0000Y',
            'estado' => '1',
            'email' => 'administrativo@finanmas.com',
            'tipo_usuario' => 2,
            'created_user_id' => 1,
            'updated_user_id' => 1,
            'password' => \Hash::make('test2023')
        ])->assignRole('Administrador');

        User::create([
            'username' => 'sin_asignar_fiador',
            'nombres' => 'SIN ASIGNAR',
            'apellidos' => '',
            'direccion' => 'n-a',
            'cedula' => '000-000000-00000',
            'estado' => '1',
            'email' => 'sin_asignar_fiador@finanmas.com',
            'tipo_usuario' => 5,
            'created_user_id' => 1,
            'updated_user_id' => 1,
            'password' => \Hash::make('test2023')
        ]);

        User::create([
            'username' => 'sin_asignar_agente',
            'nombres' => 'SIN ASIGNAR',
            'apellidos' => '',
            'direccion' => 'n-a',
            'cedula' => '999-999999-99999',
            'estado' => '1',
            'email' => 'sin_asignar_agente@finanmas.com',
            'tipo_usuario' => 4,
            'created_user_id' => 1,
            'updated_user_id' => 1,
            'password' => \Hash::make('test2023')
        ]);

        User::create([
            'username' => 'tamara_ag',
            'nombres' => 'TAMARA PATRICIA',
            'apellidos' => 'LOPEZ URBINA',
            'direccion' => 'PLANTEL DE LA ALCALDIA 25VRS AL ESTE - FUNDECI 3RA ETAPA',
            'cedula' => '281-120887-0010D',
            'estado' => '1',
            'email' => 'tamara@finanmas.com',
            'tipo_usuario' => 4,
            'created_user_id' => 1,
            'updated_user_id' => 1,
            'password' => \Hash::make('tamara2024')
        ]);

        User::create([
            'username' => 'maykell_ag',
            'nombres' => 'MAYKELL ABRAHAM',
            'apellidos' => 'HERNANDEZ BALLADARES',
            'direccion' => 'PORTON IMPLAGSA 1C AL NORTE 1/2C AL OESTE - VILLA23 JULIO',
            'cedula' => '281-070793-0001R',
            'estado' => '1',
            'email' => 'maykell_agente@finanmas.com',
            'tipo_usuario' => 4,
            'created_user_id' => 1,
            'updated_user_id' => 1,
            'password' => \Hash::make('maykell2024')
        ]);

        User::create([
            'username' => 'joel_ag',
            'nombres' => 'JOEL ERNESTO',
            'apellidos' => 'FLORES RAMIREZ',
            'direccion' => 'IGLESIA SN JOSE 3C AL NORTE 1/2C AL ESTE - SAN FELIPE',
            'cedula' => '281-060581-0009C',
            'estado' => '1',
            'email' => 'joel@finanmas.com',
            'tipo_usuario' => 4,
            'created_user_id' => 1,
            'updated_user_id' => 1,
            'password' => \Hash::make('joel2024')
        ]);

        User::create([
            'username' => 'jaime_ag',
            'nombres' => 'JAIME JOSE',
            'apellidos' => 'TORRES ZAPATA',
            'direccion' => 'DONDE FUE GASOLINERA TEXACO 2C 1/2 AL ESTE - GUADALUPE',
            'cedula' => '281-150892-0014M',
            'estado' => '0',
            'email' => 'jaime_gestor@finanmas.com',
            'tipo_usuario' => 4,
            'created_user_id' => 1,
            'updated_user_id' => 1,
            'password' => \Hash::make('jaime2024')
        ]);

        User::create([
            'username' => 'jaime',
            'nombres' => 'JAIME JOSE',
            'apellidos' => 'TORRES ZAPATA',
            'direccion' => 'DONDE FUE GASOLINERA TEXACO 2C 1/2 AL ESTE - GUADALUPE',
            'cedula' => '281-150892-0014M',
            'estado' => '0',
            'email' => 'jaime@finanmas.com',
            'tipo_usuario' => 2,
            'created_user_id' => 1,
            'updated_user_id' => 1,
            'password' => \Hash::make('jaime2024')
        ]);

        User::create([
            'username' => 'lester_ag',
            'nombres' => 'LESTER DANILO',
            'apellidos' => 'GARCIA',
            'direccion' => 'RPTO 1RO DE MAYO, DEL ALTO 5C AL NORTE 1/2 AL ESTE',
            'cedula' => '281-190885-0007B',
            'estado' => '1',
            'email' => 'lester_gestor@finanmas.com',
            'tipo_usuario' => 4,
            'created_user_id' => 1,
            'updated_user_id' => 1,
            'password' => \Hash::make('lester2024')
        ]);

        User::create([
            'username' => 'lester',
            'nombres' => 'LESTER DANILO',
            'apellidos' => 'GARCIA',
            'direccion' => 'RPTO 1RO DE MAYO, DEL ALTO 5C AL NORTE 1/2 AL ESTE',
            'cedula' => '281-190885-0007B',
            'estado' => '1',
            'email' => 'lester@finanmas.com',
            'tipo_usuario' => 2,
            'created_user_id' => 1,
            'updated_user_id' => 1,
            'password' => \Hash::make('lester2024')
        ]);

        User::create([
            'username' => 'martin',
            'nombres' => 'MARTIN NOEL',
            'apellidos' => 'BHERVIZ',
            'direccion' => 'EL SAGRARIO, IGLESIA LA RECOLECCION 1C AL NORTE 1/2 C AL OESTE',
            'cedula' => '281-210473-0023M',
            'estado' => '1',
            'email' => 'martin@finanmas.com',
            'tipo_usuario' => 2,
            'created_user_id' => 1,
            'updated_user_id' => 1,
            'password' => \Hash::make('martin2024')
        ]);

        User::create([
            'username' => 'martin_ag',
            'nombres' => 'MARTIN NOEL',
            'apellidos' => 'BHERVIZ',
            'direccion' => 'EL SAGRARIO, IGLESIA LA RECOLECCION 1C AL NORTE 1/2 C AL OESTE',
            'cedula' => '281-210473-0023M',
            'estado' => '1',
            'email' => 'martin_agente@finanmas.com',
            'tipo_usuario' => 4,
            'created_user_id' => 1,
            'updated_user_id' => 1,
            'password' => \Hash::make('martin2024')
        ]);

        User::create([
            'username' => 'kevin',
            'nombres' => 'KEVIN ALEXANDER',
            'apellidos' => 'CERDA VINDEL',
            'direccion' => 'De la terminal de la 101 1C. Arriba 1 1/2 c. Al sur',
            'cedula' => '001-270497-0000A',
            'estado' => '1',
            'email' => 'kevin@finanmas.com',
            'tipo_usuario' => 2,
            'created_user_id' => 1,
            'updated_user_id' => 1,
            'password' => \Hash::make('kevin2024')
        ]);
        User::create([
            'username' => 'pamela',
            'nombres' => 'PAMELA DE LOS ANGELES',
            'apellidos' => 'PINELL TELLEZ',
            'direccion' => 'Dr.Cayatetano Munguia 120 varas al este.',
            'cedula' => '281-140699-1007N',
            'estado' => '1',
            'email' => 'pamela@finanmas.com',
            'tipo_usuario' => 2,
            'created_user_id' => 1,
            'updated_user_id' => 1,
            'password' => \Hash::make('pamela2024')
        ]);

        User::create([
            'username' => 'rolando',
            'nombres' => 'ROLANDO',
            'apellidos' => '-',
            'direccion' => '-',
            'cedula' => '-',
            'estado' => '1',
            'email' => 'rolando@finanmas.com',
            'tipo_usuario' => 2,
            'created_user_id' => 1,
            'updated_user_id' => 1,
            'password' => \Hash::make('rolando2024')
        ]);
        User::create([
            'username' => 'rolando_ag',
            'nombres' => 'ROLANDO',
            'apellidos' => '-',
            'direccion' => '-',
            'cedula' => '-',
            'estado' => '1',
            'email' => 'rolando_agente@finanmas.com',
            'tipo_usuario' => 4,
            'created_user_id' => 1,
            'updated_user_id' => 1,
            'password' => \Hash::make('rolando2024')
        ]);

        User::create([
            'username' => 'bielka',
            'nombres' => 'BIELKA',
            'apellidos' => '-',
            'direccion' => '-',
            'cedula' => '-',
            'estado' => '1',
            'email' => 'bielka@finanmas.com',
            'tipo_usuario' => 2,
            'created_user_id' => 1,
            'updated_user_id' => 1,
            'password' => \Hash::make('bielka2024')
        ]);

        User::create([
            'username' => 'ingrid',
            'nombres' => 'INGRID',
            'apellidos' => '-',
            'direccion' => '-',
            'cedula' => '-',
            'estado' => '1',
            'email' => 'ingrid@finanmas.com',
            'tipo_usuario' => 2,
            'created_user_id' => 1,
            'updated_user_id' => 1,
            'password' => \Hash::make('ingrid2024')
        ]);

        User::create([
            'username' => 'ingrid_ag',
            'nombres' => 'INGRID',
            'apellidos' => '-',
            'direccion' => '-',
            'cedula' => '-',
            'estado' => '1',
            'email' => 'ingrid_agente@finanmas.com',
            'tipo_usuario' => 4,
            'created_user_id' => 1,
            'updated_user_id' => 1,
            'password' => \Hash::make('ingrid2024')
        ]);

        User::create([
            'username' => 'joseline',
            'nombres' => 'JOSELINE',
            'apellidos' => '-',
            'direccion' => '-',
            'cedula' => '-',
            'estado' => '1',
            'email' => 'joseline@finanmas.com',
            'tipo_usuario' => 2,
            'created_user_id' => 1,
            'updated_user_id' => 1,
            'password' => \Hash::make('joseline2024')
        ]);

        User::create([
            'username' => 'mauricio',
            'nombres' => 'MAURICIO',
            'apellidos' => '-',
            'direccion' => '-',
            'cedula' => '-',
            'estado' => '1',
            'email' => 'mauricio@finanmas.com',
            'tipo_usuario' => 2,
            'created_user_id' => 1,
            'updated_user_id' => 1,
            'password' => \Hash::make('mauricio2024')
        ]);

        User::create([
            'username' => 'mauricio_ag',
            'nombres' => 'MAURICIO',
            'apellidos' => '-',
            'direccion' => '-',
            'cedula' => '-',
            'estado' => '1',
            'email' => 'mauricio_agente@finanmas.com',
            'tipo_usuario' => 4,
            'created_user_id' => 1,
            'updated_user_id' => 1,
            'password' => \Hash::make('mauricio2024')
        ]);

        User::create([
            'username' => 'victor',
            'nombres' => 'VICTOR',
            'apellidos' => '-',
            'direccion' => '-',
            'cedula' => '-',
            'estado' => '1',
            'email' => 'victor@finanmas.com',
            'tipo_usuario' => 2,
            'created_user_id' => 1,
            'updated_user_id' => 1,
            'password' => \Hash::make('victor2024')
        ]);

        User::create([
            'username' => 'victor_ag',
            'nombres' => 'VICTOR',
            'apellidos' => '-',
            'direccion' => '-',
            'cedula' => '-',
            'estado' => '1',
            'email' => 'victor_agente@finanmas.com',
            'tipo_usuario' => 4,
            'created_user_id' => 1,
            'updated_user_id' => 1,
            'password' => \Hash::make('victor2024')
        ]);

        User::create([
            'username' => 'ruth',
            'nombres' => 'RUTH BELEN',
            'apellidos' => 'FLORES',
            'direccion' => '-',
            'cedula' => '281-201101-1016B	',
            'estado' => '1',
            'email' => 'ruth@finanmas.com',
            'tipo_usuario' => 2,
            'created_user_id' => 1,
            'updated_user_id' => 1,
            'password' => \Hash::make('ruth2024')
        ]);

          User::create([
            'username' => 'maykell',
            'nombres' => 'MAYKELL ABRAHAM',
            'apellidos' => 'HERNANDEZ BALLADARES',
            'direccion' => '-',
            'cedula' => '281-201101-1016B	',
            'estado' => '1',
            'email' => 'maykell@finanmas.com',
            'tipo_usuario' => 2,
            'created_user_id' => 1,
            'updated_user_id' => 1,
            'password' => \Hash::make('maykell2024')
        ]);
    }
}
