<form id="accountForm">
  <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
    <div>
      <label class="block text-sm mb-1">Name</label>
      <input type="text" class="w-full border rounded-lg px-3 py-2" name="name" value="{{ old('name', $user->name ?? '') }}" required>
    </div>
    <div>
      <label class="block text-sm mb-1">Username</label>
      <input type="text" class="w-full border rounded-lg px-3 py-2" name="username" value="{{ old('username', $user->username ?? '') }}" required>
    </div>
    <div>
      <label class="block text-sm mb-1">Email</label>
      <input type="email" class="w-full border rounded-lg px-3 py-2" name="email" value="{{ old('email', $user->email ?? '') }}" required>
    </div>
    <div>
      <label class="block text-sm mb-1">Role</label>
      <select class="w-full border rounded-lg px-3 py-2" name="role" required>
        @foreach($roles as $role)
          <option value="{{ $role }}" {{ (old('role', $user->role ?? '') == $role) ? 'selected' : '' }}>{{ ucfirst($role) }}</option>
        @endforeach
      </select>
    </div>
    <div class="md:col-span-2">
      <label class="block text-sm mb-1">Employee (optional)</label>
      <select name="id_employee" class="w-full border rounded-lg px-3 py-2">
        <option value="">— Select —</option>
        @foreach($employees as $emp)
          <option value="{{ $emp->id }}" @if(old('id_employee', $user->id_employee ?? '') == $emp->id) selected @endif>{{ $emp->name }}</option>
        @endforeach
      </select>
    </div>
    <div class="md:col-span-2">
      <label class="block text-sm mb-1">Password (leave blank to keep current)</label>
      <input type="password" class="w-full border rounded-lg px-3 py-2" name="password">
    </div>
    <div class="md:col-span-2">
      <label class="block text-sm mb-1">Confirm Password</label>
      <input type="password" class="w-full border rounded-lg px-3 py-2" name="password_confirmation">
    </div>
  </div>
</form>
