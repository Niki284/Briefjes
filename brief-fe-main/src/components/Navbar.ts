// components/Navbar.ts
import { NAV_ITEMS } from "../pages/navigatie";

// eslint-disable-next-line @typescript-eslint/explicit-module-boundary-types
export function Navbar() {
  const nav = document.createElement("nav");
  const ul = document.createElement("ul");

  NAV_ITEMS.forEach((item) => {
    const li = document.createElement("li");
    const a = document.createElement("a");
    a.href = item.path;
    a.textContent = item.label;
    li.appendChild(a);
    ul.appendChild(li);
  });

  nav.appendChild(ul);
  nav.className = "navbar";
  return nav;
}
