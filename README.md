# WordPress Themes

This repository is a monorepo for commercial WordPress theme development.

Current product:

- Theme: Nexa Pro
- Theme path: `themes/nexa-pro`
- Companion plugin path: `plugins/nexa-pro-core`
- Child theme path reserved for later work: `child-themes/nexa-pro-child`

## Nexa Pro

Nexa Pro is a professional business theme foundation for agencies, consultants,
SaaS teams, startups, recruiters, and service businesses. It currently includes
a configurable parent theme with homepage sections, design tokens, header and
footer settings, media controls, repeaters, presets, settings import/export,
and rollback support.

## Development

Run validation from the repository root:

```sh
scripts/validate-theme.sh
```

Create a local installable theme ZIP:

```sh
scripts/package-nexa-pro.sh
```

The package is written to `dist/` and must not be committed.

## Release Notes

See:

- `themes/nexa-pro/README.md`
- `themes/nexa-pro/CHANGELOG.md`
- `plugins/nexa-pro-core/README.md`
- `docs/NEXA-PRO-RELEASE-CHECKLIST.md`
