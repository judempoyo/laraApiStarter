---
name: making-model-exportable
description: Extend the export module safely.
---

Implement ExportableInterface, register the adapter in config/export.php and explicitly scope queries to the requesting user. Admin-only resources must use the api.admin gate. Preserve media-backed storage, queued execution and completion events.
Run composer check-all.
