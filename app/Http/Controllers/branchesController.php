<?php

namespace App\Http\Controllers;

use App\Models\Branches;
use Illuminate\Http\Request;

class branchesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cabangs = Branches::all();
        return view('karyawan.cabang', compact('cabangs'));
        
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

        Branches::create([
            'name' => $request->input('name'),
        ]);

        return redirect()->route('cabang.index')->with('success', 'Cabang created successfully.');
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
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $cabang = Branches::findOrFail($id);
        $cabang->update([
            'name' => $request->input('name'),
        ]);

        return redirect()->route('cabang.index')->with('success', 'Cabang updated successfully.');
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
    $cabang = Branches::findOrFail($id);
    $cabang->delete();

    return redirect()->route('cabang.index')->with('success', 'Cabang deleted successfully.');
    }
}
