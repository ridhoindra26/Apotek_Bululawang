@extends('layout.layout')
@section('title','Akun')

@section('content')
<div x-data="accountPage()" class="container-fluid p-6 space-y-6">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-xl font-semibold text-slate-800">Kelola Akun</h1>
      <p class="text-slate-500 text-sm">Cari, tambah, ubah, dan hapus akun pengguna.</p>
    </div>
    <button @click="openCreate()"
            class="px-4 py-2 rounded-xl bg-teal-600 text-white hover:bg-teal-700 shadow">
      + Akun Baru
    </button>
  </div>

  {{-- Alerts --}}
  @if(session('success'))
    <div class="rounded-md bg-green-50 border border-green-200 text-green-700 px-4 py-2">
      {{ session('success') }}
    </div>
  @endif
  @if ($errors->any())
    <div class="rounded-md bg-red-50 border border-red-200 text-red-700 px-4 py-2">
      <ul class="list-disc ml-5">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- Filters --}}
  <form method="GET" action="{{ route('accounts.index') }}" class="grid md:grid-cols-3 gap-3">
    <div>
      <label class="block text-sm text-slate-600 mb-1">Search</label>
      <input type="text" name="q" value="{{ $q }}" placeholder="name/username/email"
             class="w-full border rounded-lg px-3 py-2" />
    </div>
    <div>
      <label class="block text-sm text-slate-600 mb-1">Role</label>
      <select name="role" class="w-full border rounded-lg px-3 py-2">
        <option value="">All</option>
        @foreach($roles as $r)
          <option value="{{ $r }}" @selected($role===$r)>{{ ucfirst($r) }}</option>
        @endforeach
      </select>
    </div>
    <div class="flex items-end">
      <button class="px-4 py-2 rounded-lg bg-slate-700 text-white hover:bg-slate-800">Filter</button>
      <a href="{{ route('accounts.index') }}" class="ml-2 px-4 py-2 rounded-lg border hover:bg-slate-50">Reset</a>
    </div>
  </form>

  {{-- Table --}}
  <div class="overflow-x-auto bg-white rounded-2xl border shadow-sm">
    <table class="min-w-full text-sm">
      <thead class="bg-slate-50 text-slate-600">
        <tr>
          <th class="text-left px-4 py-3">Name</th>
          <th class="text-left px-4 py-3">Username</th>
          <th class="text-left px-4 py-3">Email</th>
          <th class="text-left px-4 py-3">Role</th>
          <th class="text-left px-4 py-3">Employee</th>
          <th class="text-right px-4 py-3">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @forelse ($users as $u)
          <tr>
            <td class="px-4 py-3">{{ $u->name }}</td>
            <td class="px-4 py-3 text-slate-700">{{ $u->username }}</td>
            <td class="px-4 py-3 text-slate-700">{{ $u->email }}</td>
            <td class="px-4 py-3">
              <span class="px-2 py-1 text-xs rounded bg-slate-100">{{ ucfirst($u->role) }}</span>
            </td>
            <td class="px-4 py-3">{{ optional($u->employee)->name ?? '—' }}</td>
            <td class="px-4 py-3 text-right space-x-2">
              <button @click="openEdit({{ $u->toJson() }})"
                      class="px-3 py-1 rounded-lg border hover:bg-slate-50">Edit</button>
              <form class="inline" method="POST" action="{{ route('accounts.destroy', $u) }}"
                    onsubmit="return confirm('Delete this account?')">
                @csrf @method('DELETE')
                <button class="px-3 py-1 rounded-lg bg-rose-600 text-white hover:bg-rose-700">Delete</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">No data.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div>
    {{ $users->links() }}
  </div>

  {{-- Modal --}}
  <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-black/40" @click="close()"></div>
    <div class="relative w-full max-w-lg bg-white rounded-2xl p-6 shadow-xl">
      <h2 class="text-lg font-semibold mb-4" x-text="isEdit ? 'Edit Akun' : 'Tambah Akun'"></h2>

      <form :action="formAction" method="POST" class="space-y-4">
        @csrf
        <template x-if="isEdit">
          <input type="hidden" name="_method" value="PATCH">
        </template>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
          <div>
            <label class="block text-sm mb-1">Name</label>
            <input type="text" class="w-full border rounded-lg px-3 py-2" name="name" x-model="form.name" required>
          </div>
          <div>
            <label class="block text-sm mb-1">Username</label>
            <input type="text" class="w-full border rounded-lg px-3 py-2" name="username" x-model="form.username" required>
          </div>
          <div>
            <label class="block text-sm mb-1">Email</label>
            <input type="email" class="w-full border rounded-lg px-3 py-2" name="email" x-model="form.email" required>
          </div>
          <div>
            <label class="block text-sm mb-1">Role</label>
            <select class="w-full border rounded-lg px-3 py-2" name="role" x-model="form.role" required>
              @foreach($roles as $r)
                <option value="{{ $r }}">{{ ucfirst($r) }}</option>
              @endforeach
            </select>
          </div>
          <div class="md:col-span-2">
            <label class="block text-sm mb-1">Employee (optional)</label>
            <input type="number" class="w-full border rounded-lg px-3 py-2"
                   name="id_employee" x-model="form.id_employee" placeholder="Employee ID">
          </div>
          <div class="md:col-span-2">
            <label class="block text-sm mb-1" x-text="isEdit ? 'Password (leave blank to keep current)' : 'Password'"></label>
            <input :required="!isEdit" type="password" class="w-full border rounded-lg px-3 py-2" name="password">
          </div>
          <div class="md:col-span-2">
            <label class="block text-sm mb-1">Confirm Password</label>
            <input :required="!isEdit" type="password" class="w-full border rounded-lg px-3 py-2" name="password_confirmation">
          </div>
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <button type="button" @click="close()" class="px-4 py-2 rounded-lg border hover:bg-slate-50">Cancel</button>
          <button class="px-4 py-2 rounded-lg bg-teal-600 text-white hover:bg-teal-700" x-text="isEdit ? 'Update' : 'Create'"></button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function accountPage() {
  return {
    showModal: false,
    isEdit: false,
    formAction: '{{ route('accounts.store') }}',
    form: {
      name: '', username: '', email: '', role: 'staff', id_employee: ''
    },
    openCreate() {
      this.isEdit = false;
      this.formAction = '{{ route('accounts.store') }}';
      this.form = { name:'', username:'', email:'', role:'staff', id_employee:'' };
      this.showModal = true;
    },
    openEdit(user) {
      this.isEdit = true;
      this.formAction = '{{ url('/accounts') }}/' + user.id;
      this.form = {
        name: user.name ?? '',
        username: user.username ?? '',
        email: user.email ?? '',
        role: user.role ?? 'staff',
        id_employee: user.id_employee ?? ''
      };
      this.showModal = true;
    },
    close() { this.showModal = false; }
  }
}
</script>
@endsection
