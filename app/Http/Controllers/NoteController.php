<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Http\Services\DatabaseService;
use App\Http\Services\TenantService;
use App\Models\Note;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NoteController extends Controller
{

    private $databaseService;
    private $tenantService;

    public function __construct(DatabaseService $databaseService, TenantService $tenantService)
    {
        $this->databaseService = $databaseService;
        $this->tenantService = $tenantService;
    }


    function getNotes(Request $request)
    {


        $tenant = $this->tenantService->getTenantInfotFromLocalDatabase(Auth::guard("api")->user()->name);

        if (!$tenant) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }

        $this->databaseService->changeDatabase($tenant->database_name);

        // Fetch notes
        $notes = DB::connection('tenant')->table('notes')->get();


        return response()->json($notes);
    }
}
