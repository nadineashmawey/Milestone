import type { ReactNode } from "react";

// Same stroke icons as App.tsx, limited to the ones the public pages use
const paths: Record<string, ReactNode> = {
  grid: <><rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/></>,
  folder: <path d="M3 7.5V18a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7l-2-2H5a2 2 0 0 0-2 2v1.5Z"/>,
  users: <><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></>,
  check: <><path d="M21 11.5V12a9 9 0 1 1-5.34-8.23"/><path d="m21 4-9 9-3-3"/></>,
  arrow: <path d="M5 12h14M13 6l6 6-6 6"/>,
  back: <path d="M19 12H5M11 6l-6 6 6 6"/>,
  search: <><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></>,
  message: <><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4Z"/><path d="M8 9h8M8 13h5"/></>,
  alert: <><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16.5v.01"/></>,
};

export function HomeIcon({ name, size = 20 }: { name: string; size?: number }) {
  return <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">{paths[name]}</svg>;
}
