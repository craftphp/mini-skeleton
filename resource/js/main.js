// Add some interactive animations
document.addEventListener("DOMContentLoaded", function () {
  // Theme toggle
  const themeToggle = document.getElementById("themeToggle");
  const root = document.documentElement;
  function setTheme(theme) {
    root.setAttribute("data-theme", theme);
    localStorage.setItem("theme", theme);
    themeToggle.textContent = theme === "dark" ? "☀️" : "🌙";
  }
  // Load theme from localStorage
  const savedTheme = localStorage.getItem("theme") || "light";
  setTheme(savedTheme);
  themeToggle.addEventListener("click", function () {
    const current =
      root.getAttribute("data-theme") === "dark" ? "light" : "dark";
    setTheme(current);
  });
  // Card animation
  const cards = document.querySelectorAll(".card");
  cards.forEach((card, index) => {
    card.style.animationDelay = `${index * 0.1}s`;
  });
  // Add click effect to status items
  const statusItems = document.querySelectorAll(".status-item");
  statusItems.forEach((item) => {
    item.addEventListener("click", function () {
      this.style.transform = "scale(0.98)";
      setTimeout(() => {
        this.style.transform = "scale(1)";
      }, 150);
    });
  });
  // Simulate loading for demonstration
  setTimeout(() => {
    const loadingElements = document.querySelectorAll(".loading");
    loadingElements.forEach((el) => {
      el.style.display = "none";
    });
  }, 2000);
});
