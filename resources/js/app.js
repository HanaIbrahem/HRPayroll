import './bootstrap';

// --- Theme core ---
function getTheme() {
  return document.documentElement.getAttribute('data-theme') || 'corporate';
}
function setTheme(t) {
  document.documentElement.setAttribute('data-theme', t);
  localStorage.setItem('theme', t);
}
function updateThemeIcon() {
  const t = getTheme();
  const sun = document.getElementById('sun-icon');
  const moon = document.getElementById('moon-icon');
  if (!sun || !moon) return;

  // show sun on light, moon on dark
  const isDark = t === 'business';
  sun.classList.toggle('hidden', isDark);
  moon.classList.toggle('hidden', !isDark);
}
function bindThemeButton() {
  const btn = document.getElementById('theme-toggle-btn');
  if (!btn || btn.dataset.bound === '1') return; // avoid double-binding across SPA navs
  btn.dataset.bound = '1';
  btn.addEventListener('click', () => {
    setTheme(getTheme() === 'corporate' ? 'business' : 'corporate');
    updateThemeIcon();
  });
}
function applySavedTheme() {
  const saved = localStorage.getItem('theme') || 'corporate';
  setTheme(saved);
}

function initThemeUI() {
  applySavedTheme();
  bindThemeButton();
  updateThemeIcon();
}

// First load
document.addEventListener('DOMContentLoaded', initThemeUI);

// Re-run after Livewire SPA navigations
document.addEventListener('livewire:navigated', initThemeUI);
