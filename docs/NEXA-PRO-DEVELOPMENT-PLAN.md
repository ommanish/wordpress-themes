# Nexa Pro Development Plan

## 1. Repository Architecture

Nexa Pro will be built as a clean commercial WordPress theme project inside this monorepo, with clear boundaries between the parent theme, companion plugin, child theme, shared tooling, tests, documentation, and release scripts.

Primary structure:

```text
wordpress-themes/
├── themes/
│   └── nexa-pro/
├── plugins/
│   └── nexa-pro-core/
├── child-themes/
│   └── nexa-pro-child/
├── shared/
├── tests/
├── docs/
└── scripts/
```

Architecture rules:

- `themes/nexa-pro` contains only theme presentation, templates, design tokens, theme options, patterns, assets, and theme-specific helpers.
- `plugins/nexa-pro-core` contains plugin-territory functionality such as custom post types, custom fields, reusable content helpers, and optional blocks or shortcodes.
- `child-themes/nexa-pro-child` contains a minimal commercial-ready child theme scaffold for customer customization.
- `shared` may contain repo-level tooling, standards configuration, reusable test fixtures, release helpers, or documentation assets.
- `tests` contains automated tests for theme, plugin, accessibility, packaging, and end-to-end behavior.
- `docs` contains product, architecture, setup, QA, accessibility, packaging, and release documentation.
- `scripts` contains local validation, packaging, release, and development helper scripts.

Branching model:

- `main`: stable release-ready code.
- `develop`: integration branch.
- `feature/*`: new functionality.
- `fix/*`: bug fixes.
- `release/*`: release preparation.

Core constraints:

- Build Nexa Pro from scratch.
- Do not reuse or recreate previous theme implementations.
- Do not use any legacy runtime file.
- Do not include the forbidden legacy brand, legacy function prefix, legacy runtime identifier, example domain, or temporary production-like URLs listed in `AGENTS.md`.
- Use PHP prefix `nexa_pro_`.
- Use text domain `nexa-pro`.
- Use option name `nexa_pro_options`.
- Support PHP 7.4 and newer.
- Follow WordPress Coding Standards.
- Create a local build and complete local validation before any push to git.

## 2. Theme Architecture

Theme name: Nexa Pro

Theme slug: `nexa-pro`

Theme location:

```text
themes/nexa-pro/
```

Target structure:

```text
themes/nexa-pro/
├── assets/
│   ├── css/
│   ├── js/
│   ├── images/
│   └── src/
├── inc/
│   ├── setup.php
│   ├── assets.php
│   ├── defaults.php
│   ├── options.php
│   ├── sanitization.php
│   ├── template-functions.php
│   ├── icons.php
│   └── admin/
├── template-parts/
│   └── sections/
├── patterns/
├── languages/
├── docs/
├── functions.php
├── style.css
├── theme.json
├── header.php
├── footer.php
├── front-page.php
├── index.php
├── page.php
├── single.php
├── archive.php
├── search.php
└── 404.php
```

Theme responsibilities:

- Register theme supports.
- Load the `nexa-pro` text domain.
- Register menus.
- Register image sizes.
- Enqueue front-end, editor, and admin assets.
- Define default options.
- Sanitize and validate option values.
- Render templates and reusable template parts.
- Provide accessible header, navigation, footer, content loops, pagination, forms, and skip links.
- Provide homepage sections controlled by theme options and/or block patterns.
- Provide `theme.json` design settings for editor consistency.
- Provide patterns for common page sections.
- Avoid plugin-territory functionality.

Theme boot strategy:

- `functions.php` remains thin.
- Core setup is split into files under `inc/`.
- Every included file has a narrow responsibility.
- Global functions use the `nexa_pro_` prefix.
- Template helpers escape output at render time.
- Options are sanitized on save and escaped on output.
- Admin actions use capability checks and nonces.
- JavaScript enhances functionality but is not required for basic admin saving.

Proposed include responsibilities:

- `inc/setup.php`: theme supports, menus, image sizes, editor setup, text domain.
- `inc/assets.php`: front-end and editor asset registration/enqueueing.
- `inc/defaults.php`: default option values and reusable default arrays.
- `inc/options.php`: option retrieval, merging defaults, helper accessors.
- `inc/sanitization.php`: sanitization callbacks for each option type.
- `inc/template-functions.php`: rendering helpers, body classes, post meta helpers, layout helpers.
- `inc/icons.php`: inline SVG icon registry with accessible labels where needed.
- `inc/admin/`: settings page, admin UI controls, media controls, validation messages, admin assets.

## 3. Admin Settings Architecture

Settings location:

```text
themes/nexa-pro/inc/admin/
```

Primary option:

```text
nexa_pro_options
```

Admin settings principles:

- Use the WordPress Settings API unless a specific custom admin-page requirement justifies otherwise.
- Store theme options in one structured option array.
- Define all defaults in `inc/defaults.php`.
- Retrieve options through helper functions instead of reading raw options throughout templates.
- Sanitize every saved value.
- Escape every rendered value.
- Use nonces and capability checks for all save actions.
- Use real WordPress media-library controls for image fields.
- Admin media fields must have usable fallback text inputs or graceful behavior when JavaScript is unavailable.
- Admin UI should expose only theme presentation settings, not plugin content management.

Proposed settings groups:

- General
  - Site layout width.
  - Container width.
  - Button style.
  - Animation preference default.
  - Back-to-top toggle.
- Branding
  - Logo behavior.
  - Sticky header logo.
  - Footer logo.
  - Favicon guidance through core Site Icon.
- Colors
  - Accent color.
  - Secondary accent color.
  - Background color.
  - Text color.
  - Header background.
  - Footer background.
- Typography
  - Base font family.
  - Heading font family.
  - Font scale.
  - Line height.
- Header
  - Layout variant.
  - Sticky header toggle.
  - Top bar toggle.
  - CTA button text and URL.
  - Mobile menu behavior.
- Footer
  - Layout variant.
  - Footer columns.
  - Copyright text.
  - Social links.
- Homepage Sections
  - Enable/disable sections.
  - Section order.
  - Hero content.
  - Services preview.
  - Case studies preview.
  - Testimonials preview.
  - CTA content.
- Blog
  - Archive layout.
  - Excerpt length.
  - Featured image display.
  - Post meta visibility.
- Performance
  - Disable animations.
  - Local asset loading behavior.
  - Preload key assets where appropriate.

Settings validation:

- Text: `sanitize_text_field`.
- Textarea: `sanitize_textarea_field`.
- URLs: `esc_url_raw`.
- Email: `sanitize_email`.
- Integers: `absint`.
- Checkboxes: normalized booleans.
- Selects/radios: whitelist allowed values.
- Colors: hex validation.
- Media IDs: `absint` with optional attachment validation.
- HTML, if allowed at all, must use a narrow `wp_kses` allowlist.

## 4. Front-End Design System

Nexa Pro should feel modern, premium, calm, and commercially useful for agencies, consultants, SaaS companies, startups, recruiters, and professional service companies.

Design goals:

- Professional rather than decorative.
- Strong content hierarchy.
- High contrast.
- Flexible enough for multiple industries.
- Excellent default spacing.
- Polished responsive behavior.
- Editor and front-end visual consistency.

Design system foundations:

- `theme.json` defines editor-facing design tokens.
- CSS custom properties define runtime design tokens.
- Modular CSS files are authored in `assets/src/` and compiled or copied to `assets/css/`.
- CSS avoids brittle override patches.
- Components are reusable across templates, patterns, and homepage sections.

Token categories:

- Colors: background, surface, text, muted text, border, primary accent, secondary accent, status colors.
- Typography: base font stack, heading font stack, body size, modular scale, line heights, font weights.
- Spacing: section spacing, container padding, grid gaps, component spacing.
- Layout: container widths, content widths, sidebar widths, breakpoints.
- Borders and shadows: radius scale, border colors, focus outlines, subtle elevation.
- Motion: duration tokens, easing tokens, reduced-motion handling.

Core components:

- Buttons.
- Links.
- Cards.
- Forms.
- Navigation.
- Mobile menu.
- Hero layouts.
- Section headings.
- Service cards.
- Case study cards.
- Testimonial cards.
- CTA bands.
- Pagination.
- Comments.
- Search forms.
- Archive loops.
- 404 layout.

Responsive approach:

- Mobile-first CSS.
- Avoid horizontal overflow.
- Ensure navigation remains usable at all widths.
- Use stable dimensions for repeated cards and media where needed.
- Avoid text overlap and clipped button labels.

## 5. Homepage Sections

The homepage should be built from reusable template parts under:

```text
themes/nexa-pro/template-parts/sections/
```

Initial commercial homepage section set:

1. Hero
   - Eyebrow text.
   - Headline.
   - Supporting copy.
   - Primary CTA.
   - Secondary CTA.
   - Optional hero image/media.
   - Optional trust indicators.

2. Logo Cloud
   - Client or partner logos.
   - Accessible image alt handling.
   - Optional grayscale display.

3. Services Overview
   - Section heading.
   - Intro copy.
   - Grid of service cards.
   - Pulls from plugin CPT when plugin is active.
   - Falls back to configured theme content without fake placeholder functionality.

4. Feature Highlights
   - Three to six benefit cards.
   - Icon support.
   - Optional split layout.

5. About / Value Proposition
   - Short company-positioning section.
   - Image and text variant.
   - CTA link.

6. Case Studies Preview
   - Pulls from plugin CPT when active.
   - Displays selected or recent case studies.
   - Graceful empty state for site admins.

7. Process
   - Step-by-step workflow section.
   - Useful for agencies, consultants, recruiters, and SaaS providers.

8. Testimonials
   - Pulls from plugin CPT when active.
   - Accessible carousel only if implemented properly.
   - Static grid preferred for initial build.

9. Metrics / Stats
   - Key numbers.
   - Optional labels.
   - Avoid fake claims in defaults.

10. Blog Preview
    - Recent posts.
    - Category filter option if needed.
    - Accessible card layout.

11. Final CTA
    - Strong closing message.
    - Button pair.
    - Optional background style variant.

Homepage controls:

- Enable/disable sections.
- Configure section order.
- Configure section content where appropriate.
- Use selective fallback behavior when plugin content is unavailable.
- Never ship fake business claims as production defaults.

## 6. Header And Footer Layouts

Header requirements:

- Semantic `<header>`.
- Skip link to main content.
- Accessible site branding.
- Primary navigation.
- Mobile navigation with keyboard support.
- Proper `aria-expanded`, `aria-controls`, and focus management.
- No keyboard traps.
- Sticky header option.
- Optional top bar.
- Optional CTA button.
- Responsive behavior without layout overlap.

Header layout variants:

1. Classic
   - Logo left.
   - Navigation center/right.
   - CTA right.

2. Centered
   - Logo centered or nav split where appropriate.

3. Minimal
   - Logo left.
   - Menu toggle or simplified nav.
   - CTA optional.

Footer requirements:

- Semantic `<footer>`.
- Footer navigation.
- Widget-ready or block-area-ready layout if implemented.
- Copyright text.
- Social links.
- Optional logo.
- Accessible link text.
- Responsive column stacking.

Footer layout variants:

1. Four-column professional footer.
2. Compact footer.
3. CTA footer with navigation columns.

Footer content groups:

- Brand summary.
- Navigation.
- Services links.
- Resources/blog links.
- Contact/social links.
- Copyright/legal row.

## 7. Companion Plugin Responsibilities

Plugin name: Nexa Pro Core

Plugin slug: `nexa-pro-core`

Plugin location:

```text
plugins/nexa-pro-core/
```

The companion plugin owns content functionality that should survive theme switching.

Plugin may register:

- Services.
- Case Studies.
- Team Members.
- Testimonials.

Plugin responsibilities:

- Register custom post types.
- Register taxonomies if needed.
- Register meta fields for CPT-specific details.
- Provide admin columns for CPTs where useful.
- Provide shortcodes or blocks only if required by product direction.
- Provide REST exposure where useful and secure.
- Provide template helper functions that the theme can optionally consume.
- Fail gracefully when the theme is inactive.
- Avoid hard dependency on Nexa Pro unless explicitly guarded.
- Load its own text domain.
- Use plugin-specific prefixes, likely `nexa_pro_core_`.

Theme/plugin integration:

- Theme checks for plugin functions before calling them.
- Theme homepage sections can pull plugin content when available.
- Theme provides admin notices recommending the companion plugin only where appropriate.
- Plugin must not enqueue unnecessary front-end assets globally unless needed.

Potential CPTs:

- Service
  - Title.
  - Editor content.
  - Featured image.
  - Icon/image meta.
  - Summary.
  - Display order.
- Case Study
  - Title.
  - Client name.
  - Industry.
  - Challenge.
  - Solution.
  - Result.
  - Featured image.
  - External URL.
- Team Member
  - Name/title.
  - Role.
  - Bio.
  - Photo.
  - Social links.
- Testimonial
  - Quote.
  - Person name.
  - Role/company.
  - Photo/logo.
  - Rating optional.

## 8. Accessibility

Target:

- WCAG 2.1 AA expectations.

Accessibility requirements:

- Semantic HTML landmarks.
- Skip link.
- Keyboard-accessible navigation.
- Visible focus styles.
- Sufficient color contrast.
- Correct heading hierarchy.
- Proper form labels.
- Accessible names for icon-only controls.
- Alt text handling for images.
- Decorative images hidden from assistive technology.
- No keyboard traps.
- Respect `prefers-reduced-motion`.
- Avoid autoplaying motion.
- Ensure menus can be opened, navigated, and closed by keyboard.
- Ensure mobile menu restores focus sensibly.
- Avoid carousel-like experiences initially unless they are built to accessibility standards.
- Ensure admin settings fields have labels, descriptions, and error messaging.

Accessibility validation:

- Manual keyboard testing.
- Screen reader spot checks.
- Automated axe checks where tooling is available.
- Color contrast checks.
- Reduced motion checks.
- Mobile viewport checks.

## 9. Performance

Performance goals:

- Lightweight first load.
- No unnecessary framework dependency.
- Modular CSS.
- Minimal JavaScript.
- Load assets conditionally.
- Avoid render-blocking work where practical.
- Use responsive images.
- Use WordPress image functions.
- No production placeholder URLs.
- No console errors.
- No PHP warnings or notices.

Performance practices:

- Enqueue only required assets.
- Use cache-friendly versioning.
- Prefer CSS for simple interactions.
- Defer non-critical JavaScript where safe.
- Avoid loading admin assets on non-theme admin pages.
- Avoid global plugin assets unless a shortcode, block, or CPT view needs them.
- Use `wp_get_attachment_image` for responsive images.
- Preload only intentional critical assets.
- Keep SVG icons inline or sprite-based with accessible handling.
- Respect user and device motion preferences.

Validation targets:

- Lighthouse performance pass during release QA.
- No horizontal overflow.
- No oversized unoptimized bundled media.
- Reasonable CSS and JavaScript payloads.
- Query Monitor pass for avoidable PHP warnings and obvious query issues.

## 10. Translation And RTL

Theme translation requirements:

- Text domain: `nexa-pro`.
- All user-facing strings wrapped in translation functions.
- Escape translated output with appropriate escaping functions.
- Generate/update POT files during release.
- Include `languages/` directory.
- Avoid hardcoded English in templates where strings should be translatable.
- Support RTL through `rtl.css` or logical properties where possible.
- Prefer CSS logical properties for spacing and positioning.
- Test header, navigation, buttons, cards, forms, and homepage sections in RTL.

Plugin translation requirements:

- Plugin text domain: `nexa-pro-core`.
- Plugin has its own `languages/` directory.
- Plugin strings are not stored in the theme text domain.

RTL validation:

- Enable an RTL locale.
- Check header and mobile navigation.
- Check grids and section layouts.
- Check icon direction where relevant.
- Check form alignment.
- Check admin settings UI.

## 11. Testing

Testing should cover theme, plugin, accessibility, performance, packaging, and marketplace readiness.

Validation categories:

- PHP syntax checks.
- JavaScript syntax checks.
- PHPCS with WordPress Coding Standards.
- Theme Check.
- Plugin Check where applicable.
- Unit or integration tests where useful.
- Accessibility checks.
- Responsive browser tests.
- No console errors.
- No PHP warnings or notices.
- No horizontal overflow.

Suggested test structure:

```text
tests/
├── theme/
├── plugin/
├── e2e/
├── accessibility/
└── fixtures/
```

Manual QA matrix:

- Fresh WordPress install.
- Theme active without companion plugin.
- Theme active with companion plugin.
- Child theme active with parent theme.
- PHP 7.4.
- Current supported PHP version.
- Latest WordPress.
- Block editor pages.
- Classic content rendering where relevant.
- Homepage with empty plugin content.
- Homepage with populated plugin content.
- Mobile, tablet, desktop.
- RTL locale.
- Reduced motion preference.
- High contrast checks.
- Keyboard-only navigation.

Local build requirement:

- Every implementation phase must produce a local build or local WordPress test state that can be inspected before any git push.
- Packaging work must generate local zip artifacts first.
- Theme, plugin, and child theme zip files must be install-tested locally before release or push.
- Local validation results must be reported before any push is requested or performed.

Release validation checklist:

- No forbidden legacy strings.
- No temporary production-like URLs.
- No missing escaping.
- No unsanitized option saves.
- No admin nonce/capability gaps.
- No PHP notices.
- No console errors.
- No broken images.
- No horizontal overflow.
- POT files generated.
- Zip package excludes development-only files.

## 12. Marketplace Packaging

Marketplace goals:

- Direct commercial sale.
- Professional premium distribution.
- ThemeForest submission readiness.

Package outputs:

- Parent theme zip: `nexa-pro.zip`.
- Companion plugin zip: `nexa-pro-core.zip`.
- Child theme zip: `nexa-pro-child.zip`.
- Documentation bundle if required by marketplace.
- Optional demo content package if later approved.

Packaging rules:

- Exclude development-only files:
  - `.git`.
  - `node_modules`.
  - Raw source maps unless intentionally shipped.
  - Local config.
  - Test artifacts.
  - Temporary files.
  - Package caches.
- Include required WordPress files:
  - `style.css`.
  - `functions.php`.
  - Templates.
  - `theme.json`.
  - Assets.
  - Languages.
  - Docs as appropriate.
- Include plugin headers and readme.
- Include child theme headers and parent template reference.
- Ensure licenses are included for bundled third-party assets.
- Ensure screenshots meet marketplace requirements.
- Ensure demo URLs are real or omitted.
- Ensure no temporary production-like URLs ship.

ThemeForest-oriented considerations:

- Plugin-territory functionality in plugin.
- Theme Check pass.
- Escaping and sanitization pass.
- Translation-ready.
- Documentation included.
- Clear installation instructions.
- Clear companion plugin installation instructions.
- No bundled premium assets without license clarity.
- GDPR/privacy notes where integrations are present.

## 13. Development Phases

### Phase 0: Planning And Repo Baseline

- Approve architecture and development plan.
- Create initial directories.
- Add documentation index.
- Add baseline validation scripts.
- Add coding standards configuration.
- Confirm branch workflow.

### Phase 1: Parent Theme Scaffold

- Create clean parent theme files.
- Add theme headers.
- Add setup includes.
- Add text domain loading.
- Add menus, supports, image sizes.
- Add base templates.
- Add initial `theme.json`.
- Produce a local testable theme state before push.

### Phase 2: Design System And Base Templates

- Add CSS token system.
- Add typography, layout, buttons, forms, cards.
- Add responsive grid utilities.
- Add base JavaScript only where needed.
- Add accessible header and footer.
- Add archive, single, page, search, and 404 templates.
- Validate locally in browser before push.

### Phase 3: Admin Settings

- Add default options.
- Add settings registration.
- Add sanitization callbacks.
- Add admin settings page.
- Add media controls.
- Add no-JavaScript fallback behavior.
- Add settings documentation.
- Validate settings save behavior locally before push.

### Phase 4: Homepage System

- Add homepage template.
- Add section template parts.
- Add section ordering and enable/disable controls.
- Add hero, services, features, case studies, process, testimonials, stats, blog, and CTA sections.
- Add graceful empty states.
- Validate homepage locally before push.

### Phase 5: Companion Plugin

- Create plugin scaffold.
- Register CPTs.
- Register meta.
- Add admin columns.
- Add theme integration helpers.
- Add translation setup.
- Add plugin validation.
- Install and activate plugin locally before push.

### Phase 6: Child Theme

- Create child theme scaffold.
- Add parent dependency metadata.
- Add minimal stylesheet.
- Add child theme documentation.
- Verify activation locally before push.

### Phase 7: Accessibility, RTL, And Responsive QA

- Keyboard testing.
- Mobile menu testing.
- Reduced motion testing.
- RTL stylesheet/logical property review.
- Axe checks.
- Responsive viewport checks.
- Complete local QA before push.

### Phase 8: Performance And Hardening

- Asset audit.
- Conditional enqueueing.
- Query review.
- Lighthouse pass.
- Security review.
- Sanitization/escaping audit.
- Forbidden string scan.
- Complete local performance checks before push.

### Phase 9: Packaging And Release

- Create packaging scripts.
- Generate POT files.
- Build release zips locally.
- Verify clean install from local zips.
- Prepare marketplace documentation.
- Final release checklist.

## 14. Individual Implementation Tickets

### Planning And Infrastructure

1. Create repository directory skeleton for Nexa Pro
   - Create `themes/`, `plugins/`, `child-themes/`, `shared/`, `tests/`, `docs/`, and `scripts/` if missing.
   - Do not add runtime code yet.

2. Add coding standards configuration
   - Configure PHPCS for WordPress Coding Standards.
   - Add project-specific exclusions for vendor/build output.

3. Add validation script skeleton
   - Add scripts for PHP syntax checks, forbidden string scanning, package validation, and local build verification.

4. Add documentation index
   - Create docs landing page linking setup, architecture, QA, and release docs.

### Parent Theme Scaffold

5. Create parent theme base files
   - Add `style.css`, `functions.php`, `theme.json`, and core templates.

6. Implement theme setup module
   - Add theme supports, menus, image sizes, and text domain loading.

7. Implement asset enqueue module
   - Register/enqueue front-end, editor, and admin assets.

8. Implement default options module
   - Define all default theme options in one place.

9. Implement option helper module
   - Add safe option retrieval and default merging helpers.

10. Implement sanitization module
    - Add typed sanitizers and whitelist validators.

11. Implement template helper module
    - Add reusable escaped render helpers and layout helpers.

12. Implement icon system
    - Add accessible inline SVG icon registry.

### Base Templates

13. Build header template
    - Add semantic header, skip link, branding, primary nav, CTA slot, and mobile toggle.

14. Build footer template
    - Add footer layouts, menus, social links, and copyright area.

15. Build index/archive/search templates
    - Add accessible post loops, empty states, and pagination.

16. Build single/page templates
    - Add content rendering, post meta, featured images, and comments support where appropriate.

17. Build 404 template
    - Add accessible search and navigation recovery links.

### Design System

18. Add CSS custom property tokens
    - Define color, typography, spacing, layout, border, shadow, and motion tokens.

19. Add base CSS
    - Normalize core elements, typography, media, links, and forms.

20. Add component CSS
    - Buttons, cards, forms, navigation, pagination, comments, and section primitives.

21. Add responsive layout CSS
    - Containers, grids, section spacing, and mobile behavior.

22. Add reduced motion handling
    - Respect `prefers-reduced-motion`.

23. Add editor style support
    - Align editor typography, widths, colors, and blocks with front end.

### Admin Settings

24. Register theme settings
    - Add Settings API registration for `nexa_pro_options`.

25. Build settings page shell
    - Add admin menu page with tabs/sections.

26. Add general settings controls
    - Layout, container, animation, back-to-top controls.

27. Add branding settings controls
    - Logo-related options and media fields.

28. Add color settings controls
    - Accent, text, background, header, and footer colors.

29. Add typography settings controls
    - Font family choices and scale controls.

30. Add header settings controls
    - Header layout, sticky behavior, top bar, CTA.

31. Add footer settings controls
    - Footer layout, columns, copyright, social links.

32. Add homepage settings controls
    - Section enablement, ordering, and key content.

33. Add blog settings controls
    - Archive layout, excerpt length, featured image, post meta.

34. Add admin media control JavaScript
    - Integrate WordPress media library progressively.

35. Add no-JavaScript admin fallbacks
    - Ensure fields can save without JavaScript.

36. Add settings error handling
    - Show validation messages and save confirmations.

### Homepage

37. Build front-page controller template
    - Load enabled sections in configured order.

38. Build hero section
    - Add text, CTA, image/media, trust indicators.

39. Build logo cloud section
    - Add accessible logo list support.

40. Build services overview section
    - Integrate plugin content when available.

41. Build feature highlights section
    - Add configurable benefit cards.

42. Build about/value proposition section
    - Add split text/media layout.

43. Build case studies preview section
    - Integrate plugin content when available.

44. Build process section
    - Add step-based workflow layout.

45. Build testimonials section
    - Integrate plugin testimonials when available.

46. Build stats section
    - Add configurable metrics without fake default claims.

47. Build blog preview section
    - Query recent posts safely.

48. Build final CTA section
    - Add configurable closing CTA.

### Companion Plugin

49. Create Nexa Pro Core plugin scaffold
    - Add plugin header, bootstrap file, includes, languages directory.

50. Register Service CPT
    - Add labels, supports, rewrite, REST support, and capabilities.

51. Register Case Study CPT
    - Add labels, supports, rewrite, REST support, and capabilities.

52. Register Team Member CPT
    - Add labels, supports, rewrite, REST support, and capabilities.

53. Register Testimonial CPT
    - Add labels, supports, rewrite, REST support, and capabilities.

54. Add plugin meta fields
    - Register sanitized meta for CPT-specific fields.

55. Add plugin admin columns
    - Improve admin management for custom content.

56. Add plugin helper functions
    - Provide safe query helpers for theme integration.

57. Add plugin activation/deactivation handling
    - Flush rewrites safely on activation/deactivation.

58. Add plugin translation support
    - Load plugin text domain.

### Child Theme

59. Create child theme scaffold
    - Add `style.css` and `functions.php`.

60. Enqueue parent and child styles
    - Ensure proper dependency order.

61. Add child theme documentation
    - Explain customization workflow.

62. Validate child theme activation
    - Confirm parent dependency and no warnings.

### Accessibility And QA

63. Add accessibility checklist document
    - Document WCAG 2.1 AA expectations.

64. Test keyboard navigation
    - Header, mobile menu, forms, buttons, links.

65. Test screen reader basics
    - Landmarks, headings, labels, menu state.

66. Test color contrast
    - Default palette and configurable palette boundaries.

67. Test reduced motion
    - Confirm motion is disabled/reduced when requested.

68. Test responsive layouts
    - Mobile, tablet, desktop, wide desktop.

69. Test RTL
    - Enable RTL locale and inspect layouts.

### Performance And Security

70. Audit asset loading
    - Ensure conditional enqueueing and minimal payload.

71. Audit responsive images
    - Confirm WordPress image functions are used.

72. Audit escaping
    - Review all template output.

73. Audit sanitization
    - Review all saved settings and meta.

74. Audit admin capabilities and nonces
    - Confirm every admin action is protected.

75. Scan forbidden strings
    - Ensure no legacy or placeholder strings exist.

### Local Build And Release Readiness

76. Add local build workflow
    - Provide commands or scripts to build assets, package zips, and run local validation before push.

77. Add packaging scripts
    - Build clean zips for theme, plugin, and child theme.

78. Add POT generation workflow
    - Generate translation templates.

79. Add marketplace documentation
    - Installation, setup, companion plugin, customization, FAQ.

80. Add release checklist
    - Validation, screenshots, packaging, install tests, and local build sign-off.

81. Perform clean install test from local zips
    - Verify parent theme, plugin, and child theme install cleanly from locally generated artifacts.

82. Prepare release candidate
    - Tag candidate internally after validation.

83. Prepare final marketplace package
    - Confirm package contents, docs, licenses, screenshots, and local install results.
