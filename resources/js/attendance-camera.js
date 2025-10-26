import Swal from "sweetalert2";

document.addEventListener("DOMContentLoaded", () => {
  // Live clock
  const clock = document.getElementById("live-clock");
  const tick = () => {
    const d = new Date();
    const pad = (n) => n.toString().padStart(2, "0");
    clock.textContent = `${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;
  };
  setInterval(tick, 1000);
  tick();

  const cameraInput = document.getElementById("camera-input");

  // Buttons to toggle disabled state during loading
  const ACTION_IDS = [
    "checkin-button",
    "checkout-button",
    "checkin-button-desktop",
    "checkout-button-desktop",
  ];
  const ACTION_BTNS = ACTION_IDS
    .map((id) => document.getElementById(id))
    .filter(Boolean);

  const setButtonsDisabled = (disabled) => {
    ACTION_BTNS.forEach((btn) => {
      btn.disabled = disabled;
      btn.classList.toggle("opacity-60", disabled);
      btn.classList.toggle("pointer-events-none", disabled);
    });
  };

  async function captureAndSubmit(type) {
    return new Promise((resolve) => {
      // Trigger camera
      cameraInput.click();
      cameraInput.onchange = async (e) => {
        const file = e.target.files[0];
        if (!file) return;

        // Preview before upload
        const preview = URL.createObjectURL(file);
        const confirm = await Swal.fire({
          title: `Confirm ${type}?`,
          html: `<p class="text-[#318f8c] mb-1">Wih cakepnya oiiii</p><img src="${preview}" class="rounded-xl max-h-60 object-cover mx-auto mb-3"/>`,
          showCancelButton: true,
          confirmButtonColor: "#318f8c",
          confirmButtonText: `Yes, ${type}`,
          cancelButtonText: "Cancel",
        });

        if (confirm.isConfirmed) {
          // Prepare form data
          const formData = new FormData();
          formData.append("photo", file);
          formData.append("_token", document.querySelector('meta[name="csrf-token"]').content);

          // Optional: timeout protection
          const controller = new AbortController();
          const timeout = setTimeout(() => controller.abort(), 30000); // 30s

          try {
            setButtonsDisabled(true);

            // SHOW LOADING (do NOT await)
            Swal.fire({
              title: "Uploading...",
              html: "Please wait a moment.",
              allowOutsideClick: false,
              allowEscapeKey: false,
              didOpen: () => {
                Swal.showLoading();
              },
            });

            // Optional: timeout protection
            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), 30000); // 30s

            const res = await fetch(`/attendance/${type}`, {
              method: "POST",
              body: formData,
              signal: controller.signal,
            });

            clearTimeout(timeout);
            const data = await res.json().catch(() => ({}));

            // CLOSE LOADING before next dialog
            if (Swal.isLoading()) Swal.close();

            if (res.ok) {
              await Swal.fire({
                icon: "success",
                title: `${type} success`,
                text: data.message ?? "Upload success",
                confirmButtonColor: "#318f8c",
              });
              window.location.reload();
            } else {
              throw new Error(data.message ?? "Upload failed");
            }
          } catch (err) {
            if (Swal.isLoading()) Swal.close();
            await Swal.fire({
              icon: "error",
              title: "Error",
              text: err.name === "AbortError" ? "Request timed out. Please try again." : (err.message || "Unexpected error"),
              confirmButtonColor: "#318f8c",
            });
            window.location.reload();
          } finally {
            setButtonsDisabled(false);
          }
        }

        // Reset input value
        cameraInput.value = "";
        resolve();
      };
    });
  }

  // Bind buttons
  const actions = [
    ["checkin-button", "checkin"],
    ["checkout-button", "checkout"],
    ["checkin-button-desktop", "checkin"],
    ["checkout-button-desktop", "checkout"],
  ];

  actions.forEach(([id, type]) => {
    const btn = document.getElementById(id);
    if (btn) btn.addEventListener("click", () => captureAndSubmit(type));
  });
});
