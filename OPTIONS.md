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

We run a central platform where new cities click "Start my site," get a subdomain or connect their own domain, and fill in a config form (city name, Google Calendar URL, org list, colors).

**This doesn't have to be WordPress.** If we're hosting it ourselves, we control the stack. Options include:

- **WordPress Multisite** — familiar admin, huge plugin ecosystem, but heavier to run and maintain at scale.
- **Custom lightweight app** (Node/Python/Go + a simple DB) — purpose-built for exactly this use case, leaner, easier to keep consistent across tenants. Admin UI is simpler because it only does what Take Action sites need.
- **Static generator with a web-based config UI** — we build a simple dashboard that writes config files and triggers rebuilds (Hugo/Astro behind the scenes). Very cheap to host, but less flexible for per-city customization.

The hosted approach lets us pick whatever tech is easiest for *us* to maintain, since activists never touch the stack — they only see their admin dashboard.

**Setup experience:** Visit our site, click "Create your Take Action site," fill in a short form, done.

| Pros | Cons |
|------|------|
| Zero setup for the activist | They depend on us — if we go away, they lose their site |
| Instant launch | Some activists will reject the dependency on principle |
| We control updates, security, and improvements centrally | Need to build admin/config UI |
| Easiest possible onboarding | Need a credible data export story to build trust |
| Stack choice is ours — can optimize for maintainability | |
| Can iterate and ship features to all cities at once | |
| **We have bare metal server capacity** — marginal cost per city is near zero | |
| Cities could charge their members $5–10/mo to offset costs, creating sustainability | |

**Independence level:** Low by default — but can be mitigated with a strong export path (e.g., "download your site as a standalone WordPress install" or "export your data as CSV/JSON").

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
| WordPress export is a real, tested migration path | WordPress Multisite adds ops complexity |
| Best of both worlds — managed or self-hosted | |

**Independence level:** High — WordPress is portable by design. Even managed users can leave.

---

## Feature Compatibility by Option

How well does each option support the features Take Action sites need?

| Feature | WP Theme | Hosted (any stack) | Static Template | Hybrid WP |
|---------|----------|---------------------|-----------------|-----------|
| **Event calendar (Google Cal)** | Yes — via plugin or custom block | Yes — native integration | Yes — build-time or client-side fetch | Yes |
| **In-Person / Virtual filter** | Yes — WP taxonomy or JS filter | Yes — trivial to build | Yes — client-side JS | Yes |
| **iCal / subscribe links** | Yes — pass through Google Cal URLs | Yes | Yes | Yes |
| **Org directory with cards** | Yes — custom post type | Yes — DB or config | Yes — markdown/YAML data files | Yes |
| **Category tags / filtering** | Yes — WP taxonomies, native | Yes — trivial | Yes — frontmatter tags + JS | Yes |
| **Business directory** | Yes — same as orgs | Yes | Yes — same as orgs | Yes |
| **Org/business logos** | Yes — WP media library | Yes — uploads or URLs | Yes — static assets or URLs | Yes |
| **Volunteer signup** | Yes — plugin or embed | Yes — embed or built-in | Yes — embed (GetZelos, Google Form) | Yes |
| **Donation integration** | Yes — many WP plugins | Yes — embed or link | Yes — link/embed | Yes |
| **Newsletter signup** | Yes — HubSpot/Mailchimp plugins | Yes — embed or API | Yes — embed | Yes |
| **Bilingual / i18n** | Partial — plugins exist but clunky | Yes — can build properly | Yes — Hugo/Astro have i18n support | Partial |
| **Custom branding (colors, logo)** | Yes — WP customizer | Yes — config form | Yes — config file | Yes |
| **Map links for events** | Yes — from calendar data | Yes | Yes | Yes |
| **Social media links** | Yes — config or widget | Yes — config | Yes — config file | Yes |
| **Recurring event display** | Yes — Google Cal handles this | Yes | Yes | Yes |
| **Extensibility** | **High** — 60,000+ WP plugins, custom PHP, any JS widget. Activists can add SEO, analytics, forms, e-commerce, membership, forums, etc. without dev help. | **Medium-High** — we control the platform so we can add features, but cities can't add their own unless we build an extension/plugin system. If WP-based: high. If custom app: only what we build. | **Low-Medium** — adding features means editing code (HTML/JS/CSS). Non-devs can't extend it. However, embeds (iframes, script tags) work for third-party tools. | **High** — same WP plugin ecosystem for self-hosters. Managed tier gets whatever we ship. |

---

## Comparison Matrix

| Criteria | WP Theme | Hosted (any stack) | Static Template | Hybrid WP |
|----------|----------|---------------------|-----------------|-----------|
| **Setup difficulty** | Medium | Trivial | Hard | Trivial–Medium |
| **Familiarity for activists** | High | High | Low | High |
| **Independence** | High | Low | High | High |
| **Hosting cost (per city)** | $5–10/mo | Free (we pay) | Free | Free or $5–10/mo |
| **Maintenance burden (per city)** | On them | On us | On them | On us or them |
| **Our maintenance burden** | Low | High | Low | Medium |
| **Data portability** | High (WP export) | Depends on export path | High (files) | High (WP export) |
| **Scalability to 50+ cities** | Easy | Easy (bare metal capacity available) | Easy | Easy |
| **Extensibility** | High (plugin ecosystem) | Medium-High (we control) | Low-Medium (code only) | High |

---

## Assessment

**Key insight: if we build a WordPress theme + plugin, hosting becomes trivial.** We have bare metal server capacity. Running WordPress Multisite (or even individual WP installs behind a management layer) on our own hardware means the hosted option (Option 2) comes almost for free once the WordPress plugin exists. Cities that want independence take the plugin and self-host. Cities that want easy get a managed install on our server. This collapses Options 1, 2, and 4 into a single strategy: **build the WP plugin, offer both paths.**

**Can all features be achieved in a WP plugin? Yes.** Every feature on the current Take Action Tucson site maps cleanly to WordPress capabilities:

| Feature | WordPress Implementation |
|---------|------------------------|
| Event calendar from Google Cal | Plugin fetches and caches Google Calendar API/iCal feed, renders as list view with date grouping |
| In-Person / Virtual filter | Custom taxonomy or simple JS toggle on the calendar output |
| iCal / subscribe links | Static links to the Google Calendar subscription URLs — just config fields |
| Org directory with cards | Custom Post Type "Organization" with custom fields (logo, description, contact links, tags) |
| Category tags / filtering | WordPress taxonomies — native, well-supported, filterable |
| Business directory | Same CPT as orgs, or a second CPT — either works |
| Logos / images | WordPress media library — upload in admin, display on cards |
| Volunteer signup | Embed slot (shortcode or block) for GetZelos, Google Form, or any third-party form |
| Donation integration | Embed slot or link — Venmo, PayPal, Stripe, etc. Just a config field |
| Newsletter signup | Embed slot for HubSpot, Mailchimp, etc. — or use a WP plugin like MC4WP |
| Bilingual / i18n | WPML or Polylang plugin — not trivial but well-trodden |
| Custom branding | WordPress Customizer — colors, logo, site name, tagline |
| Map links | Generated from event location data — straightforward |
| Social media links | Config fields in the Customizer or plugin settings |
| Recurring events | Google Calendar handles the recurrence; we just display what it gives us |

**Nothing here requires a custom app.** The WP plugin ecosystem already handles the hard parts (i18n, media management, caching). The plugin we build is mostly: (1) Google Calendar fetch + display, (2) Organization/Business custom post types with a card layout, (3) a settings page for calendar URL, social links, donation link, and embed slots.

**The main risk is trust.** Activist groups care about independence. WordPress mitigates this naturally — it's the world's most portable CMS. Even hosted users can export with one click and move to any $5/mo shared host.

**Hosting economics:** We have bare metal capacity, so marginal cost per city is near zero. Cities could charge their own members $5–10/mo for sustainability if they want, or we absorb it. Either way, this isn't a scaling cost problem.

---

## Recommended Next Steps

1. **Clarify "calendar component"** — Google Calendar integration seems like the baseline (Tucson already uses it). Do we need anything beyond displaying a Google Calendar feed?

2. **Pick 1–2 options to prototype** — Suggestion: Option 2 (Hosted, custom lightweight) or Option 4 (Hybrid WP) are the strongest candidates.

3. **Talk to potential users** — What would "Take Action Pennsylvania" actually want? Would they use WordPress? Do they already have a Google Workspace?
