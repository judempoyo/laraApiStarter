---
name: adding-api-module
description: Install or extend an optional API module.
---

Read .api-starter.json and api:list. Run php artisan api:add <module>; inspect included dependencies and api:doctor. Add module files only through its manifest, use User trait initializers, module routes and providers, and optional integration events. Verify add twice, removal and rollback. Never overwrite developer changes.
