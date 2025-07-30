import { verifyAccessToken, hasRole, logout } from "./utils/auth.ts";

const hamburger = document.getElementById("hamburger") as HTMLElement;
const menuOverlay = document.getElementById("menu-overlay") as HTMLElement;
const buttonGroup = document.querySelector(".button-group") as HTMLElement;
const menuNav = document.querySelector(".menu-nav") as HTMLElement;

const toggleMenu = () => {
  if (menuOverlay.classList.contains("active")) {
    menuOverlay.classList.add("inactive");

    setTimeout(() => {
      menuOverlay.classList.remove("active", "inactive");
      document.body.style.overflow = "";
    }, 500);
  } else {
    menuOverlay.classList.add("active");
    document.body.style.overflow = "hidden";
  }

  hamburger.classList.toggle("active");
};

hamburger.addEventListener("click", toggleMenu);

// const updateNavigation = () => {


//   const accessToken = verifyAccessToken();

//   if (accessToken) {
//     // User is logged in
//     buttonGroup.innerHTML = `
//       <a href="/logout/" class="btn secondary" id="logout">Log uit</a>
//       <a href="${hasRole("beheerder") ? "/channels/" : "/dashboard/"}" class="btn primary">${accessToken.name}</a>
//     `;

//     menuNav.innerHTML = `
//     <li><a href="./">Home</a></li>
//     <li><a href="/logout/" class="btn secondary" id="logout">Log uit</a></li>
//     <li><a href="${hasRole("beheerder") ? "/channels/" : "/dashboard/"}">${accessToken.name}</a></li>
//     `;

//     const logoutButton = document.getElementById("logout") as HTMLElement;
//     logoutButton.addEventListener("click", () => {
//       logout();
//       location.reload();
//     });
//   } else {
//     // User is not logged in
//     buttonGroup.innerHTML = `
//       <a href="./login/" class="btn secondary">Inloggen</a>
//       <a href="./registreer/" class="btn primary">Registreren</a>
//     `;
//   }
// };

// Update the navigation links on page load

const updateNavigation = () => {
  const accessToken = verifyAccessToken();
  const isBeheerder = hasRole("beheerder");

  if (accessToken) {
    // User is logged in
    buttonGroup.innerHTML = `
      <a href="/pages/logout/" class="btn secondary logout">Log uit</a>
      <a href="${isBeheerder ? "/channels/" : "/dashboard/"}" class="btn primary">${accessToken.name}</a>
    `;

    menuNav.innerHTML = `
      <li><a href="./">Home</a></li>
      <li><a href="/pages/logout/" class="btn secondary logout">Log uit</a></li>
      <li><a href="${isBeheerder ? "/channels/" : "/dashboard/"}">${accessToken.name}</a></li>
    `;

    const logoutButtons = document.querySelectorAll(".logout");
    logoutButtons.forEach((btn) =>
      btn.addEventListener("click", () => {
        logout();
        location.reload();
      })
    );
  } else {
    // User is not logged in
    buttonGroup.innerHTML = `
      <a href="./login/" class="btn secondary">Inloggen</a>
      <a href="./registreer/" class="btn primary">Registreren</a>
    `;
  }
};

updateNavigation();

// Automatisch naar de kanaalpagina als gebruiker ingelogd is
const accessToken = verifyAccessToken();

const loggedInPages = ["channels.html", "detail.html", "addchannel.html", "editchannel.html"];
const publicPages = ["index.html", "", "login.html", "registreer.html"];
const currentPage = window.location.pathname.split("/").pop() || "";
if (accessToken && !loggedInPages.includes(currentPage)) {
  window.location.href = "/pages/channels/channels.html"; // of welk pad jij gebruikt
}
if (!accessToken && !publicPages.includes(currentPage)) {
  window.location.href = "/pages/login/login.html"; // of welk pad jij gebruikt
}

    

// const accessToken = verifyAccessToken();
// if (accessToken && !window.location.pathname.includes("channels.html")) {
//   window.location.href = "/pages/channels/channels.html"; // of welk pad jij gebruikt
// }

