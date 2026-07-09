# Nexa WordPress Themes Development Instructions

## Repository purpose

This repository is a monorepo for building multiple professional commercial WordPress themes.

Current first product:

- Theme name: Nexa Pro
- Theme slug: nexa-pro
- Theme location: themes/nexa-pro
- Companion plugin: plugins/nexa-pro-core
- Child theme: child-themes/nexa-pro-child

## Product goals

Nexa Pro must be suitable for:

- Direct commercial sale
- Professional premium distribution
- ThemeForest submission
- Agencies
- Consultants
- SaaS companies
- Startups
- Recruiters
- Professional service companies

## Architecture

Use this structure:

wordpress-themes/
├── themes/
│ └── nexa-pro/
├── plugins/
│ └── nexa-pro-core/
├── child-themes/
│ └── nexa-pro-child/
├── shared/
├── tests/
├── docs/
└── scripts/

## Theme development rules

1. Build Nexa Pro from scratch.
2. Do not use or recreate old Connexa code.
3. Do not use legacy-runtime.php.
4. Do not create fake placeholder functionality.
5. Use the PHP prefix `nexa_pro_`.
6. Use the text domain `nexa-pro`.
7. Use the option name `nexa_pro_options`.
8. Follow WordPress coding standards.
9. Support PHP 7.4 and newer.
10. Sanitize every saved value.
11. Escape all rendered output.
12. Use capability checks and nonces for admin actions.
13. Use semantic HTML.
14. Meet WCAG 2.1 AA accessibility expectations.
15. Respect prefers-reduced-motion.
16. Do not solve structural issues with override CSS patches.
17. Keep CSS modular and maintainable.
18. Use real WordPress media-library controls.
19. Admin functionality must fail gracefully when JavaScript is unavailable.
20. Do not edit the main branch directly.

## Theme structure

themes/nexa-pro/
├── assets/
│ ├── css/
│ ├── js/
│ ├── images/
│ └── src/
├── inc/
│ ├── setup.php
│ ├── assets.php
│ ├── defaults.php
│ ├── options.php
│ ├── sanitization.php
│ ├── template-functions.php
│ ├── icons.php
│ └── admin/
├── template-parts/
│ └── sections/
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

## Companion plugin responsibilities

Plugin-style content belongs in `plugins/nexa-pro-core`.

The plugin may register:

- Services
- Case Studies
- Team Members
- Testimonials

Do not place custom post types inside the theme.

## Git workflow

- main: stable releases
- develop: integration
- feature/\*: new functionality
- fix/\*: bug fixes
- release/\*: release preparation

Codex must:

1. Work on a feature or fix branch.
2. Explain the plan before editing.
3. Keep changes limited to the active ticket.
4. Run validation before completion.
5. Show changed files.
6. Show remaining risks.
7. Suggest a commit message.
8. Do not push or merge unless explicitly asked.

## Required validation

Run where available:

- PHP syntax checks
- JavaScript syntax checks
- PHPCS with WordPress Coding Standards
- Theme Check
- Unit or integration tests
- Accessibility checks
- Responsive browser tests
- No console errors
- No PHP warnings or notices
- No horizontal overflow

## Forbidden legacy strings

The project must not include:

- Connexa
- connexa\_
- legacy-runtime
- nexapro.example.com
- placeholder production URLs

## Completion report

For every development task, report:

1. Summary
2. Root cause or design reasoning
3. Files changed
4. Tests run
5. Test results
6. Remaining risks
7. Suggested commit message
