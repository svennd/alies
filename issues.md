# Issues

- Legacy `lab_detail` fields `sample_id`, `report`, `comment`, `lab_updated_at`, and the original uncategorized `lab_code_text` have no dedicated destination in `lab_results`, so the migration can only preserve the normalized result value, unit, and reference range.

- The current detail view still contains old-schema references such as `$lab_info['lab_id']` for the Medilab link, while the new schema stores that external identifier in `source_id`. That page can show a broken outbound link for migrated reports until it is updated.
- `application/controllers/Lab.php` still has `list_lab()` reading from `$this->lab`, but the legacy lab model is no longer loaded in the controller. That route is currently inconsistent with the API-backed lab implementation.
