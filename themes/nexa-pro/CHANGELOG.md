# Changelog

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
