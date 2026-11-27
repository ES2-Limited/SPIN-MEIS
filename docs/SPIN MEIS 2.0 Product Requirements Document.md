# SPIN MEIS 2.0: Product Requirements Document

Version 1.0 (draft) · 27 November 2026 · Prepared by Technical Product Management for the FPMU · Baseline: SPIN MEIS 2.0 PRD v0.2 and the SPIN indicator list of 2 November 2026

## 1. Summary

SPIN MEIS 2.0 is the one place where the FPMU, SPIUs, RBDAs and the World Bank see verified SPIN results. It replaces hand-keyed indicator forms with a platform that receives data from SLIS, BLIS, the GRM software and the survey platform by API or file upload, accepts manual entry where no feed exists, computes indicators with versioned rules, and sends every value through verify, then approve, before it reaches a report.

- **Scope:** 115 indicator lines in five categories (24 Results Framework lines reported to the World Bank, 91 communication, behaviour change, grievance and gender lines), plus Impact Indicators, River Basin, Procurement, AWP&B and Reports.
- **Design baseline:** the working prototype on the IDMIS 2.0 template.
- **What this PRD adds to v0.2:** a review of both source documents (section 2), a re-sequenced release plan that delivers value before every integration is ready (section 10), testable user stories (section 6), and a decision log with owners (section 13).
- **Recommendation:** release in three increments. Release 1 gives all 115 lines manual entry, approval, lineage and reporting. Releases 2 and 3 add automation source by source. Do not commit to the two-thirds automation target at go-live; commit to it at the end of Release 3.

## 2. Review findings

Both documents are strong on content. The v0.2 PRD covers indicators, API standards and access control in depth. The findings below are the gaps a build team would hit first.

| # | Finding | Impact | Resolution in this PRD |
| --- | --- | --- | --- |
| F1 | The indicator list has numbering and text defects: number 32 is used twice (Communication and Behaviour Change); entries 21 and 32 carry the same text; entries 7, 9, 17 and 47 are cut off; entry 19 has a stray annotation; entries 18 and 19 are highlighted without explanation. | Ambiguous source of truth for 111 distinct indicators. | Keep one record for the duplicate (CF-01); list owner to issue a corrected list (D-7, D-12). |
| F2 | Four Results Framework lines reported to the Bank (IR-4.1, IR-2.4 female and youth, IR-3.3 youth) are missing from the list. | ISR reporting gap if the list is treated as final. | Retained in MEIS pending Bank confirmation (D-8). |
| F3 | The 91 non-PAD indicators have no baseline, target, frequency or formula. | Status colours, dashboards and the scorecard cannot be computed. | New status of No target (NEW-2); approved reference sheets become a release gate (D-9). |
| F4 | Record once conflicts with interim MEIS registers (BCC-03, GRM-05, GRM-08), and success measure 3 says zero duplicates. | Contradiction the first auditor will find. | Interim registers are flagged as bridges with a sunset date and a duplicate check (NEW-3). |
| F5 | Critical path risk: SLIS and BLIS have no APIs and connect as root; the GRM software lacks fields needed for GM-06 to GM-16; the survey platform is undecided (D-6). Phase 2 assumed six weeks. | The automation target slips and drags the first report with it. | Releases are sequenced so Release 1 does not depend on any integration (section 10). |
| F6 | The status rule (90% and 60% bands) assumes higher is better and numeric. | Wrong colour for lower-is-better KPIs (overdue grievances, escalation rate, open grievances past timeline) and meaningless for Yes/No, milestone and text indicators. | Polarity and indicator-type rules (NEW-1, NEW-10). |
| F7 | Privacy rules conflict: API-09 forbids names and phones, while DQ-02 de-duplicates on name hash, date of birth and phone hash. | Either the duplicate check fails or personal data leaks. | Keyed hash computed at the source, never reversible in MEIS (NEW-7). |
| F8 | Aggregates from the SEA/SH compartment can identify a case where counts are small. | Survivor re-identification risk. | Small-cell suppression (NEW-4). |
| F9 | Offline capture is a Must, but conflict handling and locked records are unspecified. | Duplicate or lost field entries. | Offline sync rules (NEW-5). |
| F10 | The 10-working-day reporting target has no service level for verification or approval, and scheduled generation (REP-05) is only a Could. | The headline success measure is not testable. | Approval service levels (NEW-6); REP-05 raised to Should. |
| F11 | ID hygiene: GEN is used both for common requirements and for gender requirements (GEN-I-xx); some IDs are out of order (DSH-07, REP-06, IMP-05). | Traceability errors in test plans. | Prefixes renamed CMN and GSI in this document; v0.2 IDs kept in the Ref column. |

## 3. Problem, goals and non-goals

**Problem.** Under TRIMING, indicator values were keyed by hand through separate forms, so the same figure could be entered twice and disagree between modules. Quarterly reporting is slow, lineage is weak, and access control is coarse. SPIN adds four more indicator families (communication, behaviour change, grievance, gender and inclusion) with different owners, which raises the cost of the old approach.

**Goals and measures** (measured six months after go-live unless stated):

| # | Goal | Measure |
| --- | --- | --- |
| G1 | Automate data capture | At least two-thirds of the 115 lines (78 designed as Auto or Semi) updated by API by the end of Release 3 |
| G2 | Faster reporting | Quarterly M&E report produced within 10 working days of quarter end |
| G3 | One trusted figure | No value entered in MEIS that is also held in SLIS, BLIS or the GRM software, except flagged bridge registers |
| G4 | Controlled access | Every user mapped to a role and data scope; no shared accounts; quarterly access reviews done |
| G5 | Traceable results | Every approved value carries source, timestamp, rule version and approver |
| G6 | Works in the field | All screens usable at 360 px width; field forms usable offline |

**Non-goals.** Rebuilding SLIS, BLIS, the GRM software or ERDMS; financial accounting or IFRs; grievance case handling (MEIS holds statistics and status, not case files); direct submission to STEP or Bank systems.

## 4. Users and jobs to be done

| User | Main job | What success looks like |
| --- | --- | --- |
| FPMU M&E Specialist | Own the Results Framework; verify, approve and report | One scorecard showing what needs attention; reports built from approved data |
| SPIU M&E Officer | Capture, review and submit state data | Enter a quarter of manual data for one state in under 30 minutes |
| RBDA data manager | Check scheme and basin data flowing from SLIS and BLIS | Gaps flagged, not hidden |
| GRM / Social Safeguards Officer | Monitor grievance KPIs | Timeliness and inclusion KPIs update as cases change |
| SEA/SH Focal Person | Confidential oversight | Case rows visible only to this role; every read logged |
| Gender Specialist, Communication Officer | Log activities; monitor their category | Registers work offline; category reports on demand |
| Procurement and Finance staff | Update milestones; reconcile spend | Variance against plan visible |
| National Project Coordinator | Decide and approve | Dashboard and report approval |
| World Bank Task Team | Prepare ISR and missions | Read-only access to approved results in ISR layout |
| Source-system owners | Exchange data | Clear API contract and sandbox |
| ICT / System Administrator | Run and secure the system | Users, roles, integrations, backups, audit in one console |

## 5. Product scope and information architecture

The side menu has five indicator categories, each managed as data so a new category appears without a code release.

| Menu group | Entry | Lines | Sub-groups |
| --- | --- | --- | --- |
| Overview | Dashboard | n/a | Category scorecard; items needing attention |
| Indicator categories | Results Framework (PAD) | 24 | PDO; Intermediate results; Quarterly entry |
|  | Communication Framework | 11 | Awareness and reach; Engagement and feedback; Media, compliance, protocols; WUA communication |
|  | Behaviour Change Communication | 26 | Knowledge, attitudes, norms; Practice adoption; WUA participation; Conflict mediation; Capacity and digital tools |
|  | Grievance Mechanism KPIs | 26 | Awareness and access; Timeliness; Inclusion; SEA/SH (restricted); Satisfaction and review |
|  | Gender & Social Inclusion | 28 | Women's participation; Disability inclusion; Women's economic empowerment; Institutional capacity |
|  | Impact Indicators | n/a | Baseline, midline, endline (13 dimensions) |
| Project data | River Basin; Procurement; Annual Work Plan & Budget | n/a | By State, Model 1, Model 2, Scheme |
| Reporting | Reports | n/a | Quarterly; Annual; Category |
| Administration | Users and roles; Integrations; Data and backup | n/a | Categories; sync logs; audit log |

**Rules that apply across categories**

- Shared indicators (for example IR-2.3, BC-12 and GS-02 on women in WUA leadership) are computed once and shown wherever they appear, with a visible link.
- Indicators with different definitions stay separate but share source data (CF-03 uses five-day acknowledgement; GM-06 uses 24 hours).
- Only the 24 Results Framework lines are reported to the Bank; the other categories go into M&E reports and their own category reports.

## 6. Requirements

Priority uses MoSCoW: Must is required for go-live of the stated release, Should follows soon after, Could is optional. Ref points to the requirement in v0.2. Items marked NEW come from the review.

### 6.1 Indicator registry and engine

| ID | Requirement | Priority | Release | Ref |
| --- | --- | --- | --- | --- |
| IND-1 | Registry holds code, list number, level, category, sub-group, name, unit, value type, baseline, annual targets, frequency, source, owner, cumulative flag and data mode (Auto, Semi, Register, Manual) | Must | R1 | RF-01, IND-03, IND-05 |
| IND-2 | Disaggregated lines (female, youth) are children of their parent | Must | R1 | RF-02 |
| IND-3 | Computation rules are configurable and versioned so earlier results can be reproduced | Must | R2 | RF-08 |
| IND-4 | Shared indicators computed once, shown in each category | Must | R2 | IND-04 |
| NEW-1 | Each indicator has a polarity (higher is better, lower is better) and a type (numeric, percentage, Yes/No, milestone, text). Status bands apply by polarity; Yes/No and milestone use stage-based status | Must | R1 | RF-05 |
| NEW-2 | Indicators without a target show No target, never Off track, and are counted separately in scorecards | Must | R1 | IND-02 |
| NEW-10 | Text and milestone indicators (list entries 7, 9, 17 and similar) hold stages, a dated narrative and evidence documents | Must | R1 | n/a |
| NEW-8 | Import tool that loads the indicator list, flags duplicates and truncated text, and produces a validation report | Should | R1 | n/a |

### 6.2 Approval, lineage and audit

| ID | Requirement | Priority | Release | Ref |
| --- | --- | --- | --- | --- |
| APR-1 | Workflow Draft, Submitted, Verified, Approved; approved values are locked; changes need a reopening request with a reason | Must | R1 | RF-06 |
| APR-2 | Maker and checker must be different people for each step | Must | R1 | Scenario 4 |
| APR-3 | A source change after approval flags the value as source changed; it is not altered silently | Must | R2 | DQ-03 |
| APR-4 | Override of an automatic value needs a reason and evidence; both values are kept and reported as overridden | Must | R2 | DQ-04 |
| APR-5 | Indicator detail shows target versus actual, quarterly history, lineage, evidence and approval history | Must | R1 | RF-07 |
| NEW-6 | Proposed service levels: verify within 3 working days and approve within 3 working days of submission, with reminders and escalation to the next role (values to be confirmed by the FPMU) | Should | R1 | n/a |
| NEW-9 | Each value shows data freshness (last source update and last sync) | Should | R2 | n/a |

### 6.3 Category pages

| ID | Requirement | Priority | Release | Ref |
| --- | --- | --- | --- | --- |
| CAT-1 | Each category page shows summary tiles, sub-group tabs and an indicator table (code, unit, baseline, target, actual, status, mode, source) | Must | R1 | IND-02 |
| CAT-2 | Category management without code changes | Must | R1 | IND-01, ADM-06 |
| CAT-3 | Survey-based indicators show round, sample size and confidence interval | Should | R3 | IND-08 |
| COM-1 | Communication activity log (type, state, audience, reach, women reached, cost, evidence) and media sentiment log feed CF-05 and CF-06 | Must | R1 | COM-01, COM-02 |
| BCC-1 | Configurable, versioned thresholds (attitude cut-off, capability threshold, qualifying digital action, mediation standard) | Must | R1 | BCC-05 |
| BCC-2 | Offline registers for drills, committees, case studies and community dashboards, each with photo or document evidence | Must | R1 | BCC-04 |
| GRM-1 | Case data from the GRM software stores reference, dates, state, scheme, category, tier, channel, complainant sex, status and timestamps; never identity or narrative | Must | R2 | GRM-01 |
| GRM-2 | IR-4.1 and GM-07 computed for any period; IR-4.1 timeframe aligned to 30 days (D-8) | Must | R2 | GRM-02 |
| GSI-1 | Register of inclusion activities (WUA elections, trainings, enterprise support) with sex, age band and disability disaggregation | Must | R1 | GEN-I-01 |
| NEW-3 | Interim MEIS registers are tagged Bridge with a planned switch-off; when the SLIS or GRM feed starts, the bridge is switched off and a duplicate check runs | Must | R1 | BCC-03, GRM-05 |

### 6.4 Dashboard and reports

| ID | Requirement | Priority | Release | Ref |
| --- | --- | --- | --- | --- |
| DSH-1 | Headline tiles, PDO progress with status, category scorecard, items needing attention | Must | R1 | DSH-01 to DSH-04, DSH-07 |
| DSH-2 | Integration health panel (last sync, record count) | Should | R2 | DSH-05 |
| REP-1 | Quarterly and annual reports from approved values only; draft watermark if anything unapproved is included | Must | R1 | REP-01, REP-04 |
| REP-2 | Export to Word, PDF and Excel; Results Framework tables in ISR layout | Must | R1 | REP-03 |
| REP-3 | Category reports and a scorecard section in the quarterly and annual packs | Must | R1 | REP-06, REP-07 |
| REP-4 | Scheduled generation of the quarterly pack on the tenth working day after quarter end | Should | R2 | REP-05 |

### 6.5 Project data modules

| ID | Requirement | Priority | Release | Ref |
| --- | --- | --- | --- | --- |
| IMP-1 | Register of 13 impact dimensions with baseline (2025), midline (2027), endline (2029) for treatment and comparison groups | Must | R1 | IMP-01, IMP-02 |
| RB-1 | Scheme register with four views (State, Model 1, Model 2, Scheme name); schemes with missing data flagged, not hidden; Model 1 (managed by the RBDA) and Model 2 (managed by the state) set the record owner; labels and state lists configurable (D-3) | Must | R1 | RB-01 to RB-04 |
| PRC-1 | Procurement plan register with planned and actual milestones; delayed packages flagged; STEP export import | Must / Should | R1 / R3 | PRC-01 to PRC-03 |
| AWP-1 | Activities with quarterly phasing, execution rate and absorption; expenditure from finance by file or API | Must / Should | R1 / R3 | AWP-01 to AWP-03 |

### 6.6 Offline and mobile

| ID | Requirement | Priority | Release | Ref |
| --- | --- | --- | --- | --- |
| MOB-1 | Installable PWA, usable from 360 px | Must | R1 | NFR-05 |
| NEW-5 | Offline sync rules: client-generated record IDs prevent duplicates; locked or approved records are never overwritten by the device; edits to the same draft by two users go to a conflict queue for the Verifier | Must | R1 | n/a |

### 6.7 Data capture: manual entry, API and file upload

Every indicator line and register accepts data through three channels, and each value records which channel produced it.

| Channel | How it works | Used for | Rules |
| --- | --- | --- | --- |
| Manual entry | Web or phone form per indicator or register, online or offline, with evidence attached | Register and Manual lines (activity logs, milestones, drills, committees) and gaps where no feed exists | Enters as Draft, then Verify and Approve; the submitter cannot verify own value |
| API | MEIS pulls from source-system APIs, or the source pushes to the MEIS API or a webhook | SLIS, GRM software, BLIS, survey platform, alert platform, finance | Staged and validated; failures quarantined with a reason; API values are read-only in MEIS |
| File upload | Excel or CSV from the MEIS template, or an export from a source system | Systems with no API (STEP, legacy exports), bulk state data, historical migration | Validate, preview, then commit; the file is kept as evidence; enters as Draft |

| ID | Requirement | Priority | Release | Ref |
| --- | --- | --- | --- | --- |
| CAP-1 | Every indicator and register has a downloadable upload template generated from its definition | Must | R1 | GEN-05 |
| CAP-2 | Upload runs validate, preview, commit: type, range, code-list, date and duplicate checks with row-level messages; nothing is saved until the user confirms | Must | R1 | GEN-05 |
| CAP-3 | Uploaded and API values enter as Draft with source set to upload or API; the file, user and time are kept | Must | R1 | GEN-02 |
| CAP-4 | Channel precedence: once a line has a live API feed, manual and upload entry for the same records are blocked unless an override with reason is used | Must | R2 | DQ-04 |
| CAP-5 | Uploads and API clients are limited to the user's or client's data scope; a state user can upload only for their own state | Must | R1 | RBAC |
| CAP-6 | Administrator registers each source system, sets credentials, schedule and field mapping, views sync logs and re-runs a failed sync | Must | R2 | ADM-02 |
| CAP-7 | If an API is down, an authorised user can upload the source export; it is reconciled when the API resumes | Should | R2 | GRM-05 |

## 7. User stories and acceptance criteria

| ID | As a | I want | So that | Acceptance criteria |
| --- | --- | --- | --- | --- |
| US-1 | FPMU M&E Specialist | a scorecard of all five categories | I see where to act first | One row per category with on track, needs attention, off track, no target and no data counts; each row links to its category page |
| US-2 | SPIU M&E Officer | to enter a manual value on a phone without signal | field work is not blocked | Entry saved on device, synced once, no duplicate when connectivity returns |
| US-3 | Verifier | to verify values I did not enter | the data has a second check | The submitter cannot verify or approve own value; both steps recorded in the audit trail |
| US-4 | FPMU M&E Specialist | to be warned when a source record changes after approval | reports do not quietly change | Value flagged source changed; figure unchanged until re-verified |
| US-5 | GRM Officer | KPIs to update as cases are resolved | I see timeliness without re-keying | Case status and IR-4.1 update within the near-real-time target; no complainant identity in MEIS |
| US-6 | SEA/SH Focal Person | exclusive access to case rows | survivors are protected | Others see aggregates only, with small-cell suppression; every read is logged |
| US-7 | Administrator | to add an indicator category | new frameworks need no release | New entry and sub-group tabs appear in the side menu for permitted roles |
| US-8 | Administrator | an alert when an integration fails twice | stale data is caught early | Alert raised; dashboard panel shows the failure and last good sync |
| US-9 | World Bank Task Team member | read-only access to approved results in ISR layout | I can prepare the ISR without asking for files | Only approved values visible; export matches ISR layout |
| US-10 | SPIU M&E Officer | to upload a state spreadsheet using the MEIS template | bulk data does not need re-typing | Row-level errors shown before commit; nothing saved until confirmed; accepted rows enter as Draft; rows for other states are rejected |
| US-11 | State user | to see only my own state's data | other states' records stay protected | Lists, dashboards, exports, uploads and API responses show own-state records only; a national user sees all states |

## 8. Integrations

Source systems stay the systems of record. MEIS reads from them and never writes back, except an optional receipt acknowledgement.

| Source | Data to MEIS | Method | Frequency | Release | Dependency |
| --- | --- | --- | --- | --- | --- |
| SLIS | WUAs, members and executives by sex, age and disability; meetings; elections; conflicts; farmers; plots; fees; O\&M; crops; training | REST pull with updated\_since; webhook on approvals | Daily; near real time for approvals | R2 | Adapter with read-only database views; SLIS must capture meeting registers, conflict log and disability status |
| GRM software | Non-identifying case fields, timestamps, escalation, appeals, corrective actions, satisfaction, SEA/SH flag | Webhook plus nightly reconciliation | Near real time | R2 | GRM owner must add fields listed in GRM-06 |
| BLIS | Basins, dams, inspections, procedures, hydro-met stations, downstream population | REST pull | Daily | R3 | Adapter beside legacy system |
| Survey platform | Aggregates per round for impact, awareness and knowledge-attitude-practice | REST or file | Per survey round | R3 | Platform decision (D-6) |
| Alert or SMS platform | Messages sent, delivered, unique recipients per community (CF-02) | REST | Weekly | R3 | Owner to be identified |
| Finance and STEP | Expenditure by activity code; procurement plan | Scheduled file or API; STEP export | Monthly | R3 | STEP has no public API |
| ERDMS | Evidence documents | Link or store | On upload | R1 | Repository access |

**API standards (unchanged from v0.2):** REST over TLS 1.2 or higher, OpenAPI 3.1 with a sandbox, OAuth 2.0 client credentials with one client per system, URL versioning with six months of overlap, incremental sync, cursor pagination, rate limiting, idempotency keys on writes, signed webhooks with retries, and a call log kept for 12 months.

**Amended by this review:**

- NEW-7: farmer identifiers are pseudonymous and the duplicate-check key is a keyed hash computed in the source system. MEIS cannot reverse it.
- Legacy adapters must use a least-privilege read-only database account. No direct database connection from MEIS. Replacing root access is a prerequisite to Release 2 (see risks).

## 9. Access control

Each user sees only the records their own unit owns; national users see everything. A record's owner follows the delivery model of its scheme: under Model 1 the RBDA manages the scheme, and under Model 2 the state manages it.

| Tier | Who | Can see | Can do |
| --- | --- | --- | --- |
| National | FPMU M&E Specialist; FPMU category owners (Gender, Communication, Safeguards and GRM, Procurement, Finance); National Project Coordinator and management | All records in all states and RBDAs | Approve values; manage indicators and reference sheets (M&E Specialist); enter and upload national data; generate and approve all reports |
| National, read only | World Bank Task Team | All states; approved values only | View and export approved results and reports |
| RBDA (Model 1) | RBDA data manager and RBDA M&E staff | Only the records of schemes their RBDA manages under Model 1 | Enter, upload and submit; verify as the Model 1 verifier; run RBDA reports |
| State (Model 2) | SPIU M&E Officer; state GRM, gender and communication staff | Only their own state's records: schemes the state manages under Model 2, plus state-level records (GRM, communication, gender, budget lines) | Enter, upload and submit; verify as the Model 2 verifier; run state reports |
| Administration | ICT / System Administrator | Users, roles, integrations, logs | Manage accounts, roles and source systems; no approval rights |

- **Ownership rule:** every record carries an owner (an RBDA or a state), set from its scheme's model and held in master data. Lists, dashboards, exports, uploads and API responses are filtered by owner. The SPIU of a Model 1 state does not see RBDA-managed scheme records (D-17).
- **Approval chain:** Model 1 records are verified by the RBDA; Model 2 records by the state; the FPMU M&E Specialist approves both. Verifiers and state or RBDA users cannot approve.
- **Compartments:** SEA/SH case rows need a dedicated permission. Other roles see aggregates with small-cell suppression (NEW-4: counts below a threshold, proposed as five, show as fewer than five with no state or month breakdown). The threshold needs a decision (D-14).
- **Separation of duties:** submit, verify and approve are separate permissions and cannot be held for the same record by one user.
- **Accounts:** MFA for all users, no shared accounts, access review every quarter, audit log of changes and of reads of restricted data.
- **Roles:** one role per stakeholder in section 4, plus Administrator. A user holds a role within an owner scope, for example Verifier for an RBDA or for a state. The permission matrix in v0.2 section 7.4 stands, with the amendments above.

## 10. Non-functional requirements

| Area | Requirement |
| --- | --- |
| Performance | Pages load in 3 seconds on 4G for 95% of requests; reports generate within 30 seconds |
| Capacity | 200 named users, 50 concurrent, at least 500,000 farmer-linked records |
| Availability | 99.5% monthly, 07:00 to 20:00 WAT; maintenance announced 48 hours ahead |
| Backup | Daily encrypted off-site backup kept 35 days; RPO 24 hours; RTO 8 hours; quarterly restore test |
| Security | OWASP ASVS Level 2; independent penetration test with no open high or critical findings before go-live |
| Privacy | Compliance with the Nigeria Data Protection Act 2023; data protection officer named and impact assessment approved (D-5); retention periods to be set (D-15) |
| Accessibility | WCAG 2.1 AA |
| Environments | Development, staging and production; no unmasked production data in lower environments |
| Handover | Code, scripts and documentation in an FPMU-owned repository; automated tests for the indicator engine and access rules |

**Recommended stack (guide only):** React with TypeScript as a PWA on the IDMIS 2.0 design system; REST API with separate indicator-engine and integration workers and a job queue; PostgreSQL with staging, core and audit schemas; shared OpenID Connect identity provider with MFA; containerised hosting in Nigeria on an FPMU-approved provider.

## 11. Release plan (indicative, to be confirmed with the vendor)

| Release | Scope | Indicative duration | Exit criterion |
| --- | --- | --- | --- |
| R0 Inception | Close decisions D-1 to D-4 and D-7 to D-10; approve reference sheets for the 24 Results Framework lines first; sign API contracts; plan the legacy database hardening | 3 weeks | Signed design, API contracts and Results Framework reference sheets |
| R1 Core | Identity, access control, audit, registry, manual and register entry (online and offline), approval workflow, all five category pages, dashboard, project data modules, quarterly and annual reports, migration from TRIMING MEIS2, penetration test | 8 weeks | Core UAT passed; first quarterly report produced from the system with manual and register data; clean penetration test |
| R2 Automation 1 | Integration layer; SLIS adapter; GRM webhook; indicator engine; reconciliation and integration health | 6 weeks | Lineage verified for every Auto indicator fed by SLIS or GRM |
| R3 Automation 2 | BLIS adapter; survey, alert and finance connectors; STEP import; switch off bridge registers | 5 weeks | At least two-thirds of lines updated by API; bridge registers retired |
| Stabilisation | Training, manuals, support | 3 months | Two consecutive quarterly reports within 10 working days |

Release 1 can go live before any source system is ready, which removes the integration risk from the first reporting cycle.

## 12. Success metrics and instrumentation

| Metric | Target | Source |
| --- | --- | --- |
| Lines with approved reference sheet and target | 100% of Results Framework lines before R1 build; 100% of all lines before go-live | Registry |
| Lines updated by API | At least two-thirds by end of R3 | Registry data mode |
| Time from quarter end to approved report | 10 working days or less | Report log |
| Approval cycle time (submit to approve) | Within the agreed service level in at least 90% of cases | Workflow log |
| Integration sync success | At least 98% of scheduled syncs succeed (proposed) | Sync log |
| Duplicate values against source systems | Zero outside tagged bridges | Reconciliation report |
| Active users per SPIU each quarter | All participating SPIUs (target to be set) | Audit log |
| Open high or critical security findings at go-live | Zero | Penetration test report |

## 13. Risks and open decisions

| Risk | Rating | Mitigation |
| --- | --- | --- |
| Legacy SLIS and BLIS data incomplete or APIs late | High | Release 1 independent of integrations; adapters built by the MEIS vendor; gaps flagged on the dashboard; semi-automatic mode until complete |
| Legacy systems run with root database access | High | Read-only accounts and views before any adapter goes live; owner agrees in R0 |
| Targets and definitions for 91 indicators not agreed | High | Reference sheets as a release gate; No target status meanwhile |
| GRM software cannot supply required fields | Medium | Scheduled export as interim; fields agreed in R0 (D-10) |
| Disagreement on computation rules | Medium | FPMU M&E Specialist signs reference sheets; rules versioned |
| Exposure of grievance data | Medium | Data minimisation, compartment, small-cell suppression, read logging, penetration test |
| Low state-level adoption | Medium | Mobile-first design; training; reporting only through MEIS after go-live |

| # | Decision | Owner | Needed by | Blocks |
| --- | --- | --- | --- | --- |
| D-1 | Participating states, RBDAs and schemes | FPMU | R0 | Master data |
| D-2 | Hosting provider and identity option | FPMU ICT | R0 | Environment set-up |
| D-3 | Confirm which states are Model 1 (RBDA-managed) and which are Model 2 (state-managed) | FPMU | R0 | River Basin views |
| D-4 | Approve reference sheets: functionality criteria, conversion factors, de-duplication rules | FPMU M&E | R0 | Indicator engine |
| D-5 | Name data protection officer; approve impact assessment | FPMU | Before go-live | Go-live |
| D-6 | Survey platform | FPMU M&E | R1 | Survey connector |
| D-7 | Confirm five categories and merge of entries 21 and 32 | FPMU M&E | R0 | Menu structure |
| D-8 | Keep IR-4.1, IR-2.4 female and youth, IR-3.3 youth; align IR-4.1 timeframe to 30 days | FPMU with Bank | R0 | ISR layout |
| D-9 | Definitions, baselines, targets, frequency for 91 indicators | Framework owners | Before go-live | Status and scorecards |
| D-10 | Extra fields in SLIS and GRM software | System owners | R0 | R2 automation |
| D-11 | Confirm polarity and type for each indicator (NEW-1) | FPMU M&E | R0 | Status rule |
| D-12 | Issue a corrected indicator list (F1) | List owner | R0 | Registry import |
| D-13 | Confirm approval service levels (NEW-6) | FPMU | R1 | Reminders |
| D-14 | Small-cell threshold for restricted aggregates (NEW-4) | SEA/SH focal person with Safeguards | R1 | Restricted views |
| D-15 | Data retention periods | DPO | Before go-live | Backup and purge |
| D-16 | Can users see national totals or other owners' aggregates for benchmarking, or only their own records? | FPMU M&E | R1 | Access scope rules |
| D-17 | In a Model 1 state, does the SPIU get read-only sight of the RBDA-managed scheme records, or none? This PRD assumes none. | FPMU M&E | R0 | Owner filter rules |

## 14. Traceability

| Source | Used for |
| --- | --- |
| SPIN indicator list, 2 Nov 2026 | Five categories and 111 distinct indicators (page references 41 to 44, 66, 80 to 81, 88 to 95, 33 to 36) |
| SPIN MEIS 2.0 PRD v0.2 | Modules, API standards, access control, non-functional requirements, acceptance scenarios |
| SPIN Project Appraisal Document, Section VII | Results Framework lines reported to the Bank |
| SPIN GRM Framework | GM-01 to GM-26 definitions |
| Communication and Behaviour Change Strategy; Gender and Social Inclusion Roadmap | CF, BC and GS indicators |
