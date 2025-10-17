import Swal from "sweetalert2";

function runClock() {
  const el = document.getElementById("live-clock");
  if (!el) return;
  const pad = (n) => n.toString().padStart(2, "0");
  const tick = () => {
    const d = new Date();
    el.textContent = `${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;
  };
  tick();
  setInterval(tick, 1000);
}

function bindButton(id, title, success) {
  const btn = document.getElementById(id);
  if (!btn) return;
  btn.addEventListener("click", async () => {
    const res = await Swal.fire({
      title,
      text: "This is a dummy action.",
      icon: "question",
      showCancelButton: true,
      confirmButtonColor: "#318f8c",
      confirmButtonText: "Confirm",
      cancelButtonText: "Cancel",
      reverseButtons: true,
    });
    if (res.isConfirmed) {
      Swal.fire({ icon: "success", title: success, confirmButtonColor: "#318f8c" });
    }
  });
}

document.addEventListener("DOMContentLoaded", () => {
  runClock();

  // Mobile action bar buttons
  bindButton("checkin-button", "Check In now?", "Checked In (dummy)");
  bindButton("checkout-button", "Check Out now?", "Checked Out (dummy)");

  // Desktop buttons
  bindButton("checkin-button-desktop", "Check In now?", "Checked In (dummy)");
  bindButton("checkout-button-desktop", "Check Out now?", "Checked Out (dummy)");
});
