// Mobile Navigation Toggle
const hamburger = document.getElementById("hamburger")
const navMenu = document.getElementById("navMenu")

if (hamburger && navMenu) {
  hamburger.addEventListener("click", () => {
    hamburger.classList.toggle("active")
    navMenu.classList.toggle("active")
  })

  // Close mobile menu when clicking on a link
  document.querySelectorAll(".nav-link").forEach((link) => {
    link.addEventListener("click", () => {
      hamburger.classList.remove("active")
      navMenu.classList.remove("active")
    })
  })
}

// Smooth scrolling for navigation links
document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
  anchor.addEventListener("click", function (e) {
    e.preventDefault()
    const target = document.querySelector(this.getAttribute("href"))
    if (target) {
      const offsetTop = target.offsetTop - 70 // Account for fixed navbar
      window.scrollTo({
        top: offsetTop,
        behavior: "smooth",
      })
    }
  })
})

// Navbar background on scroll
window.addEventListener("scroll", () => {
  const navbar = document.querySelector(".navbar")
  if (window.scrollY > 100) {
    navbar.style.background = "rgba(26, 26, 26, 0.98)"
    navbar.style.boxShadow = "0 2px 20px rgba(0, 0, 0, 0.3)"
  } else {
    navbar.style.background = "rgba(26, 26, 26, 0.95)"
    navbar.style.boxShadow = "none"
  }
})

// Hero buttons functionality
const primaryBtn = document.querySelector(".hero-buttons .btn-primary")
const secondaryBtn = document.querySelector(".hero-buttons .btn-secondary")

if (primaryBtn) {
  primaryBtn.addEventListener("click", () => {
    document.querySelector("#contact").scrollIntoView({
      behavior: "smooth",
    })
  })
}

if (secondaryBtn) {
  secondaryBtn.addEventListener("click", () => {
    // This would typically open a video modal or redirect to a demo reel
    alert("Demo reel coming soon! Contact us for a preview of our work.")
  })
}

// Academy enrollment button
const academyBtn = document.querySelector(".academy .btn-pink")
if (academyBtn) {
  academyBtn.addEventListener("click", () => {
    alert("Academy enrollment opening soon! Contact us to be notified when applications open.")
  })
}

// Contact form submission
const contactForm = document.getElementById("contactForm")
if (contactForm) {
  contactForm.addEventListener("submit", (e) => {
    e.preventDefault()

    // Get form data
    const name = e.target.querySelector('input[type="text"]').value
    const email = e.target.querySelector('input[type="email"]').value
    const message = e.target.querySelector("textarea").value

    // Basic validation
    if (!name || !email || !message) {
      alert("Please fill in all fields.")
      return
    }

    if (!isValidEmail(email)) {
      alert("Please enter a valid email address.")
      return
    }

    // Simulate form submission
    alert("Thank you for your message! We'll get back to you within 24 hours.")
    e.target.reset()
  })
}

// Email validation helper
function isValidEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(email)
}

// Service card hover effects
document.querySelectorAll(".service-card").forEach((card) => {
  card.addEventListener("mouseenter", () => {
    card.style.transform = "translateY(-8px)"
  })

  card.addEventListener("mouseleave", () => {
    card.style.transform = "translateY(0)"
  })
})

// Studio card hover effects
document.querySelectorAll(".studio-card").forEach((card) => {
  card.addEventListener("mouseenter", () => {
    const img = card.querySelector("img")
    if (img) {
      img.style.transform = "scale(1.05)"
    }
  })

  card.addEventListener("mouseleave", () => {
    const img = card.querySelector("img")
    if (img) {
      img.style.transform = "scale(1)"
    }
  })
})

// Intersection Observer for animations
const observerOptions = {
  threshold: 0.1,
  rootMargin: "0px 0px -50px 0px",
}

const observer = new IntersectionObserver((entries) => {
  entries.forEach((entry) => {
    if (entry.isIntersecting) {
      entry.target.style.opacity = "1"
      entry.target.style.transform = "translateY(0)"
    }
  })
}, observerOptions)

// Observe elements for animation
document.querySelectorAll(".service-card, .academy-content, .studio-card").forEach((el) => {
  el.style.opacity = "0"
  el.style.transform = "translateY(30px)"
  el.style.transition = "opacity 0.6s ease, transform 0.6s ease"
  observer.observe(el)
})

// Add mobile menu styles dynamically
const style = document.createElement("style")
style.textContent = `
    @media (max-width: 768px) {
        .nav-menu {
            position: fixed;
            left: -100%;
            top: 64px;
            flex-direction: column;
            background-color: rgba(26, 26, 26, 0.98);
            width: 100%;
            text-align: center;
            transition: 0.3s;
            box-shadow: 0 10px 27px rgba(0, 0, 0, 0.3);
            padding: 2rem 0;
            backdrop-filter: blur(10px);
        }

        .nav-menu.active {
            left: 0;
        }

        .nav-menu .nav-link {
            display: block;
            margin: 1rem 0;
            padding: 0.5rem;
        }

        .hamburger.active span:nth-child(2) {
            opacity: 0;
        }

        .hamburger.active span:nth-child(1) {
            transform: translateY(8px) rotate(45deg);
        }

        .hamburger.active span:nth-child(3) {
            transform: translateY(-8px) rotate(-45deg);
        }
    }
`
document.head.appendChild(style)

// Parallax effect for hero section
window.addEventListener("scroll", () => {
  const scrolled = window.pageYOffset
  const heroImg = document.querySelector(".hero-bg img")
  if (heroImg) {
    heroImg.style.transform = `translateY(${scrolled * 0.5}px)`
  }
})

// Console log for debugging
console.log("Tamino ETV website loaded successfully!")
