<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\GreetingTypes;
use App\Models\Greetings;

class GreetingController extends Controller
{
    public function index()
    {
        $types = GreetingTypes::orderBy('name')->get();

        $greetings = Greetings::with('type')
            ->orderByDesc('id')
            ->paginate(20);

        return view('greetings.index', compact('types', 'greetings'));
    }

    public function storeGreeting(Request $request)
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'id_type' => ['required', 'exists:greeting_types,id'],
        ]);

        Greetings::create($data);

        return redirect()
            ->route('greetings.index')
            ->with('success', 'Greeting berhasil dibuat.');
    }

    public function updateGreeting(Request $request, Greetings $greeting)
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'id_type' => ['required', 'exists:greeting_types,id'],
        ]);

        $greeting->update($data);

        return redirect()
            ->route('greetings.index')
            ->with('success', 'Greeting berhasil diupdate.');
    }

    public function destroyGreeting(Greetings $greeting)
    {
        $greeting->delete();

        return redirect()
            ->route('greetings.index')
            ->with('success', 'Greeting berhasil dihapus.');
    }

    // ───────── Greeting Types CRUD ─────────

    public function storeType(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:greeting_types,name'],
        ]);

        GreetingTypes::create($data);

        return redirect()
            ->route('greetings.index')
            ->with('success', 'Greeting type berhasil dibuat.');
    }

    public function updateType(Request $request, GreetingTypes $greetingType)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:greeting_types,name,' . $greetingType->id],
        ]);

        $greetingType->update($data);

        return redirect()
            ->route('greetings.index')
            ->with('success', 'Greeting type berhasil diupdate.');
    }

    public function destroyType(GreetingTypes $greetingType)
    {
        if ($greetingType->greetings()->exists()) {
            return redirect()
                ->route('greetings.index')
                ->with('error', 'Tidak bisa menghapus, masih ada greetings yang memakai type ini.');
        }

        $greetingType->delete();

        return redirect()
            ->route('greetings.index')
            ->with('success', 'Greeting type berhasil dihapus.');
    }
}
