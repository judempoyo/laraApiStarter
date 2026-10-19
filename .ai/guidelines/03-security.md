# 03-security

Never disable security middleware to make a test pass. Protect authenticated endpoints, ownership checks and rate limits. Never log tokens, secrets or passwords. Documentation is denied outside local unless explicitly configured. Do not add a dependency without asking the developer. Use Boost database-schema and log tools before writing migrations or diagnosing failures.
