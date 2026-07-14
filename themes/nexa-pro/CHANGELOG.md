# Changelog

## 1.1.0-beta.1 - 2026-07-13

Beta release preparation for the Nexa Pro component builder platform.

### Added

- Nexa Pro Core migration preview, apply, rollback, and migration-state tracking
- Explicit legacy/builder compatibility mode
- Safe builder-mode frontend rendering gate
- Component platform import/export with schema version `2`
- Preview-first import flow with conflict handling
- Reusable ID remapping and duplicate-anchor protection during imports
- Migration and transfer validation scripts
- Nexa Pro Core packaging script
- Nexa Pro 1.1 beta testing guide

### Changed

- Theme version updated to `1.1.0-beta.1`.
- Nexa Pro Core version updated to `0.5.0-beta.1`.
- Component storage schema remains `1`.

### Beta Notes

- This is not a final stable 1.1.0 release.
- Use on staging or test sites only.
- Migration does not run automatically and never deletes legacy theme options.
- Rollback restores the latest migration backup only.

## 1.0.0 - 2026-07-12

Initial release preparation for local production testing, customer beta testing,
marketplace review preparation, and installable ZIP packaging.

### Major Feature Groups

- Parent theme scaffold and WordPress template hierarchy
- Modular homepage section architecture
- Theme options stored in `nexa_pro_options`
- Header branding, layout, sticky, transparent, and CTA controls
- Homepage visibility, content, repeaters, media, and section ordering controls
- Global color and typography settings
- Footer, contact actions, schedule modal, legal links, and social links
- Settings export, validated import, starter presets, reset, and rollback tools

### Accessibility Improvements

- Confirmed skip-link path and main-content focus target
- Retained semantic landmarks and logical homepage heading structure
- Retained keyboard mobile navigation state handling
- Retained schedule dialog focus management and focus return
- Added stronger reduced-motion handling after component styles
- Added focus fallback for browsers without full `:focus-visible` support

### Responsive And Performance Improvements

- Added small-screen branding and modal sizing safeguards
- Added admin narrow-screen table, repeater, order, and tools layout safeguards
- Kept frontend assets dependency-free and limited to the theme stylesheet and script
- Kept admin assets scoped to Appearance > Nexa Pro
- Added validation and packaging scripts for repeatable release checks

### Security Hardening

- Reviewed nonce and capability coverage for settings and Tools actions
- Reviewed import file-size, product, schema, and JSON validation behavior
- Reviewed attachment-ID validation and media-reference clearing
- Added release validation scans for unsafe functions and direct template option reads

### Known Limitations

- Browser, keyboard, screen-reader smoke, contrast, LocalWP install, and ZIP
  install tests should be repeated before marketplace submission.
- Companion plugin and child theme packaging are reserved for later work.
