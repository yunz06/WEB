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

## 2026-11-19 — UI, Admin access, and deployment readiness

- Fixed category navigation contrast: the masthead's white container no longer overrides the navy category bar; hover, focus, and active states now keep readable text.
- Moved the Admin portal entry from the footer into the masthead beside reader authentication actions.
- Clarified the Admin login journey and explicitly reject reader-role accounts at the Admin portal.
- Documented the local Admin CLI setup and the default seed-only demo account.
- Added Docker deployment support for PHP 8.2 + Apache, a database-aware health endpoint, and persistent upload directory initialization.
- Documented XAMPP request/data flow, MariaDB SQL execution (not MSSQL), Netlify limitations, and a Render Docker deployment path.
- Added regression checks for the Admin entry point, navbar contrast contract, SQL dialect guidance, and deployment configuration.

## 2026-11-19 — Admin access, navigation contrast, and deployment clarity

- Strengthened category navigation base, hover, focus, active, and dropdown color states so labels remain readable on the navy navigation surface.
- Kept the Admin entry in the public masthead rather than relying on a footer-only link; Admin login no longer offers public registration.
- Added owner-confirmed local promotion of an existing User account to Admin through the CLI-only account manager, with secure password entry where supported.
- Clarified that the MariaDB/MySQL update script must be run with a MySQL-compatible client/phpMyAdmin, not the VS Code MSSQL/SQL Server connection.
- Clarified local XAMPP data flow and the production boundary: public deployments use a hosted database, not the developer laptop's `127.0.0.1`.
- Added deployment guidance for PHP Docker hosting and documented why Netlify static hosting cannot directly execute this PHP MVC application.
