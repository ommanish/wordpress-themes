# Nexa Pro Release Checklist

Use this checklist before tagging or shipping a Nexa Pro release candidate or
final release.

## Clean Install And Activation

- Install the packaged ZIP on a clean WordPress site.
- Activate Nexa Pro without PHP warnings or notices.
- Confirm required theme files are present.
- Confirm no generated ZIP files are committed.

## Homepage And Menus

- Assign a Primary Menu.
- Assign a Footer Menu if footer navigation is needed.
- Configure homepage settings and set a static front page.
- Disable and re-enable controlled homepage sections.
- Confirm disabled section wrappers and anchors are absent.
- Confirm section ordering preserves hidden section positions.

## Responsive Checks

Manually check at:

- 320px
- 375px
- 480px
- 768px
- 960px
- 1280px
- 1440px

Confirm:

- No horizontal overflow.
- Header branding and menu controls do not overlap.
- Buttons remain tappable.
- Cards, process steps, media, footer columns, modal content, and legal links wrap cleanly.
- Admin tabs, repeaters, Homepage Order, Tools, import preview, preset cards, color controls, and typography controls remain usable.

## Accessibility Checks

- Keyboard-test header navigation, mobile menu, links, buttons, forms, and footer links.
- Confirm Escape closes the mobile menu and schedule modal.
- Confirm modal focus moves in, remains inside while open, and returns to the trigger.
- Confirm focus indicators are visible on light and dark surfaces.
- Confirm one meaningful H1 per rendered page.
- Confirm form labels and admin control labels are connected.
- Run a screen-reader smoke test for landmarks, headings, menu state, dialog name, and status announcements.
- Check color contrast for default surfaces, muted text, buttons, links, footer content, and modal controls.
- Test with reduced motion enabled.

## Settings And Media

- Save Global Design colors and typography.
- Select, replace, and remove all media controls.
- Add, remove, undo, and reorder repeater rows.
- Reorder homepage sections and reset order.
- Export settings JSON.
- Import valid settings JSON through preview and confirmation.
- Reject wrong-product, unsupported-schema, malformed, and oversized JSON files.
- Apply each starter preset.
- Reset settings to defaults.
- Restore rollback.

## Performance And Security

- Run `scripts/validate-theme.sh`.
- Run `scripts/package-nexa-pro.sh`.
- Run `scripts/package-nexa-pro-core.sh` when shipping the companion plugin.
- Confirm frontend loads no external fonts or third-party scripts.
- Confirm admin assets load only on Appearance > Nexa Pro.
- Confirm no console errors.
- Confirm no PHP warnings or notices with debugging enabled.
- Confirm import uploads do not expose filesystem paths in notices.

## Packaging

- Confirm ZIP root is `nexa-pro/`.
- Confirm plugin ZIP root is `nexa-pro-core/` when shipping Nexa Pro Core.
- Install-test the generated ZIP from `dist/`.
- Install-test the generated plugin ZIP from `dist/` when applicable.
- Confirm `style.css`, `functions.php`, `index.php`, `header.php`, `footer.php`, `theme.json`, assets, README, CHANGELOG, LICENSE, and CREDITS are included.
- Confirm development-only files, caches, logs, source maps, hidden Git files, and generated artifacts are excluded.

## Screenshot

- Add `themes/nexa-pro/screenshot.png` before final release.
- Recommended dimensions: `1200 x 900`.
- Use only approved, non-private, non-copyrighted content and imagery.
- Confirm the screenshot accurately represents the default theme.

## Final Git Readiness

- Confirm version consistency across `style.css`, `functions.php`, changelog, docs, and package filename.
- Confirm changelog reflects actual verified work.
- Confirm credits and license are accurate.
- Confirm manual browser checks are complete.
- Confirm final release tag name and target branch.
