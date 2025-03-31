<?php

namespace App\Http\Controllers;

use App\Http\Services\DatabaseService;
use App\Http\Services\TenantService;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class AuthController extends Controller
{
    private $databaseService;
    private $tenantService;

    public function __construct(DatabaseService $databaseService, TenantService $tenantService)
    {
        $this->databaseService = $databaseService;
        $this->tenantService = $tenantService;
    }
    public function login(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "errors" => $validator->errors(),
            ]);
        }

        $credentials = $request->only('email', 'password');

        // Get Tenant (Name - Database Name) From Local Database
        $tenant = $this->tenantService->getTenantInfotFromLocalDatabase($request->name);

        if (!$tenant) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }

        // Change Database to Tenant Database
        $this->databaseService->changeDatabase($tenant->database_name);

        // Get User Data From Tenant Database
        $tenantUser = $this->tenantService->getTenantUserFromOtherDatabase($credentials['email']);

        if (!$tenantUser) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }

        // Check Credentials
        if (!$tenantUser || !Hash::check($credentials['password'], $tenantUser->password)) {
            return response()->json([
                'status' => false,
                'error' => 'Invalid credentials'
            ], 401);
        }

        // Get Tenant User Form Local DB
        $tenantUserLocal = $this->tenantService->getTenantUserFromLocalDatabase($credentials['email']);


        if (!$tenantUserLocal) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }

        // Create token
        $token = $tenantUserLocal->createToken('authToken')->accessToken;

        return response()->json([
            'token' => $token,
            'user' => $tenantUser,
            'message' => 'User authenticated successfully',
        ]);
    }


    public function sign_up(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "errors" => $validator->errors(),
            ]);
        }


        // Store user into default database
        DB::table('users')->insert([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $databaseName = 'tenant_' . strtolower(str_replace(' ', '_', $request->name)) . "_" .  Str::random(8);

        // Create tenant in system database
        $tenant = $this->tenantService->createTenant($request->name, $databaseName);

        // Create New Database
        $this->databaseService->createNewDatabaseAndMigrate($databaseName);


        // Create User in Tenant Database
        $newTenantUser =  $this->tenantService->createTenantUser($request->name, $request->email, $request->password);


        $newTenantNote = $this->tenantService->createTenantNote($request->name);


        if ($tenant && $newTenantUser && $newTenantNote) {
            return response()->json([
                'status' => true,
                'message' => 'Tenant registered successfully!'
            ], 200);
        }
    }


    public function checkCredentials($user, $password)
    {
        // Check for credentials
        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json([
                'status' => false,
                'error' => 'Invalid credentials'
            ], 401);
        }
    }
}
