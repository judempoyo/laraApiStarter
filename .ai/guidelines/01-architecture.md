# 01-architecture

Use one Action per operation, typed DTOs, FormRequests for validation, and Resources for serialization. Controllers only coordinate these objects. Never return an Eloquent model without a Resource. Use declare(strict_types=1) and Pint. Preserve existing factories and en/fr support.
