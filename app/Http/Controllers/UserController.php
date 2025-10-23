<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $q    = $request->string('q');
        $role = $request->string('role');

        $users = User::query()
            ->when($q, function ($qr) use ($q) {
                $qr->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                      ->orWhere('username', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->when($role, fn($qr) => $qr->where('role', $role))
            ->orderBy('name')
            ->paginate(10)
            ->appends($request->query());

        $roles = ['user','admin','superadmin'];

        return view('users.index', compact('users', 'roles', 'q', 'role'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'username'    => ['required','string','max:50','alpha_dash','unique:users,username'],
            'name'        => ['required','string','max:150'],
            'email'       => ['required','email','max:150','unique:users,email'],
            'password'    => ['required','string','confirmed'],
            'role'        => ['required', Rule::in(['user','admin','superadmin'])],
            'id_employee' => ['nullable','integer','exists:employees,id'],
        ]);

        // Password will be hashed by the model mutator you already have
        User::create($data);

        return redirect()->route('accounts.index')->with('success', 'Account created.');
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'username'    => ['required','string','max:50','alpha_dash', Rule::unique('users','username')->ignore($user->id)],
            'name'        => ['required','string','max:150'],
            'email'       => ['required','email','max:150', Rule::unique('users','email')->ignore($user->id)],
            'password'    => ['nullable','string','min:8','confirmed'],
            'role'        => ['required', Rule::in(['user','admin','superadmin'])],
            'id_employee' => ['nullable','integer','exists:employees,id'],
        ]);

        // If password left blank, keep old
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('accounts.index')->with('success', 'Account updated.');
    }

    public function destroy(User $user)
    {
        $user->delete(); // hard delete (use SoftDeletes if you want archive)
        return redirect()->route('accounts.index')->with('success', 'Account deleted.');
    }
}
