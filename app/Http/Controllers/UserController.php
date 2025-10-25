<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Employees;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $q           = trim((string) $request->query('q', ''));
        $role        = $request->query('role');                // null | string
        $employeeId  = $request->query('employee_id');         // null | string/int

        $roles = ['user','admin','superadmin']; // fallback

        // Employees for dropdown
        $employees = Employees::query()
            ->select('id','name')
            ->orderBy('name')
            ->get();

        $users = User::query()
            ->when($q !== '', function ($qr) use ($q) {
                $qr->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                    ->orWhere('username', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->when(!empty($role), fn ($qr) => $qr->where('role', $role))
            ->when(!empty($employeeId), fn ($qr) => $qr->where('id_employee', $employeeId))
            ->orderBy('name')
            ->paginate(10)
            // keep query string on pagination links
            ->appends($request->query());

        return view('users.index', [
            'users'      => $users,
            'roles'      => $roles,
            'employees'  => $employees,
            'q'          => $q,
            'role'       => (string) ($role ?? ''),
            'employeeId' => (string) ($employeeId ?? ''),
        ]);
    }

    public function getUserDataForModal(Request $request, $id = null)
    {
        // Get roles for dropdown
        $roles = ['user', 'admin', 'superadmin'];

        // Get employees for dropdown
        $employees = Employees::query()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        // If editing, fetch user data
        $user = null;
        if ($id) {
            $user = User::find($id);
            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }
        }
        return view('users._account-modal', compact('user', 'roles', 'employees'));        // return response()->json([
        //     'user' => $user,
        //     'roles' => $roles,
        //     'employees' => $employees,
        // ]);
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
            'password'    => ['nullable','string','confirmed'],
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
