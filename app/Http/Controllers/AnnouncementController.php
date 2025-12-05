<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Announcement;
use App\Models\Employees;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::with('employees')
            ->orderByDesc('date_from')
            ->paginate(10);

        return view('announcements.index', compact('announcements'));
    }

    public function create()
    {
        $employees = Employees::orderBy('name')->get(); // adjust column name
        return view('announcements.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'          => ['required', 'string', 'max:255'],
            'body'           => ['required', 'string'],
            'date_from'      => ['required', 'date'],
            'date_to'        => ['nullable', 'date', 'after_or_equal:date_from'],
            'employee_ids'   => ['required', 'array'],
            'employee_ids.*' => ['integer', 'exists:employees,id'],
        ]);

        $announcement = Announcement::create([
            'title'      => $data['title'],
            'body'       => $data['body'],
            'date_from'  => $data['date_from'],
            'date_to'    => $data['date_to'] ?? null,
            'created_by' => auth()->id(),
        ]);

        $announcement->employees()->sync($data['employee_ids']);

        return redirect()
            ->route('announcements.index')
            ->with('success', 'Pengumuman berhasil dibuat.');
    }

    public function edit(String $id)
    {
        $employees      = Employees::orderBy('name')->get();
        $announcement   = Announcement::find($id);
        $selectedIds    = $announcement->employees->pluck('id')->toArray();

        return view('announcements.edit', compact('announcement', 'employees', 'selectedIds'));
    }

    public function update(Request $request, String $id)
    {
        $data = $request->validate([
            'title'          => ['required', 'string', 'max:255'],
            'body'           => ['required', 'string'],
            'date_from'      => ['required', 'date'],
            'date_to'        => ['nullable', 'date', 'after_or_equal:date_from'],
            'employee_ids'   => ['required', 'array'],
            'employee_ids.*' => ['integer', 'exists:employees,id'],
        ]);
        $announcement = Announcement::find($id);

        $announcement->update([
            'title'     => $data['title'],
            'body'      => $data['body'],
            'date_from' => $data['date_from'],
            'date_to'   => $data['date_to'] ?? null,
        ]);

        // dd($announcement->id);

        $announcement->employees()->sync($data['employee_ids']);

        return redirect()
            ->route('announcements.index')
            ->with('success', 'Pengumuman berhasil diupdate.');
    }

    public function destroy(String $id)
    {
        $announcement = Announcement::find($id);
        $announcement->delete();

        return redirect()
            ->route('announcements.index')
            ->with('success', 'Pengumuman berhasil dihapus.');
    }
}
