# Nexa Pro 1.1 Beta Testing Guide

Nexa Pro `1.1.0-beta.1` and Nexa Pro Core `0.5.0-beta.1` are beta software.
Use them on staging or test sites only.

Do not run migration on irreplaceable production data without a full database
and uploads backup.

## Prerequisites

- WordPress 6.0 or newer.
- PHP 7.4 or newer.
- Nexa Pro theme package:
  `dist/nexa-pro-1.1.0-beta.1.zip`.
- Nexa Pro Core plugin package:
  `dist/nexa-pro-core-0.5.0-beta.1.zip`.
- Debug logging enabled for beta QA where practical.
- A separate clean LocalWP site for install testing.
- A separate cloned/test LocalWP site for upgrade testing.

## Clean Install

1. Install `dist/nexa-pro-1.1.0-beta.1.zip` through Appearance > Themes > Add New > Upload Theme.
2. Activate Nexa Pro.
3. Install `dist/nexa-pro-core-0.5.0-beta.1.zip` through Plugins > Add New > Upload Plugin.
4. Activate Nexa Pro Core.
5. Confirm the theme version is `1.1.0-beta.1`.
6. Confirm the plugin version is `0.5.0-beta.1`.
7. Confirm Appearance > Nexa Pro Builder loads.
8. Confirm new installs do not force a migration prompt or alter frontend output.
9. Add, edit, disable, enable, duplicate, reorder, and delete a page component.
10. Enable builder compatibility mode explicitly from the Migration screen.
11. Confirm builder-rendered frontend sections appear only for pages with valid builder data.
12. Confirm generated navigation and reusable components still work.

## Upgrade Test

Use a cloned or disposable site with Nexa Pro `1.0.0` fixed-section settings.

1. Confirm the legacy frontend renders correctly before upgrade.
2. Upgrade/install Nexa Pro `1.1.0-beta.1`.
3. Install/upgrade Nexa Pro Core `0.5.0-beta.1`.
4. Confirm the frontend is unchanged while compatibility mode is `legacy`.
5. Open Appearance > Nexa Pro Builder > Migration.
6. Confirm migration is available only when legacy data and a valid target page exist.
7. Preview migration in `merge` mode.
8. Review source hash, target page, component count, legacy order, anchors, warnings, and unsupported sections.
9. Apply migration without switching to builder mode.
10. Confirm legacy frontend output remains active.
11. Switch compatibility mode to `builder`.
12. Confirm migrated builder components render on the target page.
13. Confirm there is one meaningful H1 and no duplicate section IDs.
14. Confirm generated navigation links use migrated anchors.
15. Roll back migration.
16. Confirm previous builder data, navigation settings, and compatibility mode are restored.
17. Reapply migration and confirm no duplicate components are created.
18. Modify one legacy setting and preview again to confirm the source-changed warning appears.

## Migration Notes

- Legacy theme settings in `nexa_pro_options` are preserved.
- Migration writes plugin-owned page component meta only after explicit apply.
- Replace mode affects only the selected page's plugin-managed builder data and creates a backup first.
- Trust and any unsupported legacy sections remain in legacy settings and are reported as unsupported.
- Rollback restores the most recent migration backup only.

## Import And Export

Test these flows from Appearance > Nexa Pro Builder > Import/Export:

1. Export full site configuration.
2. Export one page.
3. Export reusable components.
4. Export navigation settings.
5. Export global settings.
6. Export the migration report after migration.
7. Preview a valid export.
8. Apply an import with explicit page mapping.
9. Test conflict modes: skip, merge, replace, and create-new.
10. Confirm create-new creates draft pages instead of silently mapping unrelated pages.
11. Confirm reusable source IDs are remapped when importing linked instances.
12. Reject malformed JSON.
13. Reject wrong-product JSON.
14. Reject unsupported schema JSON.
15. Reject oversized JSON.
16. Preview a schema version `1` settings export and confirm limitations are shown.

## Responsive And Accessibility Checks

Test admin widths:

- 320px
- 375px
- 768px
- 1280px

Test frontend widths:

- 320px
- 375px
- 768px
- 1024px
- 1280px
- 1440px

Confirm:

- No horizontal overflow.
- Migration preview tables remain readable.
- Import page mapping controls remain usable.
- Destructive confirmations are visible and labelled.
- Keyboard focus is visible.
- Mobile navigation opens, closes, and returns focus.
- Modals retain focus and close with Escape.
- Reduced-motion settings are respected.
- Generated navigation marks the active section when enabled.

## Recovery Procedure

If migration output is not acceptable:

1. Return compatibility mode to `legacy`.
2. Use the Migration screen rollback action.
3. Confirm the frontend renders legacy fixed sections again.
4. Export current settings and builder data for debugging.
5. Record the migration hash, warnings, target page, mode, and plugin version.
6. Restore a database backup if rollback is not enough for the test scenario.

## Known Beta Limitations

- Migration does not create WordPress menus automatically.
- Migration does not delete or rewrite legacy theme settings.
- Import does not sideload media from remote sites.
- Import does not install plugins or create demo content.
- Import/export does not execute arbitrary CSS, JavaScript, PHP, or remote fetches.
- Rollback stores only the latest migration backup.
- Drag-and-drop visual canvas editing is not included.

## Issue Report Template

Include:

- WordPress version.
- PHP version.
- Theme version.
- Plugin version.
- Component storage schema version.
- Export schema version.
- Browser and viewport width.
- Clean install or upgrade path.
- Migration mode.
- Compatibility mode.
- Steps to reproduce.
- Expected result.
- Actual result.
- Browser console output.
- PHP debug log excerpt.
- Sanitized export or migration report when relevant.
