---
domain: едем-чиним.рф
origin: https://xn----gtbcbqazdh2h.xn--p1ai
source_render: false
generated_at: 2026-10-12T19:02:24.768Z
findings: 1
source_evidence: .seoagent/audit/evidence.md
note: >-
  MACHINE-GENERATED findings report derived from the live-crawl evidence.
  The audit (audit/latest.md) BUILDS ON this file — it must carry every
  finding below forward (adding GSC context and prioritization on top),
  never re-derive or truncate the list. Do not hand-edit; re-run
  `seoagent crawl` to regenerate.
---

# Technical findings — едем-чиним.рф (from the live crawl)

Derived by code from `.seoagent/audit/evidence.md` (crawled https://xn----gtbcbqazdh2h.xn--p1ai at 2026-10-12T19:02:24.768Z). Every finding below is **Confirmed** against that evidence. Reporting every one of these is non-negotiable — session economy trims bookkeeping, never findings.

## [Medium] 3 images are missing alt text across 2 pages

**Confirmed** · Evidence: evidence.md § Site-wide rollup — Images missing alt

**Check:** `images_without_alt`

These `<img>` tags carry no `alt` attribute at all (decorative `alt=""` counts as present and is not flagged).

Affected URLs:
- https://xn----gtbcbqazdh2h.xn--p1ai/ — 2 images (/wp-content/themes/edemchinim/img/logo.svg, https://xn----gtbcbqazdh2h.xn--p1ai/wp-content/themes/edemchinim/img/logo.svg)
- https://xn----gtbcbqazdh2h.xn--p1ai/calculator — 1 image (https://xn----gtbcbqazdh2h.xn--p1ai/wp-content/themes/edemchinim/img/logo.svg)

**Why it matters:** Missing alt text costs image-search visibility and accessibility, and is one of the most common automated-audit dings.

**Suggested fix:** Add descriptive alt text to each listed image (start with the srcs shown).

## 1 crawled page is excluded from search on purpose

These pages were captured by the crawl but are held out of the page-scoped findings above: the site itself excludes them from Google, so on-page gaps on them cost no traffic. Listed here so nothing is dropped silently — if one of these should be indexable, that is the finding.

**1 page — declares `noindex` (robots meta tag or X-Robots-Tag header):**
- https://xn----gtbcbqazdh2h.xn--p1ai/data/reviews

## Already present on the live site — never recommend adding these

The crawl confirms the following already exist in the live server HTML. If the repo source lacks any of them, the **repo source is stale — the live page already serves it; reconcile the source** with what is live. Never phrase these as "add X".

- https://xn----gtbcbqazdh2h.xn--p1ai/ — <title>, meta description, canonical, Open Graph tags, Twitter card, JSON-LD schema (WebPage, BreadcrumbList, WebSite, Organization)
- https://xn----gtbcbqazdh2h.xn--p1ai/calculator — <title>, meta description, canonical, Open Graph tags, Twitter card, JSON-LD schema (WebPage, BreadcrumbList, WebSite, Organization)
- https://xn----gtbcbqazdh2h.xn--p1ai/data/reviews — <title>, meta description, Open Graph tags, Twitter card, JSON-LD schema (WebPage, BreadcrumbList, WebSite, Organization)
