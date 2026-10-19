---
name: adding-webhook-event
description: Extend the webhooks module safely.
---

Define an IntegrationEvent subclass in the emitting module. Add its name to WebhookEvent in webhooks. Preserve signature verification, delivery retry limits and payload privacy. Test delivery and retry behavior. Never call WebhookDispatcher from another module.
Run composer check-all.
