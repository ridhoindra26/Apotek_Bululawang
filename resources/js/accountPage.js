import Swal from "sweetalert2";  // Import SweetAlert2

(function () {
  const createAccountButton = document.getElementById('createAccountButton');
  const editButtons = document.querySelectorAll('.editButton');

  // Function to show SweetAlert modal for Create Account
  function showCreateAccountModal() {
    Swal.fire({
      title: 'Tambah Akun',
      html: document.getElementById('accountForm').innerHTML,  // Dynamically injected modal HTML here
      showCancelButton: true,
      confirmButtonText: 'Create',
      cancelButtonText: 'Cancel',
      reverseButtons: true,
      preConfirm: () => {
        const form = document.getElementById('accountForm');
        const formData = new FormData(form);

        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        return fetch('/accounts', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrfToken,  // Add CSRF token here
          },
          body: formData,
        })
        .then(response => response.json())
        .then(result => {
          if (result.success) {
            Swal.fire({
              title: 'Success!',
              text: 'Account created successfully.',
              icon: 'success',
              confirmButtonText: 'Ok'
            });
          } else {
            Swal.fire({
              title: 'Error!',
              text: result.message || 'Failed to create account.',
              icon: 'error',
              confirmButtonText: 'Ok'
            });
          }
          return result;
        })
        .catch(error => {
          Swal.fire({
            title: 'Error!',
            text: error.message || 'Something went wrong.',
            icon: 'error',
            confirmButtonText: 'Ok'
          });
        });
      }
    });
  }

  // Function to show SweetAlert modal for Edit Account
  function showEditAccountModal(userId) {
    Swal.fire({
      title: 'Edit Akun',
      html: document.getElementById('accountForm').innerHTML,  // Dynamically injected modal HTML here
      showCancelButton: true,
      confirmButtonText: 'Update',
      cancelButtonText: 'Cancel',
      reverseButtons: true,
      preConfirm: () => {
        const form = document.getElementById('accountForm');
        const formData = new FormData(form);
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        return fetch(`/accounts/${userId}`, {
          method: 'PATCH',
          headers: {
            'X-CSRF-TOKEN': csrfToken,  // Add CSRF token here
          },
          body: formData,
        })
        .then(response => response.json())
        .then(result => {
          if (result.success) {
            Swal.fire({
              title: 'Success!',
              text: 'Account updated successfully.',
              icon: 'success',
              confirmButtonText: 'Ok'
            });
          } else {
            Swal.fire({
              title: 'Error!',
              text: result.message || 'Failed to update account.',
              icon: 'error',
              confirmButtonText: 'Ok'
            });
          }
          return result;
        })
        .catch(error => {
          Swal.fire({
            title: 'Error!',
            text: error.message || 'Something went wrong.',
            icon: 'error',
            confirmButtonText: 'Ok'
          });
        });
      }
    });
  }

  // Add event listener to the create account button
  if (createAccountButton) {
    createAccountButton.addEventListener('click', showCreateAccountModal);
  }

  // Add event listeners to the edit buttons
  editButtons.forEach(button => {
    button.addEventListener('click', function () {
      const userId = this.dataset.id;
      showEditAccountModal(userId);
    });
  });
})();
