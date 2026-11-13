# Database optimization report — Auto Archive (2026-11-13)

Part of an audit of the database usage of all CUZY modules (November 2026). The change was implemented, reviewed,
tested and merged into `main` (merge commit `4bf5b40`) for version 1.0.2.

## Findings and status

| ID | Finding | Status |
|---|---|---|
| AAR1 | Two aggregate queries per space in the daily cron | Done |

## What changed

- `Events::findActiveContainerIds()`: two grouped queries for all the enabled spaces (latest content date, latest
  comment date, `HAVING MAX(...) >= threshold`) instead of two `MAX()` queries per space. The daily cron then only
  archives the spaces not in that list.
- Same rule as before: a space is archived when neither its latest content nor its latest comment is newer than the
  threshold (no content and no comment counts as inactive).
- No migration. An optional core index on `content (contentcontainer_id, created_at)` was not added.

## Tests

New unit suite `AutoArchiveCronTest` (active by content, active by comment, inactive). Suite on `main`: 2 tests, OK.

## Pre-release review

Reviewed the diff against the code before the work: the grouped queries give the same set of active spaces as the
former per-space checks (same tables, same date comparison, enabled spaces only), and they are valid under
`ONLY_FULL_GROUP_BY`. `module.json` (1.0.2) matches the CHANGELOG. No bug found.
