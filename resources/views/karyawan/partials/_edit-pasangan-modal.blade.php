<div id="editModal" class="fixed inset-0 z-50 hidden w-full p-4 overflow-y-auto bg-black/40">
  <div class="mx-auto relative w-full max-w-md">
    <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">

      <!-- Header -->
      <div class="flex items-start justify-between p-4 border-b rounded-t dark:border-gray-600">
        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
          Edit Pasangan
        </h3>
        <button type="button" id="editModalCloseBtn"
                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
          </svg>
        </button>
      </div>

      <!-- Body -->
      <div class="p-6 space-y-6">
        <form id="editForm" method="POST">
          @csrf
          @method('POST')
          <div class="mb-3">
            <label for="editName" class="block text-sm font-medium text-gray-700">Nama Pasangan</label>
            <input type="text" id="editName" name="name"
                   class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm
                          focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
          </div>
          <div class="mb-3">
            <label for="editIndex" class="block text-sm font-medium text-gray-700">Urutan (Index)</label>
            <select id="editIndex" name="index"
                    class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm
                          focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
              @foreach ($indexChoices as $i)
                <option value="{{ $i }}">{{ $i }}</option>
              @endforeach
            </select>
          </div>

          <div class="flex gap-2">
            <button type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
              Simpan
            </button>
            <button type="button" id="editModalCancelBtn"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
              Batal
            </button>
          </div>
        </form>
      </div>

    </div>
  </div>
</div>
