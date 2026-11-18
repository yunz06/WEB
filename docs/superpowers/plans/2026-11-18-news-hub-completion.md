# NEWS HUB Completion Plan

> **For agentic workers:** Execute inline; run tests and preserve each existing change.

**Goal:** Complete the existing PHP/MariaDB NEWS HUB for local demonstration with secure reader, admin, content, and comment flows.

**Architecture:** Keep the current MVC router, PDO singleton, and six tables. Add only schema fields already specified by the master prompt; use an additive migration for the live database and an updated fresh-install schema. Keep admin fail-closed until session auth is implemented.

**Tech Stack:** PHP 8.2, MariaDB 10.4.32, PDO, HTML/CSS/JS, Bootstrap 5.

**Spec:** User attachment “MASTER PROMPT — COMPLETE NEWS HUB PROJECT”.

## Global Constraints
- Preserve existing source edits and data.
- Keep exactly six tables and existing table/column names.
- No DROP, TRUNCATE, external news APIs, backend frameworks, or fake verified news.
- Run a DB backup before migration.
- Verify with PHP CLI and the local Apache host where available.
- Report actual pass/fail/blocked/not-tested status.

## Review Focus
- Published records must satisfy status and published_at checks on every public path.
- Session identity and role must come from DB; never trust form/session role IDs without validation.
- CSRF protects every state-changing request.
- Rich text and uploaded assets must not execute code or escape uploads path.
- Pagination, category trees, and search must use prepared statements and bounded input.
- Seed import must be repeatable and retain six tables.

### Task 1: Baseline and backup
- Capture git status and current DB schema/counts.
- Create and verify a local mysqldump backup without exposing credentials.
- Record pre-change files and DB status.

### Task 2: Schema and migration
- Write failing integration assertions for the additive fields and indexes.
- Update fresh-install SQL and add idempotent migration.
- Apply migration only after backup; verify exact six tables, columns, FKs, indexes, and existing rows.

### Task 3: Auth and admin gate
- Test registration/login/logout, role checks, locked users, and CSRF.
- Implement User model, AuthController/view, session hardening, and router auth/admin gates.
- Keep registration role fixed to user; admin users manage account status/roles without deleting accounts.

### Task 4: Content models and admin
- Test article/category/image/comment CRUD paths and validation.
- Add missing model methods, preserve existing method names, connect authorship to authenticated admin, enforce slug uniqueness and publication schedule.
- Complete dashboard, article editor/list, category tree, user and comment management views.
- Harden image upload, deletion, and upload-directory execution rules.

### Task 5: Reader flows
- Test homepage, details, category filter/pagination, search, and comments.
- Add public category/search/comment controllers/views and update homepage links while keeping the incumbent visual system.
- Public results show only published records whose publication time has arrived; comments show only approved.

### Task 6: Seed and assets
- Create a rerunnable seed for 10 parent categories, 15–20 child categories, 40–60 clearly marked sample articles, 5–10 featured items, draft items, and comments in each status.
- Use local generated/owned images and a documented local-only demo admin account with a password hash.
- Run seed twice and verify counts remain stable.

### Task 7: QA and documentation
- [x] Run PHP lint, unit/integration/security checks, DB checks, and HTTP route checks.
- [x] Inspect responsive/layout code. Real browser remains blocked by known renderer failure; did not retry Edge/SwiftShader.
- [x] Write README, `PROJECT_COMPLETION_REPORT.md`, and `TEST_REPORT.md` with evidence statuses and remaining blockers.

Execution result: implementation and CLI/Apache checks complete. Browser visual QA remains blocked and is not claimed complete.

