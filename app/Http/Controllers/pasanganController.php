<?php

namespace App\Http\Controllers;

use App\Models\Roles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class pasanganController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pasangans = Roles::orderBy('index')->get();
        $indexChoices = range(1, $pasangans->count());

        return view('karyawan.pasangan', compact('pasangans', 'indexChoices'));
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

    $nextIndex = Roles::max('index') + 1;

    Roles::create([
        'name' => $request->input('name'),
        'index' => $nextIndex,
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
            'index' => 'required|integer',
        ]);

        $pasangan = Roles::findOrFail($id);
        $newIndex = $validatedData['index'];
        $oldIndex = $pasangan->index;

        DB::transaction(function () use ($pasangan, $newIndex, $oldIndex, $validatedData) {
            $existing = Roles::where('index', $newIndex)->first();

            if ($existing && $existing->id !== $pasangan->id) {
                $existing->update(['index' => $oldIndex]);
            }

            $pasangan->update([
                'name'  => $validatedData['name'],
                'index' => $newIndex,
            ]);
        });

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
