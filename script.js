// Close menus when clicking outside
document.addEventListener("click", (e) => {
  if (!e.target.closest(".dropdown")) {
    destSearch.classList.remove("show");
  }
});

document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll(".delete").forEach(function (btn) {
    btn.addEventListener("click", function (e) {
      e.preventDefault();

      if (!confirm("Are you sure you want to delete this record?")) return;

      let url = this.getAttribute("href"); // e.g. delete.php?id=3&table=staff
      let row = this.closest("tr");

      fetch(url, { method: "GET" })
        .then((response) => response.text())
        .then((data) => {
          if (data.includes("success")) {
            row.remove(); // ✅ remove row visually
          } else {
            alert("❌ Failed to delete: " + data);
          }
        })
        .catch((err) => alert("⚠️ Error: " + err));
    });
  });
});

// Contact Form
const contactForm = document.getElementById("contact-form");
const contactResponse = document.getElementById("contact-response");

if (contactForm && contactResponse) {
  contactForm.addEventListener("submit", async (e) => {
    e.preventDefault(); // STOP page reload immediately

    contactResponse.textContent = "Sending...";
    contactResponse.style.color = "blue";

    const formData = new FormData(contactForm);

    try {
      const response = await fetch("contact_submit.php", {
        method: "POST",
        body: formData,
      });

      const data = await response.json();
      contactResponse.textContent = data.message;
      contactResponse.style.color = data.status === "success" ? "green" : "red";

      if (data.status === "success") contactForm.reset();
    } catch (error) {
      contactResponse.textContent = "An error occurred. Please try again.";
      contactResponse.style.color = "red";
    }
  });
}

