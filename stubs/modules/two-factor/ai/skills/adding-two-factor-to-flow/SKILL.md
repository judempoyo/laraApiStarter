---
name: adding-two-factor-to-flow
description: Extend the two-factor module safely.
---

Use LoginChallenged and the two-factor provider. Temporary tokens must only permit verification, expire after five minutes and be rejected on protected API routes. Preserve recovery-code single use and test challenge, completion and invalid-code behavior.
Run composer check-all.
