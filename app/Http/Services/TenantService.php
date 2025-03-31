<?php

namespace App\Http\Services;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TenantService
{


    public function getTenantInfotFromLocalDatabase($name)
    {

        $tenant = Tenant::where('name', $name)->first();

        return $tenant;
    }

    public function getTenantUserFromLocalDatabase($email)
    {

        $tenantUser = User::where('email', $email)->first();

        return $tenantUser;
    }

    public function getTenantUserFromOtherDatabase($email)
    {

        $tenantUser = User::on('tenant')
            ->where('email', $email)->first();

        return $tenantUser;
    }

    public function createTenant($name, $database_name) {
        $tenant = Tenant::create([
            'name' => $name,
            'database_name' => $database_name
        ]);

        return $tenant;
    }

    public function createTenantUser($name, $email,$password) {
        // Create user inside the new tenant database
       $create = DB::connection('tenant')->table('users')->insert([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $create ? true : false;

    }


    public function createTenantNote($name) {
        // Add default note
       $create =  DB::connection('tenant')->table('notes')->insert([
            'content' => "Welcome, {$name}!",
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $create ? true : false;

    }
}
