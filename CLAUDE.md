# CLAUDE.md — Ascendum Brand Center

## Project Overview

Ascendum Brand Center is a private digital platform centralising brand guidelines and assets for Ascendum Group and its five sub-brands (Ascendum, My Ascendum, Ascendum Service Center, Ascendum Advanced Solutions, Ascendum Rent). It serves internal Ascendum collaborators authenticated via Microsoft Azure AD / O365 SSO, and invited external users (partners, agencies, suppliers) authenticated with email + password. The platform is built on WordPress (frontend + backoffice), with content assembled through a component-based system ("lego blocks" in BO) and a strict 4-level page hierarchy driven by WordPress menu order.

---

## Tech Stack

| Layer | Technology |
|---|---|
| CMS / Backend | WordPress (latest stable) |
| Theme files | `index.php`, page templates, `functions.php`, Gutenberg blocks as needed |
| Hosting (STG) | Siteground — `ascendum.brand-center-stg.innovagencyhost.com` |
| Hosting (PROD) | `brandcenter.ascendumgroup.com` |
| SSO | Microsoft Azure AD / O365 — WP OAuth plugin (TBC) |
| External Auth | Native WordPress user/pass |
| Language | English only |
| Browsers (Desktop) | Chrome, Edge, Firefox, Safari — 2 latest versions |
| Browsers (Mobile) | Chrome (Android), Safari (iOS) — 2 latest versions |

---

## Design Tokens

Extracted from Figma file `eN8lg4gjHQgYXZVpbABO9J`, node `5526:27925` (Identity / Ascendum / Colors).

> ⚠️ **Figma scope rule:** Only frames from the **Dev Ready** pages are approved for implementation. All other pages (WIP Design, Status snapshots, Trash, etc.) are still in progress and must not be used.
>
> - Dev Ready Desktop: page `4595:7127`
> - Dev Ready Mobile: page `4595:7128`
> - Dev Ready Components: page `139:9018`

### Colours

| Token | Hex | Usage |
|---|---|---|
| `--blue-ascendum` | `#003846` | Primary — logo, headings, nav, brand containers |
| `--blue-ascendum-60%` | `rgba(0,56,70,0.6)` | Placeholder text, muted elements |
| `--blue-ascendum-90%` | `rgba(0,56,70,0.9)` | Footer border |
| `--red-ascendum` | `#E31D1A` | Accent only — highlights, CTAs; never dominant, never on logo |
| `--grey-ascendum` | `#D6D6D6` | Backgrounds, dividers, UI structure |
| `--darker-grey` | `#585858` | Body text |
| `--grey` | `#B1B1B1` | Inactive breadcrumbs, sidebar inactive states |
| `--bg/lightest-grey` | `#F4F4F4` | Sidebar active item background |
| `--white` | `#FFFFFF` | Base background |

### Typography

| Token | Family | Weight | Size | Line Height |
|---|---|---|---|---|
| `Headline/Desktop/H1` | Montserrat | Bold 700 | 40px | 1.3 |
| `Headline/Desktop/H2` | Montserrat | Bold 700 | 30px | 1.3 |
| `Headline/Desktop/H4` | Montserrat | Bold 700 | 18px | 1.3 |
| `Headline/Desktop/H5` | IBM Plex Sans | SemiBold 600 | 18px | 1.5 |
| `Body/M/Regular` | IBM Plex Sans | Regular 400 | 16px | 1.5 |
| `Body/M/SemiBold` | IBM Plex Sans | SemiBold 600 | 16px | 1.5 |
| `Body/S/Bold` | IBM Plex Sans | SemiBold 600 | 14px | 1.5 |
| `Body/S/Regular` | IBM Plex Sans | Regular 400 | 14px | 1.0 |

---

## General Requirements

These rules apply globally across all features unless a specific requirement states otherwise (source: Confluence `1228505261`):

- All functional rules, validations, and error handling follow **Requisitos Gerais**
- Content that is specified as configurable must be **editable in the WordPress backoffice**
- All pages, components, and forms must follow the **Figma Dev Ready designs** — no deviation without approval
- Use WordPress theme files (`index.php`, page templates, `functions.php`) and Gutenberg blocks as the implementation pattern
- The platform is **single-language: English**
- Components are "lego blocks" assembled in the BO to compose interior pages
- Authentication gates the entire platform: unauthenticated access always redirects to Login

---

## User Roles & Permissions

| Role | Auth Method | Backoffice | Can Invite | Profile Menu Options |
|---|---|---|---|---|
| **Admin** | SSO (O365) | Yes (`/wp-admin`) | No (manages users directly in BO) | Go to admin console + Logout |
| **Local Admin** | SSO (O365) | No | Yes (via frontend form) | Invite User + Logout |
| **Internal User** | SSO (O365) | No | No | Logout only |
| **External User** | Email + Password | No | No | Change Password + Logout |

**Entry point:** All unauthenticated requests → Login (`A01`). Post-auth, all roles land on Homepage (`B01`). Avatar: O365 profile photo if available; default image otherwise.

---

## MCP Resources

| Resource | Key / ID / URL |
|---|---|
| **Figma file key** | `eN8lg4gjHQgYXZVpbABO9J` |
| Figma — Dev Ready Desktop ✅ | Page `4595:7127` |
| Figma — Dev Ready Mobile ✅ | Page `4595:7128` |
| Figma — Dev Ready Components ✅ | Page `139:9018` |
| Figma — WIP / other pages ⛔ | Do not implement from these |
| **Confluence cloud ID** | `fc1532db-a54c-46a0-a6af-d5c482f6deec` |
| Confluence space | `ABC` at `innovagency.atlassian.net` |
| **Miro board — IA & Navigation** | `https://miro.com/app/board/uXjVGsOnvkg=/` |
| Miro — IA frame | `?moveToWidget=3458764665139918832` |
| Miro — Navigation frame | `?moveToWidget=3458764665140029073` |
| Miro — Components frame (IA board) | `?moveToWidget=3458764665140029104` |
| Miro — C02 & C03 spec frame | `https://miro.com/app/board/uXjVGsOnvkg=/?moveToWidget=3458764665140029104` |
| _(Note: original board `uXjVG1s1mck` is inaccessible via MCP — use the frame above)_ | |
| STG WP Admin | `https://qabrandcenter.ascendumgroup.com/wp-admin/` |
| PROD WP Admin | `https://brandcenter.ascendumgroup.com/wp-admin/` |

---

## Information Architecture & Navigation

Source: Confluence `1228505277` + Miro navigation frame (`3458764665140029073`).

The platform has a **4-level page hierarchy** driven entirely by WordPress menu order. Seven navigation elements are active across all authenticated pages:

| Element | Figma Node (Dev Ready) | Rules |
|---|---|---|
| **Top Mega-Menu** (Desktop) | `4937:4243` | 3-level hierarchy. L1 = horizontal bar; L2 = bold category headers in dropdown columns; L3 = links under each L2. Column rule: max 8 lines per column; L2 always stays in the same column as its first L3 child. L2 with no children = leaf node, bold, occupies one line. Active state on current section. |
| **Mobile Menu** | `4703:5326` | Drill-down (not accordion). Selecting L1 enters a new screen listing L2 options. Back button at top with parent name + left-arrow icon. Logout fixed at bottom of L1. |
| **Left Sidebar** (Contextual) | `4595:7268` | Shows sibling pages (same parent) of the current page. Below siblings: fixed links to "Navigation Tips" and "FAQs". Active page highlighted. Hidden on pages outside the main hierarchy (e.g. Contact form → full-width content). |
| **Right Anchor Bar** | `4595:7268` | Rendered only if anchors are defined in BO for the page. Scrollspy highlights current in-view section. Click → smooth scroll. Absent = main content expands full width. |
| **User Profile Dropdown** | `4937:5850` / `4937:5856` / `5223:11623` | Activated by avatar/name click; dismissed by second click or outside click. Options are role-dependent (see Roles table). |
| **Footer** | `4595:7242` | Present on all templates. Institutional links, social icons, legal links — all editable in BO. Legal pages always open in a new tab for both authenticated and public users. |
| **Breadcrumb** | `5642:12004` | Home → L1 → L2 → Current page (current item is non-clickable text). Visibility rule: shown ONLY on pages that are part of the main WordPress nav menu. Pages outside the menu (FAQs, Navigation Tips, Contact form) must NOT show a breadcrumb, even if they have a WordPress parent page. On an L1 menu page the breadcrumb shows: Home → Current page. |

---

## Requirements Index

> `(D)` = Desktop, `(M)` = Mobile. All Figma nodes are from Dev Ready pages only.
> C02 and C03 specs live in Miro (board `uXjVGsOnvkg`, frame `3458764665140029104`) — the original board `uXjVG1s1mck` is inaccessible via MCP, use your board instead.
> E02 is built from Figma only (nodes `5384:20711` Desktop, `5384:20914` Mobile) — Confluence page is empty but Figma is complete and sufficient.
> E03 is still pending — do not implement until confirmed.

### Module A — Authentication

| ID | Name | Confluence Page ID | Figma Node(s) | Status |
|---|---|---|---|---|
| A01 | Login | `1228505349` | `4595:7252` (D), `4703:5555` (M) | Active |
| A02 | Invite External User | `1243480065` | `4595:7377` (A02.1 D), `4937:4299` (A02.2 D), `5343:15762` (A02.3 D), `6090:15057` (M) | Active |
| A03 | Password Recovery | `1241251846` | `4937:4249` (A03.1 D), `4937:4275` (A03.2 D); A03.3 = A02.2; A03.4 = A02.3 | Active |
| A04 | Admin Password Reset (BO) | `1243480082` | — (BO only) | Active |
| A05 | Change Password | `1242333186` | `4595:7369` (D) | Active |
| A06 | Logout | `1249902593` | `4937:5850` (profile dropdown) | Active |

### Module B — Homepage

| ID | Name | Confluence Page ID | Figma Node(s) | Status |
|---|---|---|---|---|
| B01 | Homepage | `1239744533` | `4595:7242` (D), `4714:1307` (M) | Active |

### Module C — Branded Content

| ID | Name | Confluence Page ID | Figma Node(s) | Status |
|---|---|---|---|---|
| C01 | Pages with children (landing page) | `1292206094` | `7267:9051` (D) | Active |
| C02 | Page structure with components | `1249902641` | Spec in Miro: board `uXjVGsOnvkg`, frame `3458764665140029104` | Active |
| C03 | Components | `1292238849` | Spec in Miro: board `uXjVGsOnvkg`, frame `3458764665140029104`. Figma components: page `139:9018` | Active |

### Module D — Search

| ID | Name | Confluence Page ID | Figma Node(s) | Status |
|---|---|---|---|---|
| D01 | Search with filters | `1250033677` | `4595:7298` (D) | Active |

### Module E — Complementary Pages

| ID | Name | Confluence Page ID | Figma Node(s) | Status |
|---|---|---|---|---|
| E01 | Contact form | `1250066445` | `4595:7305` (D) | Active |
| E02 | Generic content (authenticated) | `1250033693` | `5384:20711` (D), `5384:20914` (M) | DRAFT |
| E03 | Generic content (public) | `1250066473` | — | DRAFT |
| E04 | 404 page | `1250066485` | `4595:7312` (D), `5368:16769` (M) | Active |

### Cross-cutting

| ID | Name | Confluence Page ID | Notes |
|---|---|---|---|
| NAV | Navigation & Structure | `1228505277` | Covers all 7 nav elements — fetch before any nav work |
| GEN | General Requirements | `1228505261` | Global rules — read first |
| EMAIL | Comunicações - Emails | `1228505365` | Email module — see section below |
| FORMATS | Formatos e componentes transversais | `1228505293` | File format specs (images: JPG/PNG/SVG/WEBP <1MB; downloads: PDF/AI/EPS/PSD/INDD/OTF/TTF; ZIP for bundles) and image dimension guidelines (Figma node `5764:17829`) |
| FILE-ACCESS | Gestão de acesso para ficheiros | `1292533765` | Strategy for protecting wp-content/uploads files from public access — route downloads through a gated `/download/` endpoint with login/RBAC checks |

---

## Email Module — Comunicação 01

Source: Confluence `1228505365`. Only the following 4 emails are in scope:

| # | Email | Trigger | Purpose | Token validity |
|---|---|---|---|---|
| 1 | **Invitation to new external user** | Local Admin (frontend) or Admin (BO) [A02] | Welcome + link to set password | 24h |
| 2 | **Password recovery** | External user clicks "Forgot password?" on login screen [A03.1] | Instructions + secure token link to reset password | 24h |
| 3 | **Admin-initiated password reset** | Admin in backoffice [A04] | Notify external user + secure token link to set new password | 24h |
| 4 | **Contact form reception** | Successful frontend form submission [E01] | Notify management team with submitted fields: name (if filled), email, message body | — |

> ❌ **Do NOT implement:**
> - Notification of invitation sent (to Local Admin)
> - Notification of invitation cancelled (to Local Admin)
> - Password change confirmation (to External User after A05)
>
> All three appear struck-through in the Confluence Emails page and are marked "NÃO CONSIDERAR".
>
> ⚠️ **Source conflict — A02:** The A02 spec text (`1243480065`) still mentions Local Admin email notifications in its flow description — this text was not updated when those emails were removed. The Emails page (`1228505365`) is the authoritative source for email scope and takes precedence. The Local Admin's only feedback after sending or cancelling an invite is the on-screen success message. No email is sent to the Local Admin in either case.
>
> ⚠️ **Email #4 trigger typo:** The Emails page references `[A04.1]` as the trigger for the password change confirmation — this is a typo in the Confluence document and the email has since been removed from scope entirely. Do not implement any email after A05.

---

## Implementation Order

1. **Auth shell** — Login (A01) + SSO integration + unauthenticated redirect rule
2. **User management** — Invite (A02), Password recovery (A03), Admin reset (A04), Change password (A05), Logout (A06)
3. **Email module** — All 4 transactional emails wired to their triggers (A02, A03, A04, E01) — no email after A05
4. **Global layout** — Header, footer, mega-menu, mobile menu, breadcrumb (NAV)
5. **Homepage** (B01) — Entry point post-auth; editorial highlights, search filters
6. **Generic content page + sidebar + anchor bar** (C02) — Core reading experience
7. **Landing pages with children** (C01) — Category index pages (now Active)
8. **Components** (C03) — BO-composable lego blocks
9. **Search with filter** (D01) — Filterable results, no-results state
10. **Contact form** (E01)
11. **Public generic content** (E03) — Legal pages, public-facing
12. **404 page** (E04)

---

## Per-Requirement Workflow

**Autonomy:** Apply all file changes, edits, and shell commands directly without asking for confirmation first. The only exceptions are: destructive actions (deleting files or database records), changes outside the theme directory, and situations where two sources conflict and a human decision is needed. For everything else — write the code, apply it, then report what you did.

**No guessing rule:** Never guess, assume, or infer implementation details not explicitly stated in Confluence, Figma, Miro, or CLAUDE.md. If something is unclear, ambiguous, or missing from all sources, stop and ask before writing any code. Applies to: behaviour not in the Confluence spec, visual details not in Figma Dev Ready frames, data structures not defined in CLAUDE.md, and any decision that affects other parts of the codebase. Collect all questions upfront and ask them together — do not ask one at a time mid-implementation.


Run these steps in order before writing any code:

**Step 1 — Fetch Confluence page (behaviour)**
```
Fetch: ari:cloud:confluence:fc1532db-a54c-46a0-a6af-d5c482f6deec:page/<PAGE_ID>
Read: actors, description, flow, rules & behaviours, error cases
Note: DRAFT pages may be incomplete — flag gaps before implementing
```

**Step 2 — Fetch Figma node(s) (design)**
```
File key: eN8lg4gjHQgYXZVpbABO9J
Only use: page 4595:7127 (Desktop Dev Ready) or 4595:7128 (Mobile Dev Ready) or 139:9018 (Components)
Use get_design_context for implementation-ready output
Use get_metadata for structural overview / node discovery
Do NOT use nodes from WIP Design, Status, or any other page
```

**Step 3 — Check Miro if navigation, routing, or component spec context is needed**
```
IA:            https://miro.com/app/board/uXjVGsOnvkg=/?moveToWidget=3458764665139918832
Navigation:    https://miro.com/app/board/uXjVGsOnvkg=/?moveToWidget=3458764665140029073
C02 & C03 spec: https://miro.com/app/board/uXjVGsOnvkg=/?moveToWidget=3458764665140029104
Check when implementing: menus, breadcrumbs, landing pages, any routing or hierarchy decision, or any component/page-structure work
```

**Step 4 — Implement**
Build against the WordPress stack. Follow Gutenberg block patterns. Content configurable in BO must be implemented as editable fields, not hardcoded.

**Step 5 — Cross-check before finishing**
- Behaviour matches Confluence spec (flows, error states, role visibility rules, validations)
- Visual output matches Figma Dev Ready frames (colour tokens, typography, spacing, active states)
- All BO-configurable content is implemented as ACF fields — nothing editorial is hardcoded
- Design tokens are used throughout — no hardcoded hex values or font sizes
- If Confluence and Figma conflict, surface the discrepancy before shipping

**Step 6 — Test**

Only after Step 5 passes, run the following:

6a. Automated checks (run these directly):
- PHP syntax validation on every file created or modified
- Confirm all registered hooks, filters, and functions exist and are correctly named
- Confirm all template files follow WordPress naming conventions and will be picked up by the template hierarchy
- Confirm all ACF field groups are registered and reference existing block templates or page templates

6b. Environment prerequisites — before any browser testing is possible, list every external action required:
- WP Admin tasks (pages to create, templates to assign, menus to configure)
- Plugin configuration needed (credentials, scopes, API keys)
- Theme assets that must be in place (images, icons)
- Test user accounts needed (which roles, how to create them)

6c. Manual test cases — provide exact step-by-step test cases for:
- Happy path (the expected successful flow)
- Every error state defined in the Confluence spec
- Every role variation (if behaviour differs by role, one test case per role)
- Mobile (if a mobile Figma frame exists for this requirement)

Do not mark a requirement as done and do not move to the next requirement until the human has confirmed the manual tests passed.

---

## Coding Conventions

- Theme structure: [e.g. custom theme at /wp-content/themes/ascendum-brand-center/]
- Block/component approach: [e.g. ACF blocks / native Gutenberg blocks / hybrid]
- PHP standard: [e.g. PSR-2 / WordPress Coding Standards]
- CSS methodology: [e.g. BEM / utility-first / CSS custom properties only]
- JavaScript: [e.g. vanilla ES6+ / jQuery where WP requires it]
- File naming: [e.g. kebab-case for templates, PascalCase for block classes]
- CSS variables: use the tokens defined in CLAUDE.md (--blue-ascendum, --red-ascendum, etc.) — never hardcode hex values
- All BO-configurable content uses ACF fields — never hardcode editorial content
- Template files follow WordPress hierarchy (page-{slug}.php, single.php, etc.)
- Every block registers its own ACF field group
- No inline styles — all styling in theme stylesheet or block-specific CSS
