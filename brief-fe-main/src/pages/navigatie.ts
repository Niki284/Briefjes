// pages/navigatie.ts

export interface NavItem {
  path: string;
  label: string;
  icon?: string; // optioneel
}

export const NAV_ITEMS: NavItem[] = [
  { path: "/", label: "Home" },
  { path: "/login", label: "Login" },
  { path: "/register", label: "Register" },
  { path: "/dashboard", label: "Dashboard" },
];
export const getNavItems = (isAuthenticated: boolean): NavItem[] => {
  if (isAuthenticated) {
    return [
      ...NAV_ITEMS,
      { path: "/profile", label: "Profile" },
      { path: "/settings", label: "Settings" },
    ];
  }
  return NAV_ITEMS;
};