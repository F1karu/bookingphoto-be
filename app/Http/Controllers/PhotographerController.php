<?php

namespace App\Http\Controllers;

use App\Models\Photographer;
use Illuminate\Http\Request;

class PhotographerController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin')->only(['store', 'update', 'destroy']);
}

   public function index()
   {
    $photographer = Photographer::all();
    return response()->json($photographer);
   }

   public function show($id)
   {
    $photographer = Photographer::findOrFail($id);
    return response()->json($photographer);
   }

   public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'phone' => 'required|string|max:20',
        'email' => 'required|email|unique:photographers,email',
        'bio' => 'required|string',
        'photo_url' => 'required|string',
        'location' => 'required|string',
        'price_per_hour' => 'required|numeric',
    ]);

    $photographer = Photographer::create([
        'name' => $request->name,
        'phone' => $request->phone,
        'email' => $request->email,
        'bio' => $request->bio,
        'photo_url' => $request->photo_url,
        'location' => $request->location,
        'price_per_hour' => $request->price_per_hour,
        'status' => 'available', 
    ]);

    return response()->json($photographer, 201);
}


   public function update(Request $request, $id)
   {
    $photographer = Photographer::findOrFail($id);

    $request->validate([
        'name' => 'sometimes|string|max:255',
        'phone' => 'sometimes|string|max:20',
            'email' => 'sometimes|email|unique:photographers,email,' . $id,
            'bio' => 'nullable|string',
            'photo_url' => 'nullable|string',
            'location' => 'nullable|string',
            'price_per_hour' => 'nullable|numeric',
            'status' => 'nullable|in:available,busy,offline',
    ]);

    $photographer->update($request->all());

    return response()->json($photographer);
   }

   public function destroy($id)
   {
    $photographer = Photographer::findOrFail($id);
    $photographer->delete();
    return response()->json(['message' => 'Photographer telah dihapus']);
   }


public function updateStatus(Request $request, $id)
{
    $photographer = Photographer::findOrFail($id);

    $request->validate([
        'status' => 'required|in:available,busy,offline',
    ]);

    $photographer->status = $request->status;
    $photographer->save();

    return response()->json($photographer);
}

}
