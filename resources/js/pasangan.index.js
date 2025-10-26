// resources/js/pasangan.index.js

document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("editModal");
  const form = document.getElementById("editForm");
  const nameInput = document.getElementById("editName");
  const indexSelect = document.getElementById("editIndex");
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
    const index = btn.getAttribute("data-index") || "";

    form.setAttribute("action", `/pasangan/${id}`);
    if (nameInput) nameInput.value = name;
    if (indexSelect) indexSelect.value = String(index);
    openModal();
  });

  closeBtn?.addEventListener("click", closeModal);
  cancelBtn?.addEventListener("click", closeModal);

  modal?.addEventListener("click", (e) => {
    if (e.target === modal) closeModal();
  });
});
