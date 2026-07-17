# Nexa WordPress Themes Development Instructions

## Repository Purpose

This repository is a monorepo for building professional commercial WordPress themes.

Current primary product:

- Theme name: Nexa Pro
- Theme slug: nexa-pro
- Theme location: `themes/nexa-pro`
- Companion plugin: `plugins/nexa-pro-core`
- Child theme: `child-themes/nexa-pro-child`

Nexa Pro is intended to be a sellable premium WordPress product, not only a codebase. Every change must protect the buyer experience, admin usability, save reliability, marketplace readiness, and long-term supportability.

## Product Standard

Nexa Pro must be suitable for:

- Direct commercial sale
- Professional premium distribution
- ThemeForest-style marketplace review
- Agencies
- Consultants
- SaaS companies
- Startups
- Recruiters
- Professional service companies

The product should let a non-technical customer create, edit, preview, and publish a professional website with confidence.

## Non-Negotiables

1. Saving must work end to end before any UI change is considered complete.
2. Never ship an admin control that looks usable but does not persist correctly.
3. Never create fake placeholder functionality.
4. Never expose internal schema, option names, component keys, or developer terminology in primary customer UI.
5. Never use or recreate old Connexa code.
6. Never use `legacy-runtime.php`.
7. Never place plugin functionality in the theme.
8. Never make broad visual changes without checking responsive behavior and keyboard access.
9. Never push, merge, tag, or package unless explicitly asked.
10. Never edit `main` directly.

## Repository Architecture

Use this structure:

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

## Theme Responsibilities

The theme owns presentation and theme-level configuration:

- Front-end templates
- Header and footer rendering
- Theme options
- Design tokens
- Typography, color, spacing, radius, shadow, and button styling
- Homepage section rendering
- Accessibility and responsive behavior
- Translation and RTL readiness
- Theme packaging files

Theme rules:

1. Use the PHP prefix `nexa_pro_`.
2. Use the text domain `nexa-pro`.
3. Use the option name `nexa_pro_options`.
4. Follow WordPress coding standards.
5. Support PHP 7.4 and newer.
6. Sanitize every saved value.
7. Escape every rendered value.
8. Use capability checks and nonces for admin actions.
9. Use semantic HTML.
10. Meet WCAG 2.1 AA accessibility expectations.
11. Respect `prefers-reduced-motion`.
12. Keep CSS modular and maintainable.
13. Do not solve structural problems with bottom-of-file override patches.
14. Use real WordPress media-library controls.
15. Admin functionality must fail gracefully without JavaScript.

## Companion Plugin Responsibilities

Plugin-style content and application behavior belongs in `plugins/nexa-pro-core`.

The companion plugin may own:

- Visual builder
- Page template library
- Reusable sections
- Import/export tools
- Migration tools
- Builder storage
- Builder rendering APIs
- Future custom post types, if approved

The plugin may register:

- Services
- Case Studies
- Team Members
- Testimonials

Do not place custom post types, shortcodes, or app-like builder storage inside the theme.

## Admin UX Principles

The Nexa Pro admin must feel like a polished product, not a collection of technical WordPress settings.

Primary admin navigation should stay simple:

- Dashboard
- Pages
- Navigation
- Site Design
- More

The More area may contain:

- Saved Sections
- Import / Export
- Migration
- Tools
- Help

Customer-facing rules:

1. Use plain language.
2. Prefer "Edit Content" and "Change Design" over technical labels.
3. Prefer "Pages" over "builder views".
4. Prefer "Site Design" over "theme schema" or "global config".
5. Prefer "Saved Sections" over "component registry".
6. Keep diagnostics, schema labels, migration details, and advanced tools out of the primary workflow.
7. Always give the user a clear way to save, preview, publish, and go back.

## Recommended Admin Design Direction

Use a clean premium SaaS-style admin interface:

- White and soft-gray surfaces
- Dark navy text
- Purple or indigo primary actions
- Restrained borders
- Moderate border radius
- Subtle shadows
- Clear visual hierarchy
- Generous spacing
- Consistent cards and panels
- Visible keyboard focus states
- Calm, professional typography

Avoid:

- cluttered WordPress-settings-page layouts for primary workflows
- large blocks of technical helper text
- raw IDs as the main label
- crowded multi-column layouts on narrow screens
- hidden save behavior
- controls that visually imply action but do not submit or persist

## Dashboard UX

The Dashboard should act as the website command center.

Recommended content:

- Welcome and site status
- Primary actions:
  - Create Page
  - Edit Homepage
  - Preview Website
- Website overview:
  - one-page or multi-page mode
  - homepage status
  - navigation mode
  - published pages
  - visible sections
- Setup checklist:
  - add logo
  - set colors
  - edit homepage
  - configure navigation
  - preview site
- Recent or important page cards

Do not make schema versions, raw option names, storage counts, or developer diagnostics prominent on the Dashboard.

## Website Creation Flow

The preferred customer flow is:

1. Choose Website Type
   - One-page Website
   - Multi-page Website
2. Choose Template
   - visual template cards
   - search and filters
   - preview template
   - use template
3. Create Page
   - title
   - slug
   - starter content choice
   - draft default
4. Build Page
   - open the Visual Page Builder
5. Preview
6. Publish

Do not ask for layout, anchors, advanced navigation, or design details before page creation unless the user explicitly chooses an advanced path.

## Visual Builder UX

The Visual Page Builder is the core product experience.

Desktop layout should prioritize:

- Top bar:
  - page title
  - status
  - desktop/tablet/mobile preview controls
  - Preview
  - Save
  - Publish or Update
  - Back to Pages
- Left panel:
  - Page Sections
  - Add Section
  - ordered section cards
- Main panel:
  - live front-end preview

Section cards should show:

- number
- friendly name
- short content summary
- visible or hidden state
- content completion state when available
- Edit Content
- Change Design
- More actions:
  - Duplicate
  - Move Up
  - Move Down
  - Save as Saved Section
  - Hide or Show
  - Delete

When a section is selected:

1. The card should visibly select.
2. The preview should scroll to the matching section.
3. The matching preview section should highlight when possible.
4. The user should be able to edit content or change design immediately.

Only allowlisted section identifiers may be used for preview scrolling, anchors, or postMessage events.

## Section Editing UX

Use three mental groups:

- Content
- Design
- Advanced

Content controls may include:

- label
- heading
- text
- buttons
- images
- repeater items

Design controls may include:

- layout
- alignment
- background
- overlay
- text theme
- spacing
- width

Advanced controls should be collapsed by default and reserved for:

- anchor
- custom class if approved
- rare technical controls

After saving a section:

1. Persist values.
2. Refresh or update the preview.
3. Keep the same section selected.
4. Show a clear success or error message.
5. Provide "Return to Page Structure".

## Add Section UX

The Add Section experience should be visual and simple.

Recommended tabs:

- Recommended
- All Sections
- Saved Sections

Each section option should show:

- friendly name
- short description
- optional preview or icon
- Add Section action

After adding a section:

1. Insert it at the chosen location.
2. Select the new section.
3. Refresh the preview.
4. Scroll to the new section.
5. Open Edit Content or clearly offer it as the next action.

## Navigation UX

Navigation is a first-class product feature and must support both one-page and multi-page websites.

Primary location:

- `Nexa Pro -> Navigation`

Connected locations:

- Visual Builder section cards
- Add Section flow
- Section Advanced settings
- Header rendering

Recommended navigation modes:

- One-page Auto Menu
- Multi-page WordPress Menu
- Hybrid Menu

For one-page websites:

1. Navigation should be generated from enabled page sections.
2. Adding a section should create or preserve a valid anchor.
3. Adding a section should make the section eligible for navigation.
4. The user must be able to show or hide each section in navigation.
5. Section visibility and navigation visibility must be separate controls.
6. Hidden sections must not create visible navigation links.
7. Re-enabled sections should return to their saved navigation position when possible.
8. Navigation labels must be editable and customer-facing.

Use this mental model:

```text
Show Section        = appears on the page
Show in Navigation  = appears in the menu
```

For multi-page websites:

1. Navigation should prioritize normal WordPress pages and menus.
2. Page links should be clear and editable.
3. Section anchors may still exist for in-page buttons or hybrid navigation.
4. Multi-page mode must not depend on one-page section anchors.

Builder section cards should make navigation state visible when relevant:

- Visible or Hidden
- In Navigation or Hidden from Navigation
- Navigation label when useful

Do not create broken anchors. Do not leave links to disabled or missing sections. Do not silently remove a user's custom navigation label.

## Site Design UX

Site Design is for global appearance only.

Recommended groups:

- Brand and Logo
- Colors
- Typography
- Header
- Footer
- Buttons
- Cards
- Images
- Site Width
- Global Spacing
- Background
- Advanced Custom CSS

Do not mix page-specific section editing into Site Design.

## Save-Flow Safety

Before changing any admin UI, identify the save path:

- form element
- submit button
- form action
- nonce field
- capability check
- field names
- sanitizer
- storage function
- redirect URL
- success notice
- error notice
- reload behavior

For every changed save flow, test:

1. Change a value.
2. Save.
3. Confirm success or useful error.
4. Reload.
5. Confirm the value persists.
6. Change it again.
7. Save and reload again.
8. Remove or clear it when applicable.
9. Confirm the cleared value persists.

If a Save button is outside its form, it must have a correct `form` attribute. Buttons that should not submit must use `type="button"`.

## Media-Control Safety

All media controls must:

- store attachment IDs, not URLs
- use `wp_enqueue_media()` only where needed
- use unique field IDs
- use matching labels
- update only the active field
- update preview immediately
- update hidden attachment ID immediately
- replace existing images reliably
- remove images reliably
- keep focus on a visible usable control
- save and reload correctly

Shared media JavaScript must not leak state between fields.

## Accessibility Requirements

Check:

- one meaningful H1 per page
- logical heading hierarchy
- semantic landmarks
- skip link
- keyboard-accessible navigation
- visible focus states
- `aria-expanded`
- `aria-controls`
- `aria-current` where appropriate
- dialog accessible names
- focus trap only inside true modals
- Escape close behavior
- focus return after modal close
- form labels
- repeater control labels
- ordering control labels
- media control labels
- color-picker labels
- empty and disabled states
- image alt behavior
- decorative background handling
- reduced-motion support

Prefer native HTML over unnecessary ARIA.

## Responsive Requirements

Every admin and front-end change must be checked at:

- 320px
- 375px
- 768px
- 1280px

Confirm:

- no horizontal overflow
- readable text
- usable buttons
- visible focus
- mobile navigation works
- modals fit the viewport
- images remain responsive
- admin panels stack logically

For the builder on narrow screens, prefer a simple mode switch:

- Structure
- Preview
- Edit

Do not force the desktop split-panel builder into small screens.

## LocalWP Testing Rules

Use authenticated LocalWP testing when work affects:

- save behavior
- admin UI
- media controls
- visual builder
- navigation
- import/export
- presets
- migration
- modals
- front-end rendering

Protected development site:

- `connexa-consulting`
- Treat as read-only unless the user explicitly approves writes.

Disposable validation site:

- Use a separate LocalWP site for write tests when available.
- Create a backup before destructive or broad testing.
- Clean up test data when practical.

Do not claim manual validation passed unless the actual browser flow was exercised.

## Git Workflow

Branches:

- `main`: stable releases
- `develop`: integration
- `feature/*`: new functionality
- `fix/*`: bug fixes
- `release/*`: release preparation

Codex must:

1. Work on a feature or fix branch.
2. Explain the plan before editing.
3. Keep changes limited to the active ticket.
4. Protect unrelated user changes.
5. Run validation before completion.
6. Show changed files.
7. Show remaining risks.
8. Suggest a commit message.
9. Do not push or merge unless explicitly asked.

## Required Validation

Run where relevant and available:

- PHP syntax checks
- JavaScript syntax checks
- `theme.json` validation
- project validation scripts
- PHPCS with WordPress Coding Standards
- Theme Check
- unit or integration tests
- accessibility checks
- responsive browser tests
- no console errors
- no PHP warnings or notices
- no horizontal overflow
- `git diff --check`

For builder or companion plugin changes, run the relevant plugin validators in `plugins/nexa-pro-core/tests/` and `scripts/`.

## Release and Packaging Rules

Before marketplace or beta release:

- theme metadata must be accurate
- version references must be consistent
- screenshot must exist and validate
- README, changelog, credits, and license must be present
- generated ZIP must install cleanly
- ZIP root must be `nexa-pro/`
- generated ZIP files must not be tracked
- no repo-only files may be required at runtime
- no PHP fatal errors, warnings, or notices
- no browser console errors
- clean LocalWP install must pass activation and basic editing tests

Do not mark a release as final unless explicitly requested.

## Forbidden Legacy Strings

The project must not include these in product code:

- Connexa
- `connexa_`
- `legacy-runtime`
- `nexapro.example.com`
- placeholder production URLs

Documentation may mention old project context only when needed to explain migration or forbidden usage.

## Completion Report

For every development task, report:

1. Summary
2. Design reasoning or root cause
3. Files changed
4. Save-flow impact
5. Accessibility impact
6. Responsive impact
7. Tests run
8. Test results
9. Manual browser checks, if performed
10. Remaining risks
11. Suggested commit message
