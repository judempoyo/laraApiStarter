# 05-modules

Read .api-starter.json before assuming an optional feature exists. Use php artisan api:add rather than copying files. Module manifests are in stubs/modules/<name>/module.json. Prefer events for optional integrations. Removal refuses modified files and required dependencies; database data is retained. Update the module template as the distributable source. Run api:doctor after changes.
