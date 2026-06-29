# Brand Center — Claude Code Prompt Library

Every prompt below is copy-paste ready. Run them in the order listed.
Prompts marked **[SITUATIONAL]** are not run in sequence — use them when the specific situation arises.

**How testing works:**
Testing is now Step 6 of the per-requirement workflow defined in `CLAUDE.md`. Claude Code will run it automatically at the end of every requirement — you do not need a separate prompt. After each implementation, Claude Code will validate PHP syntax, list environment prerequisites, and give you exact manual browser test cases. Do not move to the next prompt until you have confirmed those tests passed.

---

## BEFORE YOU START

- `CLAUDE.md` must be at the root of the project before running any prompt
- Only **Dev Ready** Figma pages are approved: `[REDACTED]` (Desktop), `[REDACTED]` (Mobile), `[REDACTED]` (Components)
- C02 and C03 specs are in Miro (board `[REDACTED]`) — use your board
- C01 is now Active (Confluence `[REDACTED]`, Figma `[REDACTED]`) — implement when you reach Prompt 18
- E02 is confirmed: build from Figma only (nodes `[REDACTED]` Desktop, `[REDACTED]` Mobile)
- E03 is still pending — do not implement until confirmed
- D01 is now fully specified
- Fill in Coding Conventions (Prompt 0b) before writing any code

---

## PHASE 0 — Project Setup

---

### Prompt 0a — Initialise Claude Code

> **Send this first, before anything else.**

```
Read CLAUDE.md carefully and confirm you have understood it before doing anything else.

This is the Brand Center — a WordPress brand platform for the Company. CLAUDE.md is the single source of truth: tech stack, design tokens, user roles, all requirement IDs with their Confluence page IDs and Figma node IDs, the email module, navigation rules, and the per-requirement workflow.

Confirm the following before we begin:
1. You have read CLAUDE.md in full
2. Only Figma Dev Ready pages are approved for implementation (`[REDACTED]` Desktop, `[REDACTED]` Mobile, `[REDACTED]` Components) — all other Figma pages are WIP and off-limits
3. You understand the 6-step per-requirement workflow: fetch Confluence (behaviour) → fetch Figma (design) → check Miro if routing/nav is involved → implement → cross-check (Step 5) → test (Step 6)
4. Step 6 means: after every requirement, you will run PHP syntax validation, list all environment prerequisites needed before browser testing, and write exact manual test cases for happy path, error states, role variations, and mobile — before I move to the next requirement
5. You will not write any code until you have fetched and read both the Confluence spec and the Figma design
6. You will surface conflicts between sources and wait for a decision before proceeding

Once confirmed, tell me which requirement we are starting with and what you will fetch first.
```

---

### Prompt 0b — Define Coding Conventions

> **Send this before writing any code.** Fill in the blanks based on your team's decisions.

```
Before we implement anything, I need to define the coding conventions for this project. Add the following as the Coding Conventions section in CLAUDE.md, replacing the placeholder:

## Coding Conventions

- Theme structure: [e.g. custom theme at /wp-content/themes/Brand Center-brand-center/]
- Block/component approach: [e.g. ACF blocks / native Gutenberg blocks / hybrid]
- PHP standard: [e.g. PSR-2 / WordPress Coding Standards]
- CSS methodology: [e.g. BEM / utility-first / CSS custom properties only]
- JavaScript: [e.g. vanilla ES6+ / jQuery where WP requires it]
- File naming: [e.g. kebab-case for templates, PascalCase for block classes]
- CSS variables: use the tokens defined in CLAUDE.md (--blue-Brand Center, --red-Brand Center, etc.) — never hardcode hex values
- All BO-configurable content uses ACF fields — never hardcode editorial content
- Template files follow WordPress hierarchy (page-{slug}.php, single.php, etc.)
- Every block registers its own ACF field group
- No inline styles — all styling in theme stylesheet or block-specific CSS
```

---

### Prompt 0c — Scaffold the Theme

```
Scaffold the WordPress theme structure for the Brand Center project.

Requirements:
- Theme folder: /wp-content/themes/brand-center/
- Create the standard WordPress theme files: style.css (with theme header), functions.php, index.php, header.php, footer.php, sidebar.php
- Register the design tokens from CLAUDE.md as CSS custom properties in the :root of the main stylesheet
- Register Google Fonts for IBM Plex Sans and Montserrat in functions.php (enqueue properly)
- Set up a base block registration structure in functions.php ready for Gutenberg blocks
- Do not create any content yet — this is skeleton only

Then run Step 6 from CLAUDE.md for this scaffold before we proceed.
```

---

## PHASE 1 — Authentication (Module A)

---

### Prompt 1 — A01: Login Screen

```
Implement A01 — Login

Fetch these sources before writing any code:
- Confluence page `[REDACTED]` (behaviour, flows, error cases)
- Figma Desktop node `[REDACTED]` (file key `[REDACTED]`, Dev Ready page `[REDACTED]`)
- Figma Mobile node `[REDACTED]` (Dev Ready page `[REDACTED]`)

Key rules from CLAUDE.md to apply:
- This page is public — no auth required to reach it
- All other pages on the platform must redirect here if the user is unauthenticated
- Two login paths on the same screen: SSO (Microsoft O365) and email+password (external users)
- No navigation elements (header, sidebar, footer) on the login screen — check Figma
- Use design tokens from CLAUDE.md for all colours and typography — no hardcoded hex values

Follow the full 6-step per-requirement workflow from CLAUDE.md including Step 6 (test) before finishing.
```

---

### Prompt 2 — SSO Integration

```
Implement the Microsoft Azure AD / O365 SSO integration for internal users.

This is a dependency of A01. The SSO plugin choice is marked TBC in CLAUDE.md.

Before implementing:
1. Recommend the most appropriate WordPress OAuth/SSO plugin for Azure AD integration (e.g. WP OAuth Server, miniOrange, WPO365) — justify the choice
2. Outline the integration approach: how the plugin connects to Azure AD, how the WP user session is created, and how the avatar photo is pulled from the O365 profile
3. Wait for my approval of the plugin choice before writing any code

Key behaviour from CLAUDE.md:
- Internal users must NOT be asked to create a WP account — login is automatic via O365
- Avatar: use O365 profile photo if available; fall back to assets/images/avatar-default.png in the theme
- After SSO login, redirect to Homepage (B01)
- On logout (A06), invalidate WP session only — do not touch the O365 session

After implementing, run Step 6 from CLAUDE.md — include the Azure AD app registration and plugin configuration as environment prerequisites in 6b.
```

---

### Prompt 3 — A02: Invite External User

```
Implement A02 — Invite External User

Fetch these sources before writing any code:
- Confluence page `[REDACTED]` (behaviour, flows, error cases)
- Figma Desktop nodes: `[REDACTED]` (A02.1 invite form), `[REDACTED]` (A02.2 set password), `[REDACTED]` (A02.3 expired) — Dev Ready page `[REDACTED]`
- Figma Mobile node `[REDACTED]` (Dev Ready page `[REDACTED]`)

Key rules from CLAUDE.md:
- Two actors can invite: Local Admin (via frontend form) and Admin (via WP backoffice)
- After invite is submitted, Email #1 (invitation) is sent to the new user — 24h token validity
- Do NOT send a confirmation email back to the Local Admin — this was removed from scope
- The invite form is accessible from the profile dropdown (visible to Local Admins only)

Also implement Email #1 from the email module (Confluence `[REDACTED]`):
- Trigger: A02 invite submitted
- Content: welcome message + link to set password
- Token validity: 24 hours

Follow the full 6-step workflow from CLAUDE.md. Include test cases for both the Local Admin path (frontend) and the Admin path (backoffice) in Step 6c.
```

---

### Prompt 4 — A03: Password Recovery

```
Implement A03 — Password Recovery (external users only)

Fetch these sources before writing any code:
- Confluence page `[REDACTED]` (behaviour, flows, error cases)
- Figma Desktop nodes: `[REDACTED]` (reset request screen), `[REDACTED]` (new password screen), `[REDACTED]` (expired link screen) — all Dev Ready page `[REDACTED]`

Key rules:
- External users only — internal users manage credentials via Microsoft
- Triggered from the Login screen via "Forgot password?"
- Email #2 (password recovery) must be sent — 24h token validity
- Expired token shows the expired screen with option to request a new link
- Never reveal whether an email address exists in the system — always show "if this email exists, you'll receive instructions"

Also implement Email #2 from the email module (Confluence `[REDACTED]`):
- Trigger: external user requests recovery from login screen
- Content: instructions + secure token link
- Token validity: 24 hours

Follow the full 6-step workflow from CLAUDE.md. Include the expired token test case in Step 6c.
```

---

### Prompt 5 — A04: Admin Password Reset (BO)

```
Implement A04 — Admin-initiated password reset from the WordPress backoffice

Fetch this source before writing any code:
- Confluence page `[REDACTED]` (behaviour, flows, error cases)
- No Figma node — this is a backoffice-only flow

Key rules:
- Only the Admin role can trigger this, from wp-admin user management
- The user receives Email #3 (admin-initiated reset) — 24h token validity
- The user sets their own new password via the frontend screen from A03 (node `[REDACTED]`)
- The Admin does not set the password on behalf of the user

Also implement Email #3 from the email module (Confluence `[REDACTED]`):
- Trigger: Admin initiates reset in backoffice
- Content: notification to external user + secure token link
- Token validity: 24 hours

Follow the full 6-step workflow from CLAUDE.md.
```

---

### Prompt 6 — A05: Change Password

```
Implement A05 — Change Password (external users only, while authenticated)

Fetch these sources before writing any code:
- Confluence page `[REDACTED]` (behaviour, flows, error cases)
- Figma Desktop node `[REDACTED]` (Dev Ready page `[REDACTED]`)

Key rules:
- Only external users see this option — hidden for all SSO users (internal, admin, local admin)
- Accessible from the profile dropdown → "Change Password"
- Requires: current password, new password, confirm new password
- On success: trigger Email #4 (password change confirmation)

Follow the full 6-step workflow from CLAUDE.md. Test cases in Step 6c must include: correct flow for external user, and verification that the option is invisible to all SSO roles.

Note: Do not implement any email triggered by A05. The password change confirmation email was removed from scope.
```

---

### Prompt 7 — A06: Logout

```
Implement A06 — Logout

Fetch these sources before writing any code:
- Confluence page `[REDACTED]` (behaviour, flows, error cases)
- Figma node `[REDACTED]` (profile dropdown, Dev Ready page `[REDACTED]`)

Key rules from CLAUDE.md:
- Logout is visible to ALL roles in the profile dropdown
- On logout: invalidate WP session and authentication cookies, redirect to Login (A01)
- For SSO users: end the WP session only — do NOT touch the O365/Microsoft session
- On mobile: Logout link is also fixed at the bottom of the first level of the mobile menu

After implementing, use Step 6 to verify end-to-end: after logout, any attempt to access an authenticated page must redirect to Login. Include this as a test case in Step 6c.
```

---

## PHASE 2 — Global Layout (Navigation)

---

### Prompt 8 — Navigation Read-First

> **Send this before implementing any nav component. Do not skip it.**

```
Before implementing any navigation component, fetch and read:
- Confluence page `[REDACTED]` (Estrutura e navegação — all 7 nav elements, rules and behaviours)
- Miro navigation frame: `[REDACTED]`

Do not implement anything yet. Read both sources and summarise:
1. Which nav elements appear on which page types
2. Role-based visibility rules for the profile dropdown
3. The 8-line column logic rule for the mega-menu
4. Which pages have NO left sidebar (full-width content)
5. Any ambiguities or questions before starting

Wait for my confirmation before proceeding.
```

---

### Prompt 9 — Header & Top Mega-Menu

```
Implement the Header and Top Mega-Menu (Desktop)

Sources:
- Confluence `[REDACTED]` (already fetched — reference as needed)
- Figma Desktop node `[REDACTED]` (Dev Ready page `[REDACTED]`)

Key rules to implement precisely:
- 3-level hierarchy: L1 = horizontal bar, L2 = bold category headers in dropdown columns, L3 = links under each L2
- Column logic: max 8 lines per column. L2 must always appear in the same column as its first L3 child. L2 with no L3 children occupies one bold line and the next group starts
- Active state: current section's menu item is visually highlighted per Figma
- Profile dropdown — role-based options:
  - Internal User → Logout only
  - Admin → Go to admin console + Logout
  - Local Admin → Invite User + Logout
  - External User → Change Password + Logout
- Avatar: O365 profile photo or default image (assets/images/avatar-default.png)
- Header must be present on all authenticated pages

Follow the full 6-step workflow from CLAUDE.md. Step 6c test cases must cover all four role variants for the profile dropdown.
```

---

### Prompt 10 — Mobile Menu

```
Implement the Mobile Menu

Sources:
- Confluence `[REDACTED]` (already fetched)
- Figma Mobile node `[REDACTED]` (Dev Ready page `[REDACTED]`)

Key rules:
- Triggered by hamburger icon
- Drill-down navigation — NOT an accordion. Tapping L1 replaces the panel with the L2 list for that category
- Back button at top of L2 panel: shows parent name + left-arrow icon, returns to L1
- Logout link is fixed at the bottom of the L1 panel, visible to all roles

Follow the full 6-step workflow from CLAUDE.md.
```

---

### Prompt 11 — Footer

```
Implement the Footer

Sources:
- Confluence `[REDACTED]` (footer rules and elements table)
- Figma Desktop node `[REDACTED]` (Dev Ready page `[REDACTED]`)

Key rules:
- Present on ALL page templates without exception
- Three sections — all manageable from the WP backoffice via ACF repeater fields:
  1. Institutional links (label + URL, opens in new tab)
  2. Social media icons (icon image + URL, opens in new tab, unlimited items)
  3. Legal links (label + URL, opens in new tab, unlimited items)
- Copyright text is fixed (not editable in BO)
- Legal links must open in a new tab for BOTH authenticated and unauthenticated users

Follow the full 6-step workflow from CLAUDE.md. Step 6b must list the BO content setup needed (initial links, icons) as prerequisites.
```

---

### Prompt 12 — Left Sidebar (Contextual Navigation)

```
Implement the Left Sidebar — contextual navigation for interior content pages

Sources:
- Confluence `[REDACTED]` (sidebar rules)
- Figma Desktop node `[REDACTED]` (Dev Ready page `[REDACTED]`)

Key rules:
- Displays sibling pages (same parent as current page), ordered by WordPress menu order
- Below siblings: two fixed links always present — "Navigation Tips" and "FAQs"
- Active state: current page is highlighted per Figma
- Hidden on pages outside the main hierarchy (Contact form, 404, Login, etc.) — on those pages content expands full width
- Includes a search input at the top of the sidebar

Note: the left sidebar and right anchor bar share the same Figma node. Implement the left sidebar now; anchor bar comes next.

Follow the full 6-step workflow from CLAUDE.md.
```

---

### Prompt 13 — Right Anchor Bar (Scrollspy)

```
Implement the Right Anchor Bar — in-page section navigation

Sources:
- Confluence `[REDACTED]` (anchor bar rules)
- Figma Desktop node `[REDACTED]` (Dev Ready page `[REDACTED]`) — right side of the layout

Key rules:
- Only rendered when the current page has at least one anchor section defined in the WP backoffice
- If no anchors are defined: this element is absent and main content expands to fill the space
- Scrollspy: as the user scrolls, the anchor for the currently visible section becomes active
- Click on an anchor → smooth scroll to that section
- Anchor sections are defined per-page in the backoffice (ACF repeater: section label + section ID)

Follow the full 6-step workflow from CLAUDE.md. Step 6c must test both states: page with anchors and page without anchors.
```

---

### Prompt 14 — Breadcrumb

```
Implement the Breadcrumb component

Sources:
- Confluence `[REDACTED]`
- Figma component node `[REDACTED]` (Dev Ready Components page `[REDACTED]`)

Key rules:
- Shows: Home → L1 → L2 → Current page (current item is plain text, non-clickable)
- All ancestor items are clickable links
- Visibility is driven by WordPress nav menu membership — NOT by WordPress page hierarchy
- Show breadcrumb ONLY if the current page is part of the main nav menu (any level)
- Do NOT show breadcrumb on pages outside the main menu, even if they have a WordPress parent: FAQs, Navigation Tips, and Contact form (E01) must never show a breadcrumb
- On an L1 menu page the breadcrumb shows: Home → Current page

Follow the full 6-step workflow from CLAUDE.md.
```

---

## PHASE 3 — Homepage (Module B)

---

### Prompt 15 — B01: Homepage

```
Implement B01 — Homepage

Fetch these sources before writing any code:
- Confluence page `[REDACTED]` (behaviour, sections, search filter initial config)
- Figma Desktop node `[REDACTED]` (Dev Ready page `[REDACTED]`)
- Figma Mobile node `[REDACTED]` (Dev Ready page `[REDACTED]`)

Key rules:
- Entry point for ALL roles after authentication — identical for every role
- Includes: editorial highlights, quick access sections, and search with filters
- The search filter initial config from Confluence `[REDACTED]` defines which menu options are active at launch — implement exactly as specified
- All editorial content must be editable from the WP backoffice (ACF fields)
- Full-width layout — no left sidebar

Follow the full 6-step workflow from CLAUDE.md. Check the Miro IA frame (`[REDACTED]`) if you need content hierarchy clarification.
```

---

## PHASE 4 — Branded Content (Module C)

---

### Prompt 16 — C02 & E02: Generic Content Page

```
Implement C02 (Page structure with components) and E02 (Generic content — authenticated)

These two requirements share the same page template — a content page for authenticated users with the full navigation shell (header, left sidebar, right anchor bar if applicable, footer, breadcrumb).

Fetch these sources before writing any code:
- Confluence page `[REDACTED]` (C02) — spec is in Miro, not Confluence. Fetch: `[REDACTED]`
- Confluence page `[REDACTED]` (E02) — Confluence page is empty but Figma is complete. Build E02 from Figma only — no need to wait for Confluence spec
- Figma Desktop node `[REDACTED]` (Dev Ready page `[REDACTED]`)
- Figma Mobile node `[REDACTED]` (Dev Ready page `[REDACTED]`)

This template must support component-based composition: editors assemble pages from lego block components in the backoffice. Define the block registration architecture before implementing individual components.

Follow the full 6-step workflow from CLAUDE.md.
```

---

### Prompt 17 — C03: Components (Lego Blocks)

```
Implement C03 — the component library (backoffice-composable blocks)

Fetch these sources before writing any code:
- Confluence page `[REDACTED]` — contains only a Miro link. Fetch the spec from Miro instead: `[REDACTED]`
- Figma Components page `[REDACTED]` — list all components available before starting

Read the Miro frame first and summarise what components are specified. Only implement components that are specified in the Miro frame AND have a corresponding Figma component on the Components page.

Available component sets on the Figma Components page:
Card Homepage, Button, Link, Header, Footer, Input, Checkbox, SearchBar, Card Category, AnchorLink, SidebarLink, Collection Card, Grid, Text block, SearchInput, Note, Generic Card, Quote, Table, Thumbnail, Chip, Breadcrumb, Message

For each component:
1. Build as a Gutenberg block with ACF field group
2. Match Figma design exactly — design tokens only, no hardcoded values
3. Register so editors can insert it from the block inserter in WP backoffice

Implement one component at a time. Run Step 6 from CLAUDE.md after each component and confirm with me before proceeding to the next.
```

---

### Prompt 18 — C01: Landing Pages with Children

```
Implement C01 — Pages with children (landing pages / category index pages)

Fetch these sources before writing any code:
- Confluence page `[REDACTED]` (fully specified — read the complete spec)
- Figma Desktop node `[REDACTED]` (Dev Ready page `[REDACTED]`)
- Miro IA frame: `[REDACTED]` — check hierarchy rules

Key rules from the Confluence spec:
- Applies to all main menu pages that have child pages (L1 and some L2)
- The cards grid is MANUALLY managed in BO — not auto-generated from child pages
- Card fields: Title (required), Image (required), Subtitle (optional, rich text), Destination link (required)
- Above the grid: Section Title (required), Body Text (optional, rich text), Buttons repeater (optional)
- The entire card is clickable
- The Excerpt field on this page feeds into D01 search results
- Page must be correctly positioned in the WordPress menu hierarchy for breadcrumb and nav to work

All card content must be managed via ACF fields — nothing hardcoded.

Follow the full 6-step workflow from CLAUDE.md.
```

---

## PHASE 5 — Search (Module D)

---

### Prompt 19 — D01: Search with Filter

```
Implement D01 — Search with filter

Fetch these sources before writing any code:
- Confluence page `[REDACTED]` — fully specified. Read the complete spec including flows, filter/tab behaviour, pagination, back-navigation, and error states before implementing
- Figma Desktop node `[REDACTED]` (Dev Ready page `[REDACTED]`)

Key rules from CLAUDE.md:
- The search bar exits the current page and redirects to a dedicated search results page
- Results are filterable — filters defined by the menu structure (configured per B01 spec)
- Must handle the no-results state (use the Figma no-results node)
- Search is within authenticated content only

Follow the full 6-step workflow from CLAUDE.md. Step 6c must include a no-results test case.
```

---

## PHASE 6 — Complementary Pages (Module E)

---

### Prompt 20 — E01: Contact Form

```
Implement E01 — Contact Form

Fetch these sources before writing any code:
- Confluence page `[REDACTED]` (behaviour, fields, validation, error cases)
- Figma Desktop node `[REDACTED]` (Dev Ready page `[REDACTED]`)

Key rules:
- No left sidebar on this page — full-width content
- Fields: name (optional), email (required), message (required)
- On successful submission: show success state (see Figma success node)
- Recipient email address must be configurable in the WP backoffice (ACF option field — not hardcoded)

Also implement Email #4 from the email module (Confluence `[REDACTED]`):
- Trigger: successful contact form submission
- Recipient: management team (address set in BO)
- Content: notification with submitted fields — name (if filled), email, message body

Follow the full 6-step workflow from CLAUDE.md.
```

---

### Prompt 21 — E03: Generic Public Content (Legal Pages)

```
Implement E03 — Generic content (public / unauthenticated)

Fetch these sources before writing any code:
- Confluence page `[REDACTED]` — DRAFT: read it, report what is specified vs missing, wait for my go-ahead
- Check Dev Ready Desktop page `[REDACTED]` for any public/legal page frames — report what you find

Key rules from CLAUDE.md:
- Publicly accessible — no authentication required
- Used for Privacy Policy, Terms of Use, and similar legal pages
- Legal links in the footer open these pages in a new tab for all users
- Page template must work without the authenticated navigation shell (no header, no sidebar, no profile dropdown)
- Footer must still be present

Follow the full 6-step workflow from CLAUDE.md.
```

---

### Prompt 22 — E04: 404 Page

```
Implement E04 — 404 Page

Fetch these sources before writing any code:
- Confluence page `[REDACTED]` (behaviour, rules)
- Figma Desktop node `[REDACTED]` (Dev Ready page `[REDACTED]`)
- Figma Mobile node `[REDACTED]` (Dev Ready page `[REDACTED]`)

Key rules:
- Appears when an authenticated user reaches a URL that does not exist or has been removed
- Must keep the user inside the platform — include navigation options to return to known areas
- Uses the authenticated layout (header and footer present)
- Create a custom 404.php template in the WordPress theme

Follow the full 6-step workflow from CLAUDE.md.
```

---

## PHASE 7 — Pre-Launch

---

### Prompt 22b — File Access Control

```
Implement file access control for protected downloads (Confluence page `[REDACTED]`).

Be direct. Read only the Confluence page before writing any code. It contains the full implementation strategy and a complete PHP code example to follow exactly.

Key rules:
- Protected files are stored OUTSIDE wp-content/uploads (not publicly accessible)
- A rewrite rule creates a /download/ route that serves protected files after auth checks
- Unauthenticated users are redirected to login
- Path traversal attacks must be prevented
- Files >10MB must be streamed, not loaded into memory
- Follow the PHP implementation in Confluence precisely

Follow the full 6-step workflow from CLAUDE.md.
```

---

### Prompt 23 — Compile Pre-Launch Checklist

```
We have finished implementing all requirements. Compile a complete pre-launch checklist.

Go through everything you implemented across all requirements (A01–A06, B01, C01–C03, D01, E01–E04, NAV, global layout, email module) and list every action that still needs to be done outside of code — including everything you flagged during implementation and during Step 6 environment prerequisites.

Organise into these categories:

1. WordPress Admin actions (pages to create, templates to assign, menus to build, settings to configure)
2. Plugin configuration (credentials, API keys, scopes, third-party settings)
3. Theme assets to add (images, icons, fonts that must be placed in the theme folder)
4. Backoffice content setup (ACF fields to populate, initial content, menu structure to build)
5. Email configuration (SMTP setup, sender address, recipient addresses)
6. Environment / infrastructure (anything specific to STG or PROD)
7. Access & permissions (WP user roles to create, test accounts needed)
8. QA to complete (browser checks, end-to-end flows requiring human verification)

For each item: state what needs to be done, which requirement it belongs to, and whether it blocks launch or is a nice-to-have.

Do not include anything already done in code — only actions requiring a human or external configuration.
```

---

### Prompt 24 — Pre-Launch Checklist — Catch Any Gaps

> **Send immediately after Prompt 23.**

```
Now re-read CLAUDE.md — specifically the Email Module section, the User Roles & Permissions table, the Navigation Structure section, and the MCP Resources section.

Are there any pre-launch items from those sections that are not already in the checklist you just produced? Add any that are missing.

Pay particular attention to:
- The Azure AD app registration and WPO365 plugin configuration (SSO)
- The default avatar image file in the theme
- The WordPress login page (slug: login, Login template assigned)
- The WP menu structure matching the IA from Miro
- The contact form recipient email address in the backoffice
- The 4 email templates and their SMTP sending configuration
```

---

### Prompt 25 — Auth & Permissions Audit

```
Perform a full authentication and permissions audit across all four roles.

For each role (Admin, Local Admin, Internal User, External User) verify:

1. Login path works correctly (SSO vs email+password)
2. Correct redirect after login → Homepage
3. Profile dropdown shows only the correct options for that role
4. Unauthenticated access to any page redirects to Login
5. Admin can access /wp-admin; all other roles receive a 403 or redirect
6. Local Admin invite flow works end-to-end (A02 + Email #1)
7. External user password recovery works end-to-end (A03 + Email #2)
8. Admin password reset works end-to-end (A04 + Email #3)
9. Change password works for external users; option is invisible for all SSO roles (A05 — no email is triggered after A05)
10. Logout ends WP session only and redirects to Login for all roles (A06)

Report any role that can access something it should not, or cannot access something it should.
```

---

### Prompt 26 — Backoffice Editability Audit

```
Audit all content that CLAUDE.md marks as backoffice-configurable.

For each item, confirm it is editable in the WordPress backoffice and not hardcoded:
- Footer: institutional links, social media icons, legal links, their URLs
- Contact form: recipient email address for Email #5
- Homepage: editorial highlights and featured section content
- Navigation: menu items and hierarchy (WordPress menu manager)
- Anchor bar sections: defined per-page via ACF repeater field
- Search filter options: configurable per B01 spec

Report anything that is hardcoded but should be editable, and fix it.
```

---

### Prompt 27 — Cross-browser & Responsive QA

```
Perform a QA pass across all implemented screens for browser and responsive coverage defined in CLAUDE.md.

Desktop (2 latest versions each): Chrome, Edge, Firefox, Safari (macOS)
Mobile (2 latest versions each): Chrome on Android, Safari on iOS

Screens to verify:
- Login (A01) — desktop and mobile
- Homepage (B01) — desktop and mobile
- Generic content page with sidebar and anchor bar (C02/E02) — desktop and mobile
- Landing page with children (C01) — desktop and mobile
- Search results with filters (D01) — desktop
- Search results — no-results state (D01) — desktop
- Contact form and success state (E01) — desktop
- 404 page (E04) — desktop and mobile
- Mobile menu drill-down (NAV) — mobile only

For each screen report: layout issues, spacing deviations from Figma, broken interactive states, or anything that does not match the Dev Ready designs.
```

---

## SITUATIONAL PROMPTS

> Use these when the specific situation arises — not in sequence.

---

### [SITUATIONAL] When a DRAFT Confluence page is incomplete

```
You flagged that Confluence page [PAGE_ID] for [REQUIREMENT ID] is incomplete.

List precisely:
1. What IS specified (actors, described flows, defined rules)
2. What is MISSING or ambiguous
3. Your recommendation for each gap (wait for spec vs safe default)

Do not implement anything until I respond.
```

---

### [SITUATIONAL] When Confluence and Figma conflict

```
You flagged a conflict between Confluence and Figma for [REQUIREMENT ID / element].

Describe clearly:
1. What the Confluence spec says
2. What the Figma design shows
3. The impact of each option on behaviour or UX

Do not resolve it yourself. Wait for my decision before writing any code.
```

---

### [SITUATIONAL] When Claude Code uses a wrong Figma page

```
Stop. You used a Figma node that is not from a Dev Ready page.

Only these pages are approved:
- Desktop Dev Ready: page `[REDACTED]` (file key `[REDACTED]`)
- Mobile Dev Ready: page `[REDACTED]`
- Components: page `[REDACTED]`

Find the correct Dev Ready node for [element] or tell me if one does not exist yet.
Do not implement from WIP Design, Status snapshots, or any other page.
```

---

### [SITUATIONAL] To verify completion before moving on

```
Before we move on from [REQUIREMENT ID], confirm:

1. Which Confluence rules did you validate against (Step 5)?
2. Which Figma node(s) did you match the output against (Step 5)?
3. Did you run PHP syntax validation (Step 6a)?
4. Did you list all environment prerequisites (Step 6b)?
5. Did you write manual test cases for happy path, error states, role variations, and mobile (Step 6c)?
6. Is all editable content implemented as ACF fields — nothing hardcoded?
7. Are design tokens used everywhere — no hardcoded hex values or font sizes?
8. Is there anything you assumed or could not verify?
```

---

### [SITUATIONAL] To get Claude Code back on track

```
Stop what you are doing.

Re-read the relevant section of CLAUDE.md and the Confluence page for [REQUIREMENT ID].

Tell me:
1. What you were about to do
2. Whether that is actually specified in the sources
3. What you should do instead
```

---

### [SITUATIONAL] When a DRAFT requirement is finalised in Confluence

```
The Confluence spec for [REQUIREMENT ID] (page [PAGE_ID]) has been updated and is no longer DRAFT.

Re-fetch the page and compare against what was already implemented.

Report:
1. New or changed behaviour requiring implementation changes
2. Rules now clarified but already implemented correctly
3. Anything to add, remove, or modify

Wait for my approval before making any changes.
```

---

### [SITUATIONAL] After a Figma Dev Ready update

```
The Figma Dev Ready designs have been updated.

Re-fetch Figma node [NODE_ID] (file key `[REDACTED]`) and compare against the current implementation.

Report:
1. Visual changes (colours, spacing, typography, layout)
2. Structural changes (new elements, removed elements, reordered sections)
3. Changes that affect behaviour or logic

Wait for my approval before making any changes.
```

---

### [SITUATIONAL] When you need to check BO-configurability

```
Before implementing [element/content], confirm: is this content specified as editable in the backoffice in the Confluence spec for [REQUIREMENT ID]?

If yes → implement as an ACF field, never hardcode.
If no or unclear → flag it and wait for my decision.
```
