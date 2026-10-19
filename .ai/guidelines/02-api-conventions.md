# 02-api-conventions

Use ApiResponse, ErrorCode and ApiException consistently. Read config/api.php for the version and pagination limits. Controller namespaces use Api\V1; the HTTP prefix uses v1. Translate client-facing strings in en/fr; do not hardcode messages. Scramble generates the only API specification; Scalar serves /docs/api.
