<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class DatabaseService
{


    public function changeDatabase($database_name)
    {

        config(['database.connections.tenant.database' => $database_name]);
        DB::purge('tenant');
        DB::reconnect('tenant');
    }


    public function changeDatabaseToDefault()
    {

        // Switch back to central database for token storage
        DB::connection('mysql')->reconnect();
    }

    public function createNewDatabaseAndMigrate($name)
    {
        // Create new database for tenant
        DB::statement("CREATE DATABASE `$name`");

        $this->changeDatabase($name);

        Artisan::call('migrate', ['--database' => 'tenant']);


    }
}
