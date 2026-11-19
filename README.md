# Vivere website

WordPress block theme for vivere.bankssolution.ng (Ann Uramu's Vivere).

- `theme.json`, `parts/`, `templates/`, `patterns/`: the block theme.
- `assets/css/vivere.css`, `assets/css/vivere-nav.css`: styles. `assets/js/vivere-motion.js` (GSAP motion), `assets/js/vivere-nav.js` (island header and full-page menu), `assets/js/vivere.js` (hero slider).
- `inc/build-pages.php`: generates the inner pages. Not loaded by the theme; require it and call `vivere_build_pages()`. It overwrites those pages, so do not re-run it after pages are edited in WordPress.

Install: zip this folder as `vivere/` and upload under Appearance > Themes.
