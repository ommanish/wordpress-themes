# Nexa Pro

Nexa Pro is a commercial-ready WordPress parent theme for professional service
websites, agencies, consultants, SaaS teams, startups, recruiters, and similar
businesses.

Current version: `1.0.0`

## Requirements

- WordPress 6.0 or newer
- PHP 7.4 or newer
- A modern browser for the best admin editing experience

The theme is designed to work without the companion plugin. When Nexa Pro Core
is active, the theme owns component presentation, layout variations, design
token classes, responsive behavior, and accessibility of rendered components.
Plugin-owned persistent builder content remains in `nexa-pro-core`.

## Installation

1. In WordPress admin, go to Appearance > Themes.
2. Upload the `nexa-pro` ZIP package.
3. Activate Nexa Pro.
4. Go to Appearance > Menus and assign a Primary Menu and, optionally, a Footer Menu.
5. Open Appearance > Nexa Pro to configure theme settings.

## Admin Settings Overview

Nexa Pro stores theme settings in the `nexa_pro_options` option.

Available settings areas include:

- Global design colors and typography
- Header branding, desktop/mobile logos, layout, sticky mode, transparent mode, and CTA
- Hero copy and CTA links
- Homepage section visibility, content, media, repeaters, and ordering
- Footer branding, legal links, contact details, social links, and schedule modal actions
- Tools for settings export, validated import, starter presets, reset, and rollback

Settings are sanitized on save and escaped on render. Media settings store
attachment IDs only.

## Homepage Setup

The front page renders a fixed hero and trust area followed by enabled movable
sections. The movable section order can be configured from Appearance > Nexa Pro
> Homepage Order.

Controlled sections include:

- About
- Services
- Features
- Process
- Why Choose Us
- Portfolio
- Testimonials
- Team
- FAQ
- Contact
- CTA

If a section is disabled, its wrapper and anchor are removed from the page.
Update menu and CTA links manually when they target disabled sections.

## Component Presentation

When rendered from Nexa Pro Core builder data, Nexa Pro supports registry-driven
layout variations for Hero, About, Services, Features, Process, Why Choose Us,
Portfolio, Testimonials, Team, FAQ, CTA, and Contact components. Component
design can inherit global theme tokens, use a built-in preset, or apply safe
overrides for spacing, width, alignment, backgrounds, cards, radius, shadow,
images, and buttons.

The theme does not accept arbitrary CSS from component data. It renders
allowlisted classes and safe CSS custom properties only. FAQ accordions use
native buttons and remain readable without JavaScript.

## Import, Export, Presets, And Rollback

The Tools tab can export normalized Nexa Pro settings as JSON. Import uses a
preview-first flow:

1. Upload JSON.
2. Validate product, schema, known keys, and media attachment IDs.
3. Review the preview summary.
4. Confirm before applying settings.

Imports replace known Nexa Pro settings and fill missing known settings from
theme defaults. Unknown settings are discarded. Invalid local media references
are cleared to `0`.

Starter presets are built in and preserve site-specific media, contact, legal,
social, calendar, and brand identity values.

Before import, preset apply, or reset, the theme stores one rollback snapshot.
Rollback restores only Nexa Pro settings.

## Accessibility Notes

The theme includes:

- Skip link to the main content area
- Semantic header, main, footer, nav, and section structure
- Keyboard-accessible mobile navigation
- Dialog focus management and focus return for the schedule modal
- Visible focus styles with `:focus-visible` and a fallback for older browsers
- Reduced-motion CSS for users who request it
- Responsive image output through WordPress image functions where attachment IDs exist

Manual keyboard, screen-reader smoke, color contrast, and responsive checks are
still required before a final release.

## Screenshot

Nexa Pro includes `screenshot.png` at `themes/nexa-pro/screenshot.png`.
The screenshot is `1200 x 900` and is included in the Nexa Pro 1.0.0
release package.

## Development Commands

From the repository root:

```sh
scripts/validate-theme.sh
scripts/package-nexa-pro.sh
```

The package script writes ZIP files to `dist/`, which is intentionally ignored
by Git.
