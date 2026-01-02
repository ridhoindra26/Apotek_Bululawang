<?php

namespace App\Http\Controllers;

use App\Models\Employees;
use App\Models\Branches;
use App\Models\Roles;

use Illuminate\Http\Request;

class karyawanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $total = Employees::count();
        $branches = Branches::select('id', 'name')
        ->withCount('employees') // akan menghasilkan kolom employees_count
        ->get();
        $karyawans = Employees::with(['branches', 'roles'])->get();

        return view('karyawan.index', compact(
            'total',
            'branches',
            'karyawans',
        ));
        
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $pasangans = Roles::all();
        $cabangs = Branches::all();
        return view('karyawan.create', compact('pasangans', 'cabangs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'id_branch' => 'required|exists:App\Models\Branches,id',
            'id_role' => 'nullable|exists:App\Models\Roles,id',
        ]);

        Employees::create([
            'name' => $request->input('name'),
            'id_branch' => $request->input('id_branch'),
            'id_role' => $request->input('id_role'),
        ]);

        return redirect()->route('karyawan.index')->with('success', 'Karyawan created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
    $karyawan = Employees::with(['branches', 'roles'])->findOrFail($id);
    return view('karyawan.show', compact('karyawan'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $pasangans = Roles::all();
        $cabangs = Branches::all();
        $karyawan = Employees::with(['branches', 'roles'])->findOrFail($id);
        return view('karyawan.edit', compact('karyawan', 'pasangans', 'cabangs'));
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'id_branch' => 'required|exists:App\Models\Branches,id',
                'id_role' => 'nullable|exists:App\Models\Roles,id',
            ]);

            $karyawan = Employees::findOrFail($id);
            $karyawan->update([
                'name' => $request->input('name'),
                'id_branch' => $request->input('id_branch'),
                'id_role' => $request->input('id_role'),
            ]);

            return redirect()->route('karyawan.index')->with('success', 'Karyawan updated successfully.');
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $karyawan = Employees::findOrFail($id);
        $karyawan->delete();
        return redirect()->route('karyawan.index')->with('success', 'Karyawan deleted successfully.');
        
    }

    public function profile(Request $request)
    {
        $user = $request->user();

        // If you have relation: $user->employee
        // Load it safely (won't break if not defined)
        $user->loadMissing('employee');

        return view('profile.index', compact('user'));
    }

    public function editPassword()
    {
        return view('profile.password');
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed'],
        ], [
            'current_password.current_password' => 'Password lama tidak sesuai.',
            'password.confirmed' => 'Konfirmasi password baru tidak sama.',
        ]);

        // Let the User model mutator hash it
        $user->password = $validated['password'];
        $user->save();

        // Optional: invalidate other sessions
        // auth()->logoutOtherDevices($validated['password']);

        return back()->with('success', 'Password berhasil diperbarui.');
    }
}
