---
name: adding-translations
description: Add matching English and French API translations.
---

Inspect lang/en and lang/fr. Add the same keys in both locales and use __() rather than literal messages. Module-only keys belong in its lang/<locale>/modules/<name>.php template. Verify both locales and missing-key behavior.
