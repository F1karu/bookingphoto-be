<?php

namespace App\Http\Controllers;

use App\Models\Addon;
use Illuminate\Http\Request;

class AddonController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | PUBLIC - List All Addons
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        return response()->json([
            'message' => 'List addons',
            'data' => Addon::all()
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | PUBLIC - Show Single Addon
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        $addon = Addon::find($id);

        if (!$addon) {
            return response()->json([
                'message' => 'Addon not found'
            ], 404);
        }

        return response()->json([
            'message' => 'Addon detail',
            'data' => $addon
        ]);
    }

    



    //untuk admin
    public function store(Request $request)
    {
        $request->validate([
            'name'                => 'required|string|max:255',
            'price'               => 'required|integer|min:0',
            'auto_enhanced_photo' => 'required|boolean',
            'type'                => 'required|string|max:50',
        ]);

        $addon = Addon::create($request->only([
            'name',
            'price',
            'auto_enhanced_photo',
            'type'
        ]));

        return response()->json([
            'message' => 'Addon created successfully',
            'data' => $addon
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $addon = Addon::findOrFail($id);

        $request->validate([
            'name'                => 'sometimes|string|max:255',
            'price'               => 'sometimes|integer|min:0',
            'auto_enhanced_photo' => 'sometimes|boolean',
            'type'                => 'sometimes|string|max:50',
        ]);

        $addon->update($request->only([
            'name',
            'price',
            'auto_enhanced_photo',
            'type'
        ]));

        return response()->json([
            'message' => 'Addon updated successfully',
            'data' => $addon
        ]);
    }

public function restore($id)
{
    $addon = Addon::withTrashed()->findOrFail($id);

    if (!$addon->trashed()) {
        return response()->json([
            'message' => 'Addon is not deleted'
        ], 400);
    }

    $addon->restore();

    return response()->json([
        'message' => 'Addon restored successfully',
        'data' => $addon
    ]);
}

public function destroy($id)
{
    $addon = Addon::findOrFail($id);
    $addon->delete();

    return response()->json([
        'message' => 'Addon deleted (soft delete)'
    ]);
}

}
