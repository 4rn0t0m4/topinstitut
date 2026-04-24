<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Support\Facades\DB;

class ImportController extends Controller
{
    private const SEARCH_TYPES = [
        'institut de beauté',
        'spa bien-être',
        'esthéticienne',
        'thalassothérapie',
    ];

    public function index()
    {
        $cursor = DB::table('google_import_cursor')->where('id', 1)->first();

        $stats = Department::leftJoin('establishments', function ($join) {
            $join->on('establishments.department_code', '=', 'departments.code')
                ->whereNotNull('establishments.google_place_id');
        })
            ->selectRaw('departments.code, departments.name,
                COUNT(establishments.id) as imported_count,
                MAX(establishments.created_at) as last_import_at')
            ->groupBy('departments.code', 'departments.name')
            ->orderBy('departments.code')
            ->get();

        return view('admin.imports.index', [
            'cursor' => $cursor,
            'stats' => $stats,
            'searchTypes' => self::SEARCH_TYPES,
            'totalImported' => $stats->sum('imported_count'),
        ]);
    }
}
