// resources/js/cabang.index.js

document.addEventListener("DOMContentLoaded", () => {
  const modalEl = document.getElementById("editModal");
  const formEl = document.getElementById("editForm");
  const nameEl = document.getElementById("editName");
  const closeBtn = document.getElementById("editModalCloseBtn");
  const cancelBtn = document.getElementById("editModalCancelBtn");

  const openModal = () => {
    modalEl.classList.remove("hidden");
    modalEl.setAttribute("aria-hidden", "false");
  };

  const closeModal = () => {
    modalEl.classList.add("hidden");
    modalEl.setAttribute("aria-hidden", "true");
  };

  document.addEventListener("click", (e) => {
    const btn = e.target.closest(".js-edit-cabang");
    if (!btn) return;

    const id = btn.getAttribute("data-id");
    const name = btn.getAttribute("data-name") || "";

    formEl.setAttribute("action", `/cabang/${id}`);
    nameEl.value = name;

    openModal();
  });

  closeBtn?.addEventListener("click", closeModal);
  cancelBtn?.addEventListener("click", closeModal);

  modalEl?.addEventListener("click", (e) => {
    if (e.target === modalEl) closeModal();
  });
});
