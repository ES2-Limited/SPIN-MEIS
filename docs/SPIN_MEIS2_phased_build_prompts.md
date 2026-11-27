# SPIN MEIS 2.0: Phased Build Prompts

How to use this file
1. Put these in your repo under `/docs`: the SPIN MEIS 2.0 PRD v1.0, PRD v0.2 (Annex B has the 115-line indicator catalogue), and the SPIN indicator list of 2 Nov 2026.
2. Paste the **Master context** once at the start of a session, or save it as `CLAUDE.md` in the repo root.
3. Run one phase at a time. Paste the phase prompt, let the agent finish, run its acceptance checks, commit, then move to the next phase.
4. Do not start a phase until the previous phase's checks pass.

---

## Master context (paste first, keep for every phase)

You are building **SPIN MEIS 2.0**, the Monitoring and Evaluation Information System for the Sustainable Power and Irrigation for Nigeria (SPIN) Project, for the Federal Ministry of Water Resources and Sanitation. Read `/docs` before you write any code. The PRD is the source of truth; if the PRD and this prompt disagree, stop and ask.

**What the system does.** It holds 115 indicator lines in five categories (Results Framework (PAD) 24, Communication Framework 11, Behaviour Change Communication 26, Grievance Mechanism KPIs 26, Gender & Social Inclusion 28), plus Impact Indicators, River Basin, Procurement, Annual Work Plan & Budget, and Reports. Data enters by three channels: manual entry, API from other systems, and file upload. Every value goes through Draft, Submitted, Verified, Approved before it reaches a report.

**Stack (kept simple and cheap to host; edit if you prefer another).**
- One Laravel 11 application (PHP 8.3) serving both the web screens and the REST API under `/api/v1/`. Screens use Livewire 3, Alpine.js and Tailwind. No separate React front end and no microservices.
- Offline and mobile: the site is a PWA (manifest plus service worker, usable from 360 px width). Only the field forms (manual entry and registers) get offline support, using a small service worker and IndexedDB that sync when online.
- Database: one PostgreSQL database. Staging tables use a `staging_` prefix; the audit log is its own table. No separate schemas.
- Queue and cache: Laravel's database queue and file cache. No Redis. One queue worker and the scheduler run on the same server.
- Auth: Laravel Fortify for email and password with TOTP two-factor (MFA), Spatie Laravel-Permission for roles, with every role assignment tied to an owner scope. Machine clients (source systems) use Sanctum API tokens with scoped abilities, expiry and rotation. Keep sign-in behind an interface so an OpenID Connect provider can replace it later if the FPMU requires single sign-on across IDMIS.
- Hosting target: one server (about 4 vCPU and 8 GB RAM) running Nginx, PHP-FPM, PostgreSQL and the queue worker, with nightly encrypted `pg_dump` backups copied off-site. Docker Compose for local development only.
- Tests: Pest for rules, scope and API; a small Playwright smoke suite for sign-in, entry, approval and upload.
- OpenAPI 3.1 document generated for the public API.

**Rules that apply in every phase**
1. **Record once.** A value that comes from a source system is read-only in MEIS. Corrections happen in the source or through a documented override with reason and evidence.
2. **Ownership and scope.** Every record has an owner: an RBDA or a state, set from its scheme's delivery model. Model 1 schemes are managed by the RBDA; Model 2 schemes are managed by the state. A user sees only records their own unit owns. National users see all records. A World Bank user is national and read-only on approved values. Enforce this in one place (a query scope or policy layer) so lists, dashboards, exports, uploads and API responses cannot bypass it.
3. **Separation of duties.** Submit, verify and approve are separate permissions. One user cannot do two steps on the same record. Verification is by the owner (RBDA for Model 1, state for Model 2). The FPMU M&E Specialist approves. Approved values are locked; changes need a reopening request with a reason.
4. **Lineage and audit.** Every value stores channel (manual, API, upload), source, timestamp, rule version, submitter, verifier and approver. Reads of restricted data (SEA/SH) are logged.
5. **SEA/SH compartment.** Case rows need a dedicated permission. Other roles see aggregates only, with small-cell suppression (counts below a configurable threshold, default 5, show as "fewer than 5").
6. **Privacy.** Never store complainant identity or grievance narrative. Farmer records use pseudonymous IDs. No personal data in logs.
7. **Indicators are data, not code.** Categories, sub-groups, indicators, thresholds and computation rules live in tables and can be edited by an administrator. Adding a category must add a side-menu entry with no code change.
8. **Status rule.** Each indicator has a polarity (higher is better or lower is better) and a type (number, percentage, yes/no, milestone, text). For numbers and percentages: 90% of target or better is On track, 60 to 89% Needs attention, below 60% Off track, using the polarity. An indicator with no target shows **No target**, never Off track. Yes/no and milestone use stage-based status.
9. **Quality bar.** Small commits, tests for every rule above, migrations that run clean from empty, seed data clearly marked as sample, no secrets in the repo, and an updated `/docs/BUILD_LOG.md` at the end of every phase listing what was built, what was deferred and any open question.

**How to work.** At the start of each phase: restate the plan in 10 lines, list assumptions, then build. At the end: run the acceptance checks, report pass or fail for each, and stop. Do not start the next phase.

---

## Phase 0: Foundation

**Goal.** A running skeleton with the design system, environments and CI.

**Build**
- One Laravel repository, Docker Compose for local development (app, PostgreSQL, mail catcher).
- CI that runs lint, tests and a build on every push.
- Livewire layout with sidebar navigation (desktop), bottom navigation (mobile), teal and amber palette, PWA manifest and service worker, empty pages for each menu entry listed in the PRD section 5.
- API health endpoint, OpenAPI generation, standard error format (code, message, field errors, correlation ID), request logging with correlation IDs.
- Environment config for development, staging and production; `.env.example`; a deploy script for the single server.

**Acceptance checks**
- `docker compose up` gives a working web app and API from a clean clone.
- CI is green. The PWA installs on a phone and loads at 360 px width.
- `/api/v1/health` returns OK and appears in the OpenAPI document.

---

## Phase 1: Identity, ownership, access control and audit

**Goal.** Secure sign-in and the access model, before any business data exists.

**Build**
- Users, roles, permissions, and role assignments **within an owner scope** (national, an RBDA, or a state). A user holds a role in a scope, for example Verifier for Kano State or for an RBDA.
- Roles: FPMU M&E Specialist, FPMU category owner (Gender, Communication, Safeguards and GRM, Procurement, Finance), National Coordinator, World Bank (read-only), RBDA data manager, SPIU M&E Officer, state category staff, SEA/SH Focal Person, Administrator. Permissions: view, enter, upload, submit, verify, approve, override, manage indicators, manage users, view restricted, run reports.
- Fortify sign-in with TOTP MFA required for every user, password policy, account lifecycle (invite, activate, disable), quarterly access review report.
- Master data: states, RBDAs, river basins, schemes with delivery model (Model 1 or Model 2), a computed owner for each scheme, and state-level owner records. Admin screens to manage them.
- Central scope layer that filters every query by owner. Audit log (who, what, when, record, before and after) with an admin viewer.

**Acceptance checks (automated tests)**
- A state user cannot read, list, export or call the API for another owner's records. Test with at least two states, one RBDA, one Model 1 scheme and one Model 2 scheme.
- A national user sees all records. A World Bank user cannot create or edit anything.
- One user cannot both submit and verify the same record.
- Audit entries exist for sign-in, role change and every create, update and delete.

---

## Phase 2: Indicator registry and categories

**Goal.** All 115 lines loaded as data, with the five categories on the side menu.

**Build**
- Tables for categories, sub-groups, indicators (code, list number, level, unit, type, polarity, cumulative flag, frequency, source system, data mode: Auto, Semi, Register, Manual, owner), targets by year (2025 to 2029), baselines, disaggregation (sex, age band, disability) and parent-child links for female and youth lines.
- Code prefixes: PDO- and IR- for the Results Framework; CF-, BC-, GM-, GS- for the others. Keep the list number from the 2 Nov 2026 list as a reference field.
- Importer that loads the catalogue from PRD v0.2 Annex B and **produces a validation report**. Known list problems: number 32 appears twice; entries 21 and 32 are the same indicator (keep one as CF-01); entries 7, 9, 17 and 47 have cut-off text; four PAD lines are missing from the list and are kept (IR-4.1, IR-2.4 female and youth, IR-3.3 youth).
- Shared indicators (for example IR-2.3, BC-12 and GS-02) are linked and computed once. Indicators with different definitions stay separate.
- Side menu generated from the categories table. Category page shell with summary tiles, sub-group tabs and an indicator table (code, unit, baseline, target, latest actual, status, mode, source).
- Admin screens to create, rename, order and retire categories and sub-groups, and to edit reference sheets.

**Acceptance checks**
- Catalogue loads with the expected counts per category (24, 11, 26, 26, 28) and the validation report lists every known defect.
- Adding a test category with two sub-groups adds a side-menu entry and tabs with no code change.
- Indicators without targets show No target.

---

## Phase 3: Manual data entry and the approval workflow

**Goal.** Every indicator can receive, verify and approve a value by hand. This phase alone makes Release 1 usable.

**Build**
- Quarterly entry per indicator, with numerator and denominator where relevant, disaggregation, comment and evidence attachments (stored, or linked to the document system).
- Registers for activity-based indicators: communication activity log, media sentiment log, inclusion activities, drills, committees, case studies, community dashboards, sensitisation activities, focal persons and training. Mark interim registers **Bridge** with a planned switch-off.
- Workflow Draft, Submitted, Verified, Approved with the verifier chosen by owner (RBDA for Model 1, state for Model 2). Approved values lock. Reopening request with reason. In-app and email notifications, reminders and escalation after the configured number of days (default 3 working days, to be confirmed).
- Milestone and text indicators (list entries 7, 9, 17 and similar): stages, dated narrative and evidence.
- Indicator detail page: target versus actual chart, quarterly history, lineage, evidence and approval history.
- Status engine implementing rule 8 of the master context.
- **Offline:** field forms queue locally, use client-generated IDs, and sync without duplicates. A locked or approved record is never overwritten by a device. Two edits to one draft go to a conflict queue for the verifier.

**Acceptance checks**
- A full submit, verify, approve cycle works for a Model 1 and a Model 2 record with different verifiers.
- A value entered offline syncs once with no duplicate; an approved record rejects a device edit.
- Status is correct for a higher-is-better, a lower-is-better, a yes/no and a no-target indicator.

---

## Phase 4: File upload

**Goal.** Bulk data in by spreadsheet.

**Build**
- A downloadable Excel or CSV template generated from each indicator or register definition.
- Upload flow: choose file, validate (type, range, code lists, dates, duplicates, owner scope), **preview with row-level errors**, then commit. Nothing is saved until the user confirms.
- Uploaded rows enter as Draft with channel set to upload; keep the original file, user and time as evidence.
- A state user can upload only for their own owner scope; a row for another owner is rejected with a clear message.
- Import history screen with downloadable error report.

**Acceptance checks**
- A file with 3 bad rows shows 3 row-level errors in the preview and saves nothing until the user fixes or removes them.
- A state user's file containing another state's rows is rejected for those rows.
- Re-uploading the same file does not create duplicates.

---

## Phase 5: Dashboard and category pages

**Goal.** The views leadership uses.

**Build**
- Dashboard: headline tiles, PDO progress against current-year and end-of-project targets, category scorecard (on track, needs attention, off track, no target, no data per category), items needing attention (off-track indicators, overdue approvals, overdue grievances, delayed procurement), and global filters for period, state, RBDA and scheme.
- Category pages for all five categories with filters for sex, age band and disability where the indicator carries that disaggregation.
- Everything respects the owner scope: a state user's dashboard shows only their own records; national sees all.

**Acceptance checks**
- Scorecard counts equal the indicator table counts for the same filters.
- Two users in different scopes see different dashboard numbers from the same data.

---

## Phase 6: Project data modules

**Goal.** River Basin, Procurement, AWP&B and Impact Indicators.

**Build**
- **River Basin:** scheme register and four views (by State, Model 1, Model 2, Scheme name). Schemes with missing data are flagged, not hidden. Model labels and the list of states per model are configurable.
- **Procurement:** plan register, planned versus actual milestones, delay in days, delayed packages flagged. Import of the STEP export file matched on package reference.
- **AWP&B:** activities with quarterly phasing, expenditure to date, execution rate and absorption by activity and component, link to indicators.
- **Impact Indicators:** 13 dimensions with baseline (2025), midline (2027) and endline (2029) for treatment and comparison groups, with sample size and confidence interval; difference-in-differences where two rounds exist.

**Acceptance checks**
- Model 1 and Model 2 views show the right owner for each scheme and respect scope.
- A STEP file with a mismatched package reference is reported, not silently dropped.

---

## Phase 7: Reports

**Goal.** Quarterly and annual reports from approved data only.

**Build**
- Quarterly and annual M&E reports: executive summary, scorecard for every category, full Results Framework tables, impact (annual), gender, GRM, communication, procurement, AWP&B, challenges and next priorities.
- Editable narrative sections saved with the report; the report goes through the approval workflow.
- Category reports for the Communication, GRM and Gender owners.
- Export to Word, PDF and Excel; Results Framework tables in the World Bank ISR layout.
- A draft watermark if any unapproved value is included. Optional scheduled generation on the tenth working day after quarter end.

**Acceptance checks**
- A report built from a quarter with one unapproved value carries the watermark; after approval it does not.
- Word, PDF and Excel exports open and match the on-screen tables.

**Release 1 gate.** Phases 0 to 7 together are Release 1: all 115 lines can be entered by hand or upload, verified, approved and reported. Run a full user acceptance test with FPMU and SPIU users and an independent security test before go-live.

---

## Phase 8: Integration layer and indicator engine

**Goal.** Data arrives from other systems by API.

**Build**
- Integration admin: register a source system, credentials, schedule, field mappings, sync logs, re-run a failed sync.
- Receive, stage and validate: pull (`updated_since`), push (`POST /api/v1/ingest/{source}`) and signed webhooks (HMAC-SHA256 with timestamp, retries with back-off). Raw payloads kept in `staging`; records de-duplicated on the source ID; failures quarantined with a reason.
- Machine access: one Sanctum API token per source system with scoped abilities, expiry and rotation, stored hashed. (The PRD names OAuth 2.0 client credentials; confirm with the FPMU that scoped tokens are acceptable, or add Laravel Passport.) Cursor pagination, rate limiting with `Retry-After`, idempotency keys on writes.
- Indicator engine: versioned computation rules (formula, source fields, filters, conversion factors) that write Draft values with links to the source records used. Shared indicators computed once.
- API-fed records are read-only. **Channel precedence:** once a line has a live feed, manual and upload entry for the same records are blocked unless overridden with a reason (both values kept).
- If a source record changes after approval, flag the value **Source changed** and require re-verification.
- Integration health panel, alerts after two failed syncs, reconciliation report, and a fallback to upload when an API is down.
- Public API from MEIS: indicators, approved values with lineage, results summary, schemes, aggregated GRM statistics (no case data), reports and sync status. Owner scope applies to every response.

**Acceptance checks**
- A webhook with a bad signature is rejected; a replayed webhook creates no duplicate.
- A source change after approval flags the value and leaves the approved figure unchanged.
- An API client scoped to one state cannot read another state's data.

---

## Phase 9: Source system connectors

**Goal.** Connect SLIS, the GRM software, BLIS, the survey platform and the others, one at a time. Build each as its own small task; start the next only when the previous is verified.

1. **SLIS adapter.** A thin read-only adapter beside the legacy system, reading from database views through a least-privilege account (never root, never a direct connection from MEIS). Feeds WUAs, members and executives by sex and disability, meetings, elections, conflicts, farmers (pseudonymous IDs), plots, fees, O&M, crops and training. Switch off the matching Bridge registers when the feed starts and run the duplicate check.
2. **GRM software.** Webhook on case create and status change plus a nightly reconciliation pull. Non-identifying fields only. Compute IR-4.1 and the GM KPIs (timeliness, escalation, inclusion, satisfaction). SEA/SH cases arrive flagged and go to the restricted compartment.
3. **BLIS adapter.** Basins, dams, inspections, operating procedures, hydro-met stations, downstream population at risk.
4. **Survey platform.** Aggregates per round and group with sample size and confidence interval, for impact, awareness and knowledge-attitude-practice indicators.
5. **Alert or SMS platform, finance system, STEP export.** Reach of flood alerts, expenditure by activity code, procurement plan.

**For each connector:** write the contract (fields, frequency, identifiers) before code, build against a sandbox, test with sample data, document the lineage for every Auto indicator it feeds, and report which indicators moved from Manual to Auto.

**Acceptance checks**
- For each Auto indicator, the detail page shows the source records and the rule version used.
- At least two-thirds of the 115 lines are updated by API once all connectors are live.
- No value exists in MEIS that duplicates a source-system value, except tagged Bridge registers.

---

## Phase 10: Migration, hardening and handover

**Build**
- Load historical values and records from the TRIMING MEIS2 database with original dates, marked "migrated". Rehearse in staging and sign off on a reconciliation of counts and totals. Remove all sample data.
- Performance tests: 3-second page loads on 4G for 95% of requests, 200 named users, 50 concurrent, 500,000 farmer-linked records, reports within 30 seconds.
- Security: OWASP ASVS Level 2 checklist, independent penetration test with no open high or critical findings, dependency scan, backup and restore test (RPO 24 hours, RTO 8 hours).
- WCAG 2.1 AA check on all user-facing screens.
- Documentation: user, administrator and API manuals; runbook; handover of code and scripts to the FPMU repository.

**Acceptance checks**
- Migration reconciliation report signed off. Penetration test clean. Restore test passed.
- The first quarterly report is produced from the system within 10 working days of quarter end.

---

## Prompt for starting each new session

> Read `CLAUDE.md` (the master context) and `/docs/BUILD_LOG.md`. We are on **Phase N**. Restate the plan for this phase in 10 lines and list your assumptions. Build it, run the acceptance checks, update `BUILD_LOG.md`, and stop. Do not begin the next phase.

## Open decisions to settle before the phases that need them

| Decision | Needed before |
|---|---|
| Which states are Model 1 and which are Model 2 | Phase 1 |
| Whether a Model 1 state's SPIU gets read-only sight of RBDA-managed scheme records (assumed none) | Phase 1 |
| Whether users can see national totals or other owners' aggregates for benchmarking (assumed no) | Phase 5 |
| Hosting provider and identity option | Phase 0 |
| Baselines and targets for the 91 non-PAD indicators | Phase 5 |
| Approval service levels and reminder timing | Phase 3 |
| Small-cell threshold for SEA/SH aggregates (assumed 5) | Phase 1 |
| Survey platform | Phase 9 |
| Extra fields SLIS and the GRM software must capture | Phase 9 |