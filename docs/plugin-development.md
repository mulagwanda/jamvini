# JamVini Plugin Development

JamVini plugins live in `plugins/{PluginFolder}` and are discovered through a `plugin.json` manifest.

## Minimal Structure

```text
plugins/UnderConstruction/
  plugin.json
  routes.php
  hooks.php
  migrations/
  src/
    Controllers/
    Middleware/
    Models/
  views/
    admin/
    public/
```

## Manifest Example

```json
{
  "name": "Under Construction",
  "slug": "under-construction",
  "version": "1.0.0",
  "description": "Shows a temporary under-construction page.",
  "author": "Your Company",
  "type": "module",
  "core": false,
  "dependencies": [],
  "min_core_version": "1.0.0",
  "menu": {
    "admin": {
      "icon": "construction",
      "label": "Under Construction",
      "route": "admin.under-construction.index",
      "position": 90,
      "section": "system"
    }
  },
  "permissions": ["manage_under_construction"],
  "middleware": {
    "public": [
      "Plugins\\UnderConstruction\\src\\Middleware\\UnderConstructionMiddleware"
    ]
  },
  "hooks_provides": ["under_construction.enabled"],
  "hooks_listens": []
}
```

## Supported Extension Points

- `routes.php`: Loaded automatically when the plugin is active.
- `hooks.php`: Loaded automatically when the plugin is active.
- `migrations/`: Run when the plugin is activated or force-migrated.
- `views/`: Available as `plugins.PluginFolder::...` and `plugins.plugin-slug::...`.
- `menu.admin`: Registers admin sidebar navigation.
- `menu.client`: Registers client portal navigation.
- `permissions`: Registers plugin permission keys.
- `middleware.global`: Runs for every request.
- `middleware.public`: Runs for public website requests.
- `middleware.client`: Runs for client portal requests.
- `middleware.admin`: Runs for admin requests.
- `middleware.api`: Runs for API requests.

Future plugins should declare middleware in `plugin.json`. They should not require editing `bootstrap/app.php`.

