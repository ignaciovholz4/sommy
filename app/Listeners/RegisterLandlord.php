<?php
namespace App\Listeners;

use App\Events\LandlordRegisteredEvent;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class RegisterLandlord
{
    public function __construct()
    {
        //
    }

    public function handle(LandlordRegisteredEvent $event)
    {
        $sqlFilePath = base_path('ecommerce_tero.sql');

        $dbName = 'tenant_' . $event->user->domaine;
        $escapedDbName = addslashes($dbName); // Sécurise la valeur
        
        if (!DB::select("SHOW DATABASES LIKE '$escapedDbName'")) {
            DB::statement("CREATE DATABASE `$escapedDbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                }

        // Commande sans mot de passe
        // $command = "mysql -u root $dbName < " . escapeshellarg($sqlFilePath);

        // $process = Process::fromShellCommandline($command);
        // $process->setTimeout(600); // ⏱️ 10 minutes (600 secondes)
        // $process->run();
        // if (!$process->isSuccessful()) {
        //     logger()->error("Erreur import SQL pour $dbName", [
        //         'error' => $process->getErrorOutput(),
        //         'output' => $process->getOutput(),
        //     ]);
        //     throw new \Exception("Échec import SQL : " . $process->getErrorOutput());
        // }
        // run artisan commande to magrate the databasephp artisan tenants:artisan "migrate --database=tenant" --tenant=2
// use Artisan::call to run artisan command
        DB::statement("GRANT ALL PRIVILEGES ON tenant_".$event->user->domaine.".* TO 'root'@'localhost';");
        DB::statement("CREATE DATABASE tenant_".$event->user->domaine);
        Artisan::call('tenants:artisan "migrate --database=tenant" --tenant=' .  $event->user->id  );
        Artisan::call('tenants:artisan "migrate --seed" --tenant=' .  $event->user->id);



        // $subdomain = $event->user->domaine . ".facturarg.test";
        // $hostsEntry = "127.0.0.1    $subdomain";
        
        // // Lire les lignes existantes
        // $hostsFile = '/etc/hosts';
        // $hostsContents = file_get_contents($hostsFile);
        
        // // Vérifier si l'entrée existe déjà
        // if (strpos($hostsContents, $subdomain) === false) {
        //     // Ajouter l'entrée
        //     file_put_contents($hostsFile, "\n$hostsEntry\n", FILE_APPEND);
        //     logger()->info("✅ Entrée ajoutée au fichier hosts : $hostsEntry");
        // }
        // logger()->info("✅ SQL import réussi pour la base [$dbName]");
        
            }
        } 
