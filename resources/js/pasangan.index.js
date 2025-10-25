// resources/js/pasangan.index.js

document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("editModal");
  const form = document.getElementById("editForm");
  const nameInput = document.getElementById("editName");
  const closeBtn = document.getElementById("editModalCloseBtn");
  const cancelBtn = document.getElementById("editModalCancelBtn");

  const openModal = () => {
    modal.classList.remove("hidden");
  };

  const closeModal = () => {
    modal.classList.add("hidden");
  };

  document.addEventListener("click", (e) => {
    const btn = e.target.closest(".js-edit-pasangan");
    if (!btn) return;

    const id = btn.getAttribute("data-id");
    const name = btn.getAttribute("data-name");

    form.setAttribute("action", `/pasangan/${id}`);
    nameInput.value = name;
    openModal();
  });

  closeBtn?.addEventListener("click", closeModal);
  cancelBtn?.addEventListener("click", closeModal);

  modal?.addEventListener("click", (e) => {
    if (e.target === modal) closeModal();
  });
});
