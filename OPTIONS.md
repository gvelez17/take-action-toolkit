# Take Action Toolkit — Cloneable Activist Toolkit Options

## Background

[Take Action Tucson](https://takeactiontucson.org) is an activism hub that serves as a central resource for pro-democracy organizing. The goal is to create a **cloneable toolkit** so other cities (e.g., "Take Action Pennsylvania," "Take Action Portland") can quickly launch their own version.

### Current Site Analysis

**Stack:** Hugo static site deployed on Vercel, Google Calendar as event source, manually curated directories.

**Key features to replicate:**

| Feature | Current Implementation |
|---------|----------------------|
| Event calendar | Chronological list view with In-Person/Virtual filter, sourced from Google Calendar. iCal/Google/Outlook subscription links. |
| Organizations directory | Card grid with logo, description, contact links, category tags (healthcare, labor, immigrant support, etc.) |
| Business directory | Similar cards for aligned local businesses |
| Action hub | Volunteer signup (GetZelos), donation (Venmo), newsletter (HubSpot), social links |
| Recurring events | "Resist @ Rush Hour" — 9 weekly locations with named hosts |
| Bilingual support | Some events offered in English/Spanish |

**What makes this clonable:** The data is simple (Google Calendar events, ~10-15 manually curated org/business entries). No login, no database, no user accounts. All integrations are external SaaS (Google, HubSpot, Venmo) — easily swappable per city.

---

## Options

### Option 1: WordPress Theme + Plugin

A WordPress theme (or starter theme + plugin) that provides the calendar view, org directory, and business directory as custom post types or block patterns. Events pull from a Google Calendar URL the user configures.

**Setup experience:** Install WordPress on any $5-10/mo host. Install the theme and plugin. Enter your city name, Google Calendar URL, and org list in the admin panel.

| Pros | Cons |
|------|------|
| Activists already know WordPress | WordPress maintenance burden (updates, security patches) |
| Huge hosting ecosystem — any shared host works | More complex initial setup than a hosted solution |
| Full independence — they own everything | Theme customization requires some WP admin comfort |
| Massive plugin ecosystem for forms, donations, newsletters | |
| Can move to any host at any time | |

**Independence level:** High — fully portable, standard WordPress export/import.

---

### Option 2: Hosted Multi-Tenant (One-Click Clone)

We run a central platform (could be WordPress Multisite, or a lightweight custom app). New cities click "Start my site," get a subdomain or connect their own domain. They fill in a config form (city name, Google Calendar URL, org list, colors).

**Setup experience:** Visit our site, click "Create your Take Action site," fill in a short form, done.

| Pros | Cons |
|------|------|
| Zero setup for the activist | They depend on us — if we go away, they lose their site |
| Instant launch | Hosting cost scales with number of cities |
| We control updates, security, and improvements centrally | Some activists will reject the dependency on principle |
| Easiest possible onboarding | Need to build admin/config UI |

**Independence level:** Low — unless we build a robust export/migration path.

---

### Option 3: Static Site Generator Template (GitHub + Free Hosting)

A GitHub template repo (Hugo, Astro, or 11ty) with a config file. Fork it, edit `config.yaml` (city name, calendar URL, org list), deploy free on Netlify/Cloudflare Pages/Vercel.

**Setup experience:** Click "Use this template" on GitHub, edit one config file, connect to Netlify/Cloudflare for free hosting.

| Pros | Cons |
|------|------|
| Free hosting (Netlify, Cloudflare Pages, Vercel) | Requires GitHub comfort or a tech-savvy helper for setup |
| Fast, secure, no server to maintain | Editing org lists = editing YAML/markdown files |
| Closest to what Tucson already runs | Not familiar to non-tech activists |
| Could build a setup wizard to generate the config | Updates require pulling from upstream template |

**Independence level:** High — their repo, their deploy, zero cost.

---

### Option 4: Hybrid — WordPress + Managed Hosting Tier

Build the WordPress theme/plugin (Option 1) as the core product. **Also** offer a managed hosting tier where we run WordPress Multisite for groups who don't want to set up their own. They can always export their site and self-host later — WordPress portability is built-in.

**Setup experience:** Either (a) "click here and we'll set you up" or (b) download the theme/plugin and install on your own WordPress.

| Pros | Cons |
|------|------|
| Low barrier for those who want easy | We maintain two things (theme AND hosting infra) |
| Full independence for those who want control | More work upfront to build both paths |
| WordPress export is a real, tested migration path | |
| Best of both worlds — managed or self-hosted | |

**Independence level:** High — WordPress is portable by design. Even managed users can leave.

---

### Option 5: Google Sheets as CMS + Simple Frontend

The data (events, orgs, businesses) lives in **Google Sheets** that activists already know how to edit. A lightweight frontend (static site or small app) reads from the published sheets via API. We provide a template site + template spreadsheets.

**Setup experience:** Copy our template Google Sheet. Add your events and orgs. Copy our template site and point it at your spreadsheet. Deploy anywhere.

| Pros | Cons |
|------|------|
| Spreadsheet editing is the most familiar tool for non-tech users | Google Sheets API has rate limits |
| No CMS to learn — it's just a spreadsheet | Formatting/validation is loose (typos, inconsistent data) |
| Collaborative editing built into Google Sheets | Still needs a frontend deployed somewhere |
| Activists already use Google Calendar + Sheets | Dependency on Google APIs |
| Data is always visible and exportable | Less polished admin experience than a real CMS |

**Independence level:** Medium — they own the data (in Google), but need the frontend hosted somewhere.

---

## Comparison Matrix

| Criteria | WP Theme | Hosted | Static Template | Hybrid WP | Sheets CMS |
|----------|----------|--------|-----------------|-----------|------------|
| **Setup difficulty** | Medium | Trivial | Hard | Trivial-Medium | Medium |
| **Familiarity for activists** | High | High | Low | High | Very High |
| **Independence** | High | Low | High | High | Medium |
| **Hosting cost (per city)** | $5-10/mo | Free (we pay) | Free | Free or $5-10/mo | Free-$5/mo |
| **Maintenance burden (per city)** | On them | On us | On them | On us or them | Minimal |
| **Our maintenance burden** | Low | High | Low | Medium | Low |
| **Data portability** | High (WP export) | Low-Medium | High (files) | High (WP export) | High (it's a spreadsheet) |
| **Scalability to 50+ cities** | Easy | Expensive | Easy | Mixed | Easy |

---

## Recommended Next Steps

1. **Clarify the "spreadsheet component"** — Is this about how activists *enter data* (editing a spreadsheet) or a spreadsheet-like *display* on the site? This shapes whether Option 5 is the right fit.

2. **Clarify "calendar component"** — Google Calendar integration seems like the baseline (Tucson already uses it). Do we need anything beyond displaying a Google Calendar feed?

3. **Pick 1-2 options to prototype** — Suggestion: Option 4 (Hybrid WordPress) gives the broadest reach. Option 5 (Sheets CMS) could be a lightweight alternative or even combined with it.

4. **Talk to potential users** — What would "Take Action Pennsylvania" actually want? Would they use WordPress? Do they already have a Google Workspace?
