# Nexa Pro Core

Nexa Pro Core is the companion plugin for Nexa Pro component storage.

Current plugin version: `0.1.0`

Current schema version: `1`

## Purpose

Phase 6B establishes persistent storage APIs for future Nexa Pro component
composition. The plugin owns page-level component data and reusable component
storage so that builder content can survive a theme switch.

The Nexa Pro theme remains responsible for presentation, templates, rendering,
design capabilities, and legacy fixed-section compatibility.

## Current Scope

Included in Phase 6B:

- Plugin bootstrap and activation/deactivation hooks
- Schema metadata
- Page component storage in page meta
- Private reusable component custom post type
- Reusable component payload meta
- Capability registration
- Sanitization and validation
- Helper APIs for storage operations
- Conservative uninstall behavior
- Standalone and WordPress-aware storage validation script

Not included in Phase 6B:

- Builder admin UI
- Visible reusable component screens
- Migration UI
- Legacy settings migration
- Frontend rendering changes
- Import/export UI for component data
- REST or AJAX write endpoints
- Demo page creation

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

## Reusable Component Behavior

Reusable components are private records with no public URLs, rewrite rules,
REST exposure, or visible admin screens in Phase 6B.

Page instances support two inheritance modes:

- `local`: all data is stored on the page instance.
- `linked`: content, design, layout, and advanced settings resolve from the
  reusable component while page placement and navigation remain local.

Recursive and self-referential reusable links are rejected.

## Uninstall And Data Preservation

Deactivation does not delete page meta or reusable components.

`uninstall.php` is conservative. Data is preserved unless the explicit constant
`NEXA_PRO_CORE_DELETE_DATA` is defined as `true` before uninstall.

## Testing

Run standalone validation from the repository root:

```sh
php plugins/nexa-pro-core/tests/validate-storage.php
```

Run against a LocalWP installation by passing `wp-load.php`:

```sh
php plugins/nexa-pro-core/tests/validate-storage.php --wp-load="/path/to/site/app/public/wp-load.php"
```

The validation script creates temporary storage test pages and removes them when
the run completes.

## Next Phase

The next scoped phase is Phase 6C: Builder admin.

Phase 6C may add visible composition screens on top of these APIs. Phase 6B does
not include that UI.
