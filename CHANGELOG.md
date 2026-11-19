# Changelog

## 2026-11-18 — Completion and safety pass

- Completed public reader flows for home, published article detail, category pages, search, authentication, and approved comments.
- Added active-category visibility checks, latest-story deduplication, category sections, related stories, pagination states, and local image fallbacks.
- Aligned Admin shell typography and responsive styling with the public system; added confirmation for destructive forms.
- Protected project metadata and private source paths from direct Apache access; added ignore rules for secrets and local artifacts.
- Added a read-only UTF-8 repair preview utility and tests. Live recovery remains approval-gated.
- Added architecture, database, development, deployment, QA, and visual-QA documentation.
- Browser visual QA remains blocked by renderer error 10054; no screenshot or browser inspection is claimed.

## 2026-11-19 — Product journey and acceptance evidence

- Added reader account overview and owner-filtered, paginated comment history.
- Added latest-news pagination while excluding featured stories from the latest list.
- Added Admin shell navigation, dashboard shortcuts, and CSRF-backed logout rendering.
- Kept the Admin editor usable when TinyMCE CDN initialization fails.
- Added user journey, assignment matrix, demo walkthrough, and final acceptance report.
- Verified Apache route and authorization behavior, protected local notes, MariaDB read-only state, PHP lint, and PHP tests.
- Browser visual QA, external font/CDN loading, upload round-trip, and rich-text save/read remain unverified.
