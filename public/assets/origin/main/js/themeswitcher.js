document.addEventListener('DOMContentLoaded', function () {
  const themeOptions = document.querySelectorAll(
    '.theme-options input[type="checkbox"]'
  );
  const currentTheme = localStorage.getItem('theme');

  function updateTheme(selectedTheme) {
    document.documentElement.setAttribute('data-user-theme', selectedTheme);
    localStorage.setItem('theme', selectedTheme);
  }

  const prefersDarkScheme = window.matchMedia(
    '(prefers-color-scheme: dark)'
  ).matches;
  const defaultTheme = prefersDarkScheme ? 'dark' : 'light';
  const userChoice = currentTheme || defaultTheme;

  document.documentElement.setAttribute('data-user-theme', userChoice);

  themeOptions.forEach((option) => {
    option.addEventListener('change', function () {
      const selectedTheme = this.getAttribute('data-value');
      updateTheme(selectedTheme);

      themeOptions.forEach((opt) => {
        opt.checked = opt.getAttribute('data-value') === selectedTheme;
      });
    });

    option.checked = option.getAttribute('data-value') === userChoice;
  });
});
