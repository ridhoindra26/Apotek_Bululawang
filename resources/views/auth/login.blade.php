<div class="flex items-center justify-center mt-6">
    <form method="POST" action="{{ route('auth.login') }}" class="w-full max-w-md">
        @csrf

        @if ($errors->any())
            <div class="bg-red-100 border border-red-500 text-red-900 px-4 py-3 rounded relative mb-3">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-3">
            <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
            <input type="text" id="username" name="username" value="{{ old('username') }}" 
                   class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
                   required>
        </div>

        <div class="mb-3">
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <input type="password" id="password" name="password" 
                   class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
                   required>
        </div>

        <button type="submit" class="w-full flex justify-center bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Login
        </button>
    </form>
</div>
