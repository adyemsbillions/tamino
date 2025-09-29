document.addEventListener("DOMContentLoaded", () => {
  // User type selection
  const userTypeBtns = document.querySelectorAll(".user-type-btn");
  const formSections = document.querySelectorAll(".form-section");
  const errorMessageDiv = document.getElementById("errorMessage");

  userTypeBtns.forEach((btn) => {
    btn.addEventListener("click", () => {
      // Remove active class from all buttons and forms
      userTypeBtns.forEach((b) => b.classList.remove("active"));
      formSections.forEach((section) => section.classList.remove("active"));

      // Add active class to clicked button and corresponding form
      btn.classList.add("active");
      const targetForm = document.getElementById(btn.dataset.type + "Form");
      if (targetForm) {
        targetForm.classList.add("active");
      }
      errorMessageDiv.style.display = "none"; // Clear error on form switch
    });
  });

  // Form submissions
  document.querySelectorAll("form").forEach((form) => {
    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      errorMessageDiv.style.display = "none"; // Clear previous errors
      const userType = document.querySelector(".user-type-btn.active").dataset
        .type;
      const formData = new FormData(form);
      formData.append(
        "action",
        userType === "staff-signup" ? "signup" : "login"
      );
      formData.append(
        "userType",
        userType === "staff-signup" ? "staff" : userType
      );

      // For Host, hardcode hostType as podcast
      if (userType === "host") {
        formData.append("hostType", "podcast");
      }

      try {
        const response = await fetch("login.php", {
          method: "POST",
          body: formData,
        });
        const result = await response.json();

        if (result.success) {
          localStorage.setItem(
            "userType",
            userType === "staff-signup" ? "staff" : userType
          );
          window.location.href = result.data.redirect;
          console.log(
            `[v0] ${userType} ${formData.get(
              "action"
            )} successful, redirecting to ${result.data.redirect}`
          );
        } else {
          errorMessageDiv.textContent = result.message;
          errorMessageDiv.style.display = "block";
        }
      } catch (error) {
        console.error("Error:", error);
        errorMessageDiv.textContent = "An error occurred. Please try again.";
        errorMessageDiv.style.display = "block";
      }
    });
  });
});
