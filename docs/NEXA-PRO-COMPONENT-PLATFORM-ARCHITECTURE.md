# Nexa Pro Component Platform Architecture

## Product Goal

Nexa Pro should evolve from a fixed homepage-section theme into a component-aware
site-building product while preserving the stable 1.0.0 release behavior.

The platform should eventually support:

- Single-page sites.
- Multipage sites.
- Reusable visual components.
- Repeatable component instances.
- Component-aware navigation.
- Professional admin composition.
- Safe upgrade from the 1.0.0 fixed-section model.

The component platform is split between the Nexa Pro theme and the future Nexa
Pro Core plugin. The theme owns presentation. The plugin owns persistent builder
content and composition data that should survive a theme switch.

## Theme Responsibility

The Nexa Pro theme owns:

- Component presentation registry.
- Component labels, descriptions, default anchors, and template paths.
- Supported layout and design capability declarations.
- Safe frontend rendering helpers.
- Section classes and safe style-property helpers.
- Navigation presentation hooks.
- Legacy fixed-section compatibility mapping.
- Theme-side extension APIs for Nexa Pro Core.
- Fallback rendering when the plugin is unavailable.

The theme must not own persistent reusable site content. It may declare that a
component type supports repeatable or reusable use, but those declarations are
capabilities only.

## Plugin Responsibility

Nexa Pro Core owns future builder and content persistence features:

- Component instance storage.
- Page builder administration.
- Reusable components.
- Reusable component custom post type.
- Page-level component composition.
- Migration execution and migration state.
- Expanded import/export.
- Generated navigation configuration.
- Builder-specific AJAX or REST actions.
- Content ownership that should survive a theme switch.

The plugin may consume the theme registry and extend it through filters, but it
must not require the theme to store plugin-owned content in `nexa_pro_options`.

## Data Ownership

- Global visual theme settings remain in `nexa_pro_options`.
- Legacy fixed-section options remain readable for compatibility.
- Page component instances will ultimately be stored by Nexa Pro Core.
- Reusable components will ultimately be stored by Nexa Pro Core.
- The theme must not own persistent reusable site content.
- The theme may expose registry metadata, render helpers, and compatibility maps.
- Existing fixed-section data must not be deleted automatically.

## Extension Contract

The theme exposes extension points for the future plugin and child themes. Hook
names use the `nexa_pro_` prefix and pass structured arrays.

### `nexa_pro_component_registry`

Filter the full component registry.

Expected value: associative array keyed by component type.

Parameters:

- `$registry`: component registry array.

Return a complete registry array. Extensions may add component types or adjust
metadata, but must preserve safe template paths and stable type identifiers.

### `nexa_pro_component_definition`

Filter one component definition.

Expected value: component definition array.

Parameters:

- `$definition`: component definition.
- `$type`: component type key.

Return the definition array. Return an empty array only when intentionally
removing a component from presentation support.

### `nexa_pro_component_render_data`

Filter render data before a component template is loaded.

Expected value: render data array.

Parameters:

- `$data`: component render data.
- `$type`: component type key.
- `$context`: render context array.

Return sanitized and presentation-ready data. Do not return raw unsanitized
request data.

### `nexa_pro_component_classes`

Filter component wrapper classes.

Expected value: list of class names.

Parameters:

- `$classes`: class-name array.
- `$type`: component type key.
- `$data`: component render data.
- `$context`: render context array.

Return class names only. Unsafe values should be discarded by the theme before
output.

### `nexa_pro_component_style_properties`

Filter safe CSS custom properties for a component wrapper.

Expected value: associative array of CSS custom property names and scalar
values.

Parameters:

- `$properties`: style-property array.
- `$type`: component type key.
- `$data`: component render data.
- `$context`: render context array.

Only CSS custom properties are allowed. Arbitrary CSS declarations are not
allowed.

### `nexa_pro_before_component`

Action fired immediately before a known component is rendered.

Parameters:

- `$type`: component type key.
- `$data`: component render data.
- `$context`: render context array.

### `nexa_pro_after_component`

Action fired immediately after a known component is rendered.

Parameters:

- `$type`: component type key.
- `$data`: component render data.
- `$context`: render context array.

## Rollout Phases

### Phase 6A - Theme Component Presentation Foundation

Scope:

- Presentation registry.
- Render helpers.
- Legacy mapping.
- Extension hooks.
- No new persistent builder storage.

Acceptance criteria:

- All initial component types are registered.
- Registry definitions include safe template paths, default anchors, and
  capability declarations.
- Rendering rejects unknown component types safely.
- Rendering only loads approved theme section templates.
- Legacy fixed-section mapping exists and does not migrate or rewrite data.
- No custom post type, builder admin, or database writes are introduced.
- Existing 1.0.0 frontend output remains unchanged by default.

### Phase 6B - Nexa Pro Core Storage Foundation

Scope:

- Plugin structure.
- Page component storage.
- Component schema.
- Private reusable component storage.
- Capability and nonce rules.
- No full admin builder yet.

Acceptance criteria:

- Component instances are stored outside the theme.
- Stored structures use stable IDs and explicit schema versions.
- Saves require proper capabilities and nonces.
- The plugin can read the theme registry without requiring theme-owned content
  storage.
- The private reusable component custom post type is registered without public
  URLs, public queries, REST exposure, or visible admin screens.
- Uninstall and deactivation preserve component data unless an explicit
  deletion opt-in is provided.

Phase 6B storage details:

- Plugin directory: `plugins/nexa-pro-core`.
- Plugin version: `0.1.0`.
- Schema version: `1`.
- Page component meta key: `_nexa_pro_components`.
- Private reusable component post type: `nexa_component`.
- Reusable component payload meta key: `_nexa_pro_core_component_payload`.
- Migration-state option key: `nexa_pro_core_migration_state`.

Phase 6B exposes helper APIs for page component storage:

- `nexa_pro_core_get_page_components( $page_id )`.
- `nexa_pro_core_save_page_components( $page_id, array $components )`.
- `nexa_pro_core_add_page_component( $page_id, array $component )`.
- `nexa_pro_core_update_page_component( $page_id, $instance_id, array $changes )`.
- `nexa_pro_core_duplicate_page_component( $page_id, $instance_id )`.
- `nexa_pro_core_remove_page_component( $page_id, $instance_id )`.
- `nexa_pro_core_reorder_page_components( $page_id, array $ordered_ids )`.
- `nexa_pro_core_move_page_component( $source_page_id, $target_page_id, $instance_id )`.

Phase 6B exposes helper APIs for reusable component storage:

- `nexa_pro_core_create_reusable_component( array $component )`.
- `nexa_pro_core_get_reusable_component( $post_id )`.
- `nexa_pro_core_update_reusable_component( $post_id, array $changes )`.
- `nexa_pro_core_duplicate_reusable_component( $post_id )`.
- `nexa_pro_core_archive_reusable_component( $post_id )`.
- `nexa_pro_core_delete_reusable_component( $post_id )`.
- `nexa_pro_core_resolve_component_instance( array $instance )`.
- `nexa_pro_core_detach_reusable_component( $page_id, $instance_id )`.
- `nexa_pro_core_get_reusable_usage_count( $post_id )`.

Capability model:

- `manage_nexa_pro_components` controls page component writes.
- `manage_nexa_pro_reusable_components` controls reusable component writes.
- `import_nexa_pro_components` is reserved for future import tools.
- `export_nexa_pro_components` is reserved for future export tools.
- Administrators receive these capabilities on activation.
- Lower roles do not receive them automatically.

Theme integration fallback:

- When the Nexa Pro theme registry is available, the plugin validates component
  types and layout support against the theme helpers.
- When another theme is active, the plugin uses its internal safe allowlist so
  stored data remains readable and editable.
- No hard theme dependency is introduced.

### Phase 6C - Builder Admin

Scope:

- Pages & Components screen.
- Add, edit, duplicate, delete, enable, disable, and reorder actions.
- Page assignment.
- Consistent component editor groups.

Acceptance criteria:

- The admin has a clear Pages & Components composition interface.
- JavaScript enhances the interface but is not the only persistence path.
- Destructive actions are visible, confirmed, and recoverable where practical.
- Component editors are organized as Content, Layout, Design, Navigation, and
  Advanced.

### Phase 6D - Navigation And Reusable Components

Scope:

- Generated navigation.
- Hybrid navigation.
- Linked and local reusable instances.
- Reusable component management UI and workflows.

Acceptance criteria:

- Navigation can be generated from component instances.
- Existing WordPress menu mode remains available.
- Reusable components are stored by Nexa Pro Core, not the theme.
- Linked instances and local copies are clearly distinguished.
- Recursive reusable references are prevented.

### Phase 6E - Layout Variations And Design Presets

Scope:

- Layout variations.
- Inheritance.
- Global design presets.
- Responsive and accessibility work.

Acceptance criteria:

- Layout switching preserves stored content.
- Design presets can be inherited or overridden safely.
- No arbitrary CSS, PHP, or JavaScript can be saved.
- New layouts remain responsive and keyboard accessible.

### Phase 6F - Migration, Import/Export, And Release Validation

Scope:

- 1.0.0 migration.
- Rollback.
- Import/export schema.
- Clean install.
- Upgrade test.
- Documentation.
- Beta release.

Acceptance criteria:

- Migration preview is available before applying changes.
- Migration is idempotent and does not duplicate components.
- Legacy options are not deleted.
- Rollback restores legacy rendering.
- Import/export validates schema, page IDs, media IDs, and component IDs.
- Clean install and upgrade validation pass before beta packaging.

## Safety Rules

- No destructive migration.
- No automatic deletion of legacy options.
- No content loss on theme switch.
- No plugin data stored only in theme options.
- No direct template `get_option()` calls.
- Stable component type identifiers.
- Strict sanitization for saved values.
- Backward compatibility with the 1.0.0 fixed-section model.
- No arbitrary PHP, JavaScript, or unsafe CSS.
- No public frontend URLs for plugin-owned reusable component records unless a
  future explicit product decision allows it.
- Theme rendering helpers must validate component type and template path before
  loading a template.

## Phase 6A Exit Criteria

Phase 6A is complete when:

- The theme has a central presentation registry for the initial component types.
- The registry can be extended through documented filters.
- Component render helpers safely reject unknown types.
- Template resolution is restricted to approved theme section templates.
- Legacy fixed-section mapping helpers are available.
- Validation covers registry completeness, template resolution, unique anchors,
  legacy mappings, extension filters, unknown types, and arbitrary template
  rejection.
- No theme-owned persistent builder storage is introduced.
- No reusable component CPT is registered by the theme.
- No builder admin UI is introduced.
