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
          html: `<img src="${preview}" class="rounded-xl max-h-60 object-cover mx-auto mb-3"/>`,
          showCancelButton: true,
          confirmButtonColor: "#318f8c",
          confirmButtonText: `Yes, ${type}`,
          cancelButtonText: "Cancel",
        });

        if (confirm.isConfirmed) {
          // Send via fetch
          const formData = new FormData();
          formData.append("photo", file);
          formData.append("_token", document.querySelector('meta[name="csrf-token"]').content);

          try {
          console.log("FormData:", Object.fromEntries(formData));
            
            const res = await fetch(`/attendance/${type}`, {
              method: "POST",
              body: formData,
            });

            const data = await res.json().catch(() => ({}));
            if (res.ok) {
              console.log(data);
              
              await Swal.fire({
                icon: "success",
                title: `${type} success`,
                text: data.ok ?? "Photo submitted successfully.",
                confirmButtonColor: "#318f8c",
              }).then(() => window.location.reload());
            } else {
              console.error("Error Response:", data);
              throw new Error(data.message ?? "Upload failed");
            }
          } catch (err) {
            console.log(err);
            
            Swal.fire({
              icon: "error",
              title: "Error",
              text: err.message,
              confirmButtonColor: "#318f8c",
            }).then(() => window.location.reload());
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
