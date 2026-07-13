# Nexa Pro Core

Nexa Pro Core is the companion plugin for Nexa Pro component storage and page
component builder administration.

Current plugin version: `0.3.0`

Current schema version: `1`

## Purpose

Nexa Pro Core owns persistent component composition data that should survive a
theme switch. It stores page-level component instances, private reusable
component records, and the builder administration workflow for composing real
WordPress pages from component instances.

The Nexa Pro theme remains responsible for presentation, templates, rendering,
design capabilities, and legacy fixed-section compatibility.

## Current Scope

Included through Phase 6D:

- Plugin bootstrap and activation/deactivation hooks
- Schema metadata
- Page component storage in page meta
- Private reusable component custom post type
- Reusable component payload meta
- Capability registration
- Sanitization and validation
- Helper APIs for storage operations
- Read-side helpers for future rendering integration
- Appearance > Nexa Pro Builder admin screen
- Editable WordPress page list and filters
- Add, edit, duplicate, enable, disable, reorder, move, delete, and undo actions
- Component editor groups for Content, Layout, Design, Navigation, and Advanced
- Plugin-scoped builder admin CSS and JavaScript
- Navigation settings stored in `nexa_pro_core_navigation`
- WordPress, generated, and hybrid navigation modes
- Generated navigation trees from page component metadata
- Hybrid insertion before, after, or in place of a placeholder menu item
- Reusable Components admin screen
- Linked and local reusable insertion workflows
- Reusable usage counts and affected page lists
- Reusable detach, archive, restore, and safe delete behavior
- Conservative uninstall behavior
- Standalone and WordPress-aware storage, builder, navigation, and reusable
  validation scripts

Not included in Phase 6D:

- Migration UI
- Legacy settings migration
- Import/export UI for component data
- REST or AJAX write endpoints
- Demo page creation
- Layout variation expansion
- Layout or design preset inheritance

## Installation

1. Copy or symlink `plugins/nexa-pro-core` into `wp-content/plugins/`.
2. In WordPress admin, open Plugins.
3. Activate Nexa Pro Core.

The plugin does not require the Nexa Pro theme to activate. If Nexa Pro is
active, the plugin can use the theme component registry. If another theme is
active, stored data remains intact and the plugin uses a safe fallback allowlist.

## Storage Model

Page component instances are stored in page meta:

```text
_nexa_pro_components
```

Reusable component records use a private custom post type:

```text
nexa_component
```

Reusable component payloads are stored in post meta:

```text
_nexa_pro_core_component_payload
```

Schema metadata is stored in:

```text
nexa_pro_core_migration_state
```

## Component Instance Fields

Component instances support:

- `instance_id`
- `component_type`
- `admin_title`
- `enabled`
- `order`
- `layout`
- `content`
- `design`
- `navigation`
- `advanced`
- `reusable_component_id`
- `inheritance_mode`
- `schema_version`
- `created_at`
- `updated_at`

Instance IDs are stable and generated in the form:

```text
nexa_<type>_<random>
```

## APIs

Page component helpers:

- `nexa_pro_core_get_page_components( $page_id )`
- `nexa_pro_core_save_page_components( $page_id, array $components )`
- `nexa_pro_core_add_page_component( $page_id, array $component )`
- `nexa_pro_core_update_page_component( $page_id, $instance_id, array $changes )`
- `nexa_pro_core_duplicate_page_component( $page_id, $instance_id )`
- `nexa_pro_core_remove_page_component( $page_id, $instance_id )`
- `nexa_pro_core_reorder_page_components( $page_id, array $ordered_ids )`
- `nexa_pro_core_move_page_component( $source_page_id, $target_page_id, $instance_id )`
- `nexa_pro_core_count_page_components( $page_id )`
- `nexa_pro_core_get_renderable_page_components( $page_id )`
- `nexa_pro_core_has_builder_components( $page_id )`

Reusable component helpers:

- `nexa_pro_core_create_reusable_component( array $component )`
- `nexa_pro_core_get_reusable_component( $post_id )`
- `nexa_pro_core_update_reusable_component( $post_id, array $changes )`
- `nexa_pro_core_duplicate_reusable_component( $post_id )`
- `nexa_pro_core_archive_reusable_component( $post_id )`
- `nexa_pro_core_delete_reusable_component( $post_id )`
- `nexa_pro_core_resolve_component_instance( array $instance )`
- `nexa_pro_core_detach_reusable_component( $page_id, $instance_id )`
- `nexa_pro_core_get_reusable_usage_count( $post_id )`
- `nexa_pro_core_list_linked_page_instances( $post_id )`

## Capabilities

Activation grants these capabilities to administrators only:

- `manage_nexa_pro_components`
- `manage_nexa_pro_reusable_components`
- `import_nexa_pro_components`
- `export_nexa_pro_components`

Write helpers verify the appropriate capability. Controlled tests may pass an
explicit bypass only when `NEXA_PRO_CORE_TESTING` is defined.

## Builder Admin

The Phase 6C builder screen is available at:

```text
Appearance > Nexa Pro Builder
```

The screen is provided by Nexa Pro Core and uses the
`manage_nexa_pro_components` capability. It lists editable WordPress pages,
filters by search/status/configuration, shows component counts, and provides
edit and preview links for the selected page.

For the selected page, administrators can:

- Add a component from the theme registry or safe fallback registry.
- Edit component content, layout, design, navigation, and advanced fields.
- Duplicate a component with a new instance ID and unique anchor.
- Enable or disable a component without deleting it.
- Move components up or down without JavaScript.
- Drag components to reorder when JavaScript is available, then explicitly save order.
- Move a component to another editable page.
- Delete a component after checking a confirmation box.
- Undo the most recent deletion through a short-lived user-scoped transient.

All write actions use authenticated `admin-post.php` handlers, capability
checks, nonces, sanitized request data, and PRG redirects. Builder JavaScript is
progressive enhancement only; core add, edit, save, enable/disable, move,
delete, and undo paths remain available without JavaScript.

The builder does not overwrite `post_content`, does not create pages
automatically, and does not create WordPress menus automatically.

## Component Editor Groups

The builder editor groups fields into:

- Content: admin title, enabled state, common text, CTA, and attachment-ID fields.
- Layout: layout variation, alignment, media position, width, columns, and spacing.
- Design: preset, background, gradient, overlay, text-theme, card, radius, and shadow controls.
- Navigation: future generated-navigation values such as label, anchor, CTA highlight, and mobile visibility.
- Advanced: sanitized class tokens, ARIA label, semantic element, device visibility, and animation preset.

The editor uses typed controls rather than arbitrary JSON, CSS, JavaScript, or
PHP input.

## Reusable Component Behavior

Reusable components are private records with no public URLs, rewrite rules,
REST exposure, or default WordPress management screens.

The Reusable Components screen is available at:

```text
Appearance > Nexa Pro Builder > Reusable Components
```

The screen supports:

- Create reusable component
- Edit source content, layout, design, navigation defaults, and advanced fields
- Duplicate
- Archive
- Restore
- Delete when not linked
- Detach all linked instances, then delete
- View linked-use count
- View affected pages
- Insert into a page as linked or local
- Create a reusable source from a page instance

Page instances support two inheritance modes:

- `local`: all data is stored on the page instance.
- `linked`: content, design, layout, and advanced settings resolve from the
  reusable component while page placement and navigation remain local.

Recursive and self-referential reusable links are rejected. The builder displays
visible badges and actions for local instances, linked instances, missing
sources, and archived sources.

Linked instances resolve content, layout, design, and advanced data from the
reusable source. Page-local navigation and placement remain on the page
instance. Local copies store complete data and do not receive future source
updates.

If a source is missing, the builder does not fatal. Administrators can detach the
page instance to a local copy using its stored page data.

## Navigation

Navigation settings are available at:

```text
Appearance > Nexa Pro Builder > Navigation
```

Settings are stored in:

```text
nexa_pro_core_navigation
```

Navigation source modes:

- `wordpress`: preserves the assigned WordPress Primary Menu. No generated
  items are injected.
- `generated`: renders menu items from configured pages and eligible component
  navigation metadata.
- `hybrid`: combines WordPress Primary Menu items with generated items.

Generated navigation can use:

- A primary single-page page.
- A configured multipage list.
- Eligible page component instances.
- Component labels, anchors, parent relationships, order overrides, CTA
  highlighting, and mobile visibility.

Hybrid insertion can place generated items:

- Before WordPress menu items.
- After WordPress menu items.
- In place of a placeholder URL such as `#nexa-generated-navigation`.

The plugin exposes:

- `nexa_pro_core_get_navigation_settings()`
- `nexa_pro_core_get_navigation_defaults()`
- `nexa_pro_core_get_generated_navigation()`
- `nexa_pro_core_get_page_navigation_items()`
- `nexa_pro_core_validate_navigation_tree()`

The Nexa Pro theme owns frontend navigation markup and presentation. WordPress
menus are not overwritten or automatically created.

## Uninstall And Data Preservation

Deactivation does not delete page meta or reusable components.

`uninstall.php` is conservative. Data is preserved unless the explicit constant
`NEXA_PRO_CORE_DELETE_DATA` is defined as `true` before uninstall.

## Testing

Run standalone validation from the repository root:

```sh
php plugins/nexa-pro-core/tests/validate-storage.php
php plugins/nexa-pro-core/tests/validate-builder-admin.php
php plugins/nexa-pro-core/tests/validate-navigation.php --wp-load="/path/to/site/app/public/wp-load.php"
php plugins/nexa-pro-core/tests/validate-reusable.php --wp-load="/path/to/site/app/public/wp-load.php"
```

Run against a LocalWP installation by passing `wp-load.php`:

```sh
php plugins/nexa-pro-core/tests/validate-storage.php --wp-load="/path/to/site/app/public/wp-load.php"
php plugins/nexa-pro-core/tests/validate-builder-admin.php --wp-load="/path/to/site/app/public/wp-load.php"
php plugins/nexa-pro-core/tests/validate-navigation.php --wp-load="/path/to/site/app/public/wp-load.php"
php plugins/nexa-pro-core/tests/validate-reusable.php --wp-load="/path/to/site/app/public/wp-load.php"
```

The validation scripts create temporary storage and builder test pages and remove
them when the run completes.

## Next Phase

The next scoped phase is Phase 6E: Layout variations and design presets.

Phase 6E may expand layout choices and design presets on top of the Phase 6D
navigation and reusable component APIs. It should not require moving plugin-owned
builder data into theme options.
