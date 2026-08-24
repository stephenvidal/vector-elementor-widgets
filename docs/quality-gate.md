# Quality Gate

Definition of done for a widget, and the verification protocol. **Data tests
confirm logic, NOT rendering.** A widget that passes all unit tests can still
throw a fatal in the editor.

## Automated gates (must be green)

```bash
composer run all   # lint + stan + test
```

- `lint` — PHPCS (WordPress Coding Standards).
- `stan` — PHPStan level 6.
- `test` — PHPUnit (Brain Monkey unit tests, 23 passing).

Plus the lexical guards in `tests/Unit/Elementor/`:
- `ControlSectionIdsTest` — no duplicate control/section ids (Elementor
  silently drops redeclared controls).
- `WidgetSettingsWhitelistTest` — every consumed setting is on the whitelist;
  no `_content_template()` override; scoped classes present.

## Per-widget contract checklist

A widget is complete only when ALL pass:

- [ ] Appears under "Vector Widgets" category
- [ ] Editor controls function
- [ ] Live preview updates appropriately
- [ ] Frontend rendering matches editor
- [ ] No PHP warnings/notices
- [ ] No console errors
- [ ] Multiple instances work
- [ ] Desktop / tablet / mobile layout works
- [ ] Keyboard behavior works where applicable
- [ ] Output is escaped appropriately
- [ ] CSS is scoped (no generic selectors, no leaks)
- [ ] Assets load only when the widget is on the page
- [ ] Theme conflicts minimized
- [ ] Works after page reload
- [ ] Works outside Elementor editing mode

## Real-HTTP verification (mandatory)

1. `docker exec vyg-dev-wp apachectl -k graceful` (clear OPcache).
2. `curl -b /tmp/wp-cookies.txt 'http://127.0.0.1:8020/wp-admin/post.php?post={N}&action=elementor'`
   — must return **HTTP 200** with no fatal markers in the body:
   - `grep -c "fatal"` → 0
   - `grep -c "controls-stack.php"` → 0
   - `grep -c "Uncaught TypeError"` → 0
3. Grep for the widget slug → ≥ 1.
4. `docker exec vyg-dev-wp cat /var/www/html/wp-content/debug.log` → no PHP
   fatals.
5. Visual verification in the browser: the widget renders with real dimensions,
   correct scoped classes, and correct content — not just that the file exists.
