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

Phase 6C builder details:

- Plugin version: `0.2.0`.
- Schema version remains `1`.
- Builder menu location: `Appearance > Nexa Pro Builder`.
- Builder menu capability: `manage_nexa_pro_components`.
- Builder screen slug: `nexa-pro-builder`.
- Builder data remains in page meta under `_nexa_pro_components`.
- Theme rendering remains in legacy/fixed-section mode unless a later explicit
  builder-rendering mode is added.

The builder screen is intentionally owned by Nexa Pro Core instead of being
embedded into the theme settings page. This avoids fragile coupling to the
theme options UI while still placing the screen under Appearance where site
composition settings naturally live.

Phase 6C request handlers:

- `admin_post_nexa_pro_core_builder_save_component`.
- `admin_post_nexa_pro_core_builder_duplicate_component`.
- `admin_post_nexa_pro_core_builder_toggle_component`.
- `admin_post_nexa_pro_core_builder_move_order`.
- `admin_post_nexa_pro_core_builder_save_order`.
- `admin_post_nexa_pro_core_builder_move_page`.
- `admin_post_nexa_pro_core_builder_delete_component`.
- `admin_post_nexa_pro_core_builder_undo_delete`.

All handlers use authenticated `admin-post.php` requests, capability checks,
page edit checks, nonces scoped to the operation and page, sanitized request
data, and PRG redirects back to the builder screen. Trashed pages are not
editable from the builder. Failed editor submissions can be retained briefly in
a user-scoped transient so the administrator does not lose entered values after
a validation error.

No-JavaScript fallback:

- Page selection uses normal `themes.php?page=nexa-pro-builder` links.
- Add and edit forms submit through regular POST requests.
- Enable/disable uses a visible submit button.
- Move up/down uses server-rendered forms and does not require drag-and-drop.
- Delete uses a required confirmation checkbox and submit button.
- Undo delete uses a server-rendered form and user-scoped transient.

JavaScript is limited to progressive enhancement:

- Drag-and-drop ordering.
- Hidden order input synchronization.
- Accessible `aria-live` announcements.
- Unsaved-change warning for the editor form.

Component editor groups:

- Content: admin title, enabled state, common text fields, CTA fields, and image
  attachment-ID fields.
- Layout: layout variation, alignment, media position, container width, column
  count, and section spacing.
- Design: preset, background, gradient, background image ID, overlay, text
  theme, card style, radius, and shadow.
- Navigation: stored future navigation values such as label, anchor,
  highlight-as-CTA, mobile visibility, and order override.
- Advanced: sanitized CSS class tokens, ARIA label, semantic element, device
  visibility, and animation preset.

The editor uses typed controls and allowlists. It does not expose arbitrary
JSON, CSS, JavaScript, or PHP input.

Delete and undo behavior:

- Delete requires a checked confirmation control.
- The deleted component, source page, and original index are stored in a
  short-lived transient keyed to the current user.
- Undo restores the deleted component to its original page and closest
  available order position.
- Undo data expires safely and is never shared across users.

Reorder behavior:

- Components remain stored while disabled and can still be reordered.
- Move up/down is the keyboard and no-JavaScript path.
- Drag-and-drop only changes the visible order until the user submits Save
  order.
- Save order must include each stored instance ID exactly once.
- Storage normalizes numeric `order` values after save.

Move-to-page behavior:

- The target page must be an editable WordPress page.
- The target write happens before source removal.
- If the target page already has the same instance ID, a new stable ID is
  generated.
- If the target page already has the same anchor, a unique anchor variant is
  generated.
- If source removal fails after target write, the target page is rolled back to
  its previous component collection where possible.

Read-side integration:

- `nexa_pro_core_get_renderable_page_components( $page_id )` returns enabled,
  reusable-resolved components in normalized order.
- `nexa_pro_core_has_builder_components( $page_id )` reports whether builder
  components are stored for a page.
- These helpers are intentionally read-only and do not switch theme rendering.

Accessibility approach:

- The screen uses semantic headings, labelled form controls, real buttons and
  links, focus-visible styling, status notices, and `aria-live` announcements.
- Details/summary controls group the editor without hiding fields from
  keyboard users.
- Drag-and-drop has Move up/down alternatives.
- Destructive actions require visible confirmation.
- Narrow admin layouts collapse to a single column and keep controls visible.

Phase 6C exit criteria:

- Administrators can compose component instances on real editable WordPress
  pages.
- Add, edit, duplicate, enable/disable, reorder, move, delete, and undo actions
  are covered by storage APIs.
- Builder admin assets enqueue only on the builder screen.
- The theme registry is used when available, with a clear fallback notice when
  unavailable.
- No builder data is stored in theme options.
- No page `post_content` is overwritten.
- No generated navigation or reusable component management UI is introduced.
- Theme frontend output remains unchanged by default.
- Standalone and WordPress-aware validation cover the builder workflows.

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

Phase 6D navigation details:

- Plugin version: `0.3.0`.
- Schema version remains `1`.
- Navigation settings option: `nexa_pro_core_navigation`.
- Builder screen location: `Appearance > Nexa Pro Builder > Navigation`.
- Navigation source modes:
  - `wordpress`: preserves the assigned WordPress Primary Menu and injects no
    generated items.
  - `generated`: renders a normalized tree from configured pages and eligible
    component navigation metadata.
  - `hybrid`: preserves WordPress menu order and inserts generated items before,
    after, or in place of a safe placeholder URL.

Generated tree model:

- Configured published pages become page nodes.
- Eligible enabled components become component nodes.
- Single-page mode flattens the selected primary page's eligible component
  nodes into top-level same-page anchor links.
- Multipage mode renders page links with optional component child links.
- Component links use `#anchor` for same-page navigation and
  `/page-slug/#anchor` for cross-page navigation.
- Component order follows stored component order unless
  `navigation.order_override` is set.
- Disabled components, components hidden from navigation, missing anchors, and
  unpublished pages are excluded by default.
- Duplicate item IDs and duplicate URLs are reported by the validation API.

Hybrid insertion:

- WordPress menu records are never rewritten.
- Generated items can be inserted before or after the WordPress Primary Menu
  items.
- Placeholder replacement looks for a WordPress menu URL such as
  `#nexa-generated-navigation` and replaces that item only at render time.
- Duplicate generated URLs already present in the WordPress menu are skipped.
- External and custom WordPress menu items remain unchanged.

Component navigation metadata:

- `show_in_navigation` controls generated eligibility.
- `navigation_label` is used for link text with safe fallbacks to registry
  defaults or the component title.
- `anchor_id` defines the link target.
- `parent_instance_id` can create nested generated items when the parent is
  valid and non-circular.
- `order_override` controls generated item order without changing component
  placement.
- `highlight_as_cta` adds a presentation class only.
- `mobile_visibility` is preserved in normalized tree output.

Phase 6D reusable details:

- Reusable admin location:
  `Appearance > Nexa Pro Builder > Reusable Components`.
- The private reusable CPT remains `nexa_component` with no public URLs,
  queries, REST exposure, or default WordPress admin screen.
- The reusable admin lists title, component type, status, linked-use count,
  pages using the source, updated date, and actions.
- Reusable records can be created from an empty definition, duplicated, archived,
  restored, deleted when safe, inserted into pages, and created from page
  instances.

Linked/local inheritance:

- Linked page instances store `inheritance_mode=linked` and a
  `reusable_component_id`.
- Linked instances resolve content, layout, design, and advanced data from the
  reusable source at read time.
- Page-local navigation and placement remain stored on the page instance.
- Local copies store complete component data and are unaffected by future source
  updates.
- Resolving linked instances does not mutate stored page data.
- A request-level reusable payload cache avoids repeated database reads.

Detach behavior:

- Detaching resolves the effective linked payload first, then saves a local
  component with `inheritance_mode=local` and no reusable ID.
- Page-local navigation and placement are preserved.
- If a source is missing, the stored page instance is retained and converted to
  local so administrators can recover without a fatal error.

Delete/archive rules:

- Reusable sources with linked instances cannot be silently deleted.
- The admin lists affected pages before deletion.
- Administrators can archive the source, restore it, or explicitly detach all
  linked instances before deleting.
- Self-references and circular reusable references are rejected by storage
  helpers.

Theme integration:

- The theme owns header navigation markup, active-state styling, mobile menu
  behavior, and frontend component rendering.
- The plugin exposes normalized settings and tree APIs.
- The theme uses WordPress menu rendering in `wordpress` mode and generated tree
  rendering in `generated` or `hybrid` mode.
- Pages with stored renderable builder components can render known theme
  component templates; pages without builder data keep legacy output.

Phase 6D exit criteria:

- WordPress menu mode preserves existing header behavior.
- Generated and hybrid modes render accessible menu/list markup without inline
  JavaScript.
- Navigation settings save and reset without JavaScript.
- Reusable create, edit, duplicate, archive, restore, insert, detach, and safe
  delete workflows are available through authenticated admin-post actions.
- Linked and local instances are visibly distinguished in the builder.
- Storage, builder, navigation, and reusable validation scripts pass.

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

Phase 6E layout registry details:

- The theme registry is the source of truth for supported layouts, default
  layouts, layout controls, content capabilities, design capabilities, preview
  labels, and responsive notes.
- Core reads the registry when available and falls back to a conservative
  internal allowlist only when the theme registry is unavailable.
- Unknown layout values fall back to the component default layout, not to a
  destructive reset of the component payload.
- Layout-specific fields are stored in the same component payload groups and
  may be ignored by a layout that does not use them. Switching layouts should
  not delete hidden content, design, navigation, or advanced values.

Phase 6E design model:

- Component design stores only a selected preset key and explicit overrides.
- Built-in presets are `inherit`, `light`, `dark`, `brand`, `accent`,
  `minimal`, `elevated`, and `image-overlay`.
- Inheritance priority is:
  1. explicit component override
  2. selected component preset
  3. global design token
  4. theme default
- Safe design tokens cover container width, content width, alignment, media
  position, section spacing, content spacing, item spacing, card density,
  background type, gradient direction, text theme, card style, radius, shadow,
  image style, button style, and column count.
- Arbitrary CSS strings, raw shadows, raw spacing values, unsupported preset
  keys, invalid attachment IDs, and out-of-range overlay opacity values are
  rejected.

Theme rendering structure:

- Builder components are mapped to the existing section templates through
  presentation-ready section data.
- Token classes use allowlisted prefixes such as `nexa-layout--`,
  `nexa-preset--`, `nexa-section-spacing--`, `nexa-card-style--`,
  `nexa-radius--`, and `nexa-shadow--`.
- Background colors, gradients, images, and overlays are applied through safe
  CSS custom properties. Empty style attributes are not emitted.
- Builder-rendered pages without a hero receive a page-title H1. When multiple
  hero components exist, only the first hero receives an H1 by default.
- FAQ accordion markup uses real buttons with `aria-expanded` and
  `aria-controls`; answers remain readable when JavaScript is unavailable.

Reusable and navigation compatibility:

- Linked reusable instances resolve source content, layout, design, and
  advanced values at read time.
- Local copies preserve their own layout/design selections.
- Updating page or reusable components merges nested content, design,
  navigation, and advanced groups before sanitization so hidden layout fields
  are not lost.
- Navigation labels and anchors are not regenerated by layout changes.
- Schema version remains `1` because the stored component shape stays
  compatible.

Phase 6E exit criteria:

- Required layouts exist for Hero, About, Services, Features, Process, Why,
  Portfolio, Testimonials, Team, FAQ, CTA, and Contact.
- Builder Layout and Design controls render only controls supported by the
  selected component definition.
- Presets and token overrides save without arbitrary CSS.
- Layout/design validation, storage validation, builder validation,
  navigation validation, reusable validation, registry validation, and theme
  validation pass.
- Manual browser validation covers representative layouts, presets,
  responsive widths, reusable linked/local behavior, and no-console-error
  checks.

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

Phase 6F migration model:

- Nexa Pro Core owns migration execution and state in
  `nexa_pro_core_migration_state`.
- Migration is never automatic on activation, update, admin page load, or
  frontend render.
- The Migration screen is available at
  `Appearance > Nexa Pro Builder > Migration`.
- Preview reads `nexa_pro_options`, the selected target page, existing builder
  data, and plugin navigation state, then reports the proposed component list
  before any builder data is written.
- Apply supports `merge` and `replace-builder-data`.
- Replace mode can replace only plugin-managed page component meta for the
  selected target page after explicit confirmation and backup.
- Legacy theme options are preserved and remain readable after migration,
  rollback, and reapply.
- Stable migration-generated instance IDs are derived from the legacy source
  hash and component type so repeated previews and applies do not duplicate
  components.
- Unsupported legacy sections such as Trust remain in legacy settings and are
  reported instead of being converted into invalid component types.

Migration states:

- `not_needed`
- `available`
- `previewed`
- `completed`
- `failed`
- `rolled_back`

Migration metadata includes source and target versions, target page ID,
component and field counts, warnings, errors, migration hash, timestamps, and
the latest backup reference. Metadata is status/report information only; it
must not expose private backup payloads in downloadable reports.

Compatibility mode:

- Stored in the plugin-owned `nexa_pro_core_compatibility_mode` option.
- `legacy` mode keeps the Nexa Pro fixed-section rendering path active.
- `builder` mode permits the theme to render plugin-managed page components
  when the selected page has valid renderable builder data.
- Builder mode falls back to legacy rendering when builder data is missing or
  invalid.
- Migration apply does not switch modes unless the administrator explicitly
  chooses that option.
- Rollback restores the previous compatibility mode from the latest backup.

Backup and rollback:

- Before migration apply, Core backs up target page component meta, plugin
  navigation settings, and compatibility mode.
- Rollback restores only the latest migration backup.
- Rollback never deletes or rewrites `nexa_pro_options`.
- Rollback requires capability checks, a nonce, and a visible confirmation
  control.

Import/export model:

- Import/export is owned by Nexa Pro Core and exposed at
  `Appearance > Nexa Pro Builder > Import/Export`.
- Export schema version is `2`.
- Schema version `1` theme settings exports are accepted only for supported
  global settings compatibility.
- Supported scopes are full-site configuration, global settings, navigation,
  one page, selected pages, selected components, reusable components, and
  migration report.
- Exports include a product identifier, export schema, theme version, plugin
  version, timestamp, scope, selected settings, page records, component
  instances, reusable components, navigation settings, and migration metadata
  where applicable.
- Migration report exports exclude private backup payloads.
- Import is preview-first. Uploading or pasting JSON does not apply changes.
- Imports enforce product, supported schema, size, depth, and record-count
  limits.
- Conflict modes are `skip`, `merge`, `replace`, and `create-new`.
- Page imports require explicit mapping, safe slug matching, or create-new
  behavior.
- Create-new mode does not silently map to an existing page with the same slug.
- Reusable component source IDs are remapped to newly created local IDs before
  linked page instances are imported.
- Merge mode repairs duplicate imported anchors before saving.

Phase 6F versioning and packages:

- Nexa Pro theme beta version: `1.1.0-beta.1`.
- Nexa Pro Core beta version: `0.5.0-beta.1`.
- Component storage schema remains `1`.
- Theme package: `dist/nexa-pro-1.1.0-beta.1.zip`.
- Plugin package: `dist/nexa-pro-core-0.5.0-beta.1.zip`.
- Theme ZIP root is `nexa-pro/`.
- Plugin ZIP root is `nexa-pro-core/`.
- Generated ZIP files remain ignored and untracked.

Phase 6F exit criteria:

- Clean install from theme and plugin ZIPs succeeds.
- Upgrade from Nexa Pro 1.0 fixed-section data preserves legacy frontend output
  until migration and compatibility mode are explicitly selected.
- Migration preview, apply, rollback, and reapply are manually tested.
- Import/export preview, conflict handling, malformed rejection, old-schema
  compatibility, and exported package structure are tested.
- Frontend builder rendering, generated navigation, reusable resolution,
  responsive widths, accessibility smoke checks, console checks, and PHP logs
  are reviewed before beta PR merge.

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
