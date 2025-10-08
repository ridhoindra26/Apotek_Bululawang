<?php

namespace App\Http\Controllers;

use App\Models\Roles;
use Illuminate\Http\Request;

class pasanganController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pasangans = Roles::all();
        return view('karyawan.pasangan', compact('pasangans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
    $request->validate([
        'name' => 'required|string|max:255',
    ]);

    Roles::create([
        'name' => $request->input('name'),
    ]);

    return redirect()->route('pasangan.index')->with('success', 'Pasangan created successfully.');

    }

    /**
     * Display the specified resource.
     */
    public function show(c $c)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(c $c)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $pasangan = Roles::findOrFail($id);
        $pasangan->update([
            'name' => $request->input('name'),
        ]);

        return redirect()->route('pasangan.index')->with('success', 'Pasangan updated successfully.');
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $pasangan = Roles::findOrFail($id);
        $pasangan->delete();
        
        return redirect()->route('pasangan.index')->with('success', 'Pasangan deleted successfully.');
        
    }
}
