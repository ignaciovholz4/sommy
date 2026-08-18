<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        //ejecutar el seeder PermissionInfoSeeder
        $this->call(PermissionInfoSeeder::class);
        $this->call(ConfiguracionTableSeeder::class);
       
        // Seed documentation in landlord database
        $this->call(DocumentationSeeder::class);

        // planes de suscripciones
        // $this->call(PlanSeeder::class);
       
       // Note: TenantDocumentationsSeeder should not be called here
       // as documentation should be retrieved from landlord database
    }
}
