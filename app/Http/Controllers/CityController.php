<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $query = City::query();

        if ($search) {
            
            $search = strtolower($search);

            $query->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
        }

        return response()->json($query->limit(50)->get());
    }
}
