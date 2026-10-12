---
domain: едем-чиним.рф
origin: https://xn----gtbcbqazdh2h.xn--p1ai
origin_source: domain
source_render: false
captured_at: 2026-10-12T19:02:24.768Z
pages_crawled: 3
pages_discovered: 3
pages_captured: 3
capture_complete: true
note: >-
  Live-crawl evidence base. Every fact here was fetched from the live site.
  The audit must ground all Confirmed claims on THIS file, never on repo
  source or priors. Do NOT recommend adding schema / robots rules /
  canonical tags that already appear below.
---

# Live-Crawl Evidence — едем-чиним.рф

**Crawled origin:** https://xn----gtbcbqazdh2h.xn--p1ai at 2026-10-12T19:02:24.768Z

## Repo routes not served live (2 — ignored, not pages of this site)

The repo scan inferred these routes from loose files (a top-level `*.html`, a route file), but the live site answers 404/410 for each and neither the sitemap nor the homepage nav links to them. They are NOT pages of this site: they are excluded from every count and finding in this file, and there is nothing to fix, redirect or remove for them. If one of them SHOULD be live, deploy it (or add it to the sitemap) and re-run `seoagent crawl`.

- https://xn----gtbcbqazdh2h.xn--p1ai/data/conditioner — HTTP 404 (repo-inferred, not live)
- https://xn----gtbcbqazdh2h.xn--p1ai/data/seasonal-prices — HTTP 404 (repo-inferred, not live)

## robots.txt (Confirmed)
- Fetched: HTTP 200
- Disallow rules present: `/wp-json/`, `/?rest_route=`
- Sitemap declarations: https://xn----gtbcbqazdh2h.xn--p1ai/sitemap_index.xml
- Raw contents:
```
# START YOAST BLOCK
# ---------------------------
User-agent: *
Disallow: /wp-json/
Disallow: /?rest_route=

Sitemap: https://xn----gtbcbqazdh2h.xn--p1ai/sitemap_index.xml
# ---------------------------
# END YOAST BLOCK
```

## sitemap.xml (Confirmed)
- **2 URLs**, 0 blog/article posts
- Sitemap INDEX: 1 of 1 child sitemap read; the URL count above is the pages they declare (child `.xml` files are not pages and were not crawled)
  - No blog posts are in the sitemap — if the site has a blog, its posts are not discoverable this way (report as a finding, Confirmed).

## Site-wide rollup (Confirmed)
- **Pages missing canonical:** https://xn----gtbcbqazdh2h.xn--p1ai/data/reviews
- **Pages missing meta description:** _(none — all pages pass)_
- **Pages with multiple H1s:** _(none — all pages pass)_
- **Pages with no structured data (zero JSON-LD):** _(none — all pages pass)_
- **Images missing alt:** 5 total — https://xn----gtbcbqazdh2h.xn--p1ai/ (2), https://xn----gtbcbqazdh2h.xn--p1ai/calculator (1), https://xn----gtbcbqazdh2h.xn--p1ai/data/reviews (2)

## https://xn----gtbcbqazdh2h.xn--p1ai/
- HTTP 200 · 1063 body words
- **title:** Выездной шиномонтаж в Москве и МО 24/7 - ЕДЕМ-ЧИНИМ
- **meta description:** Выездной шиномонтаж в Москве и Московской области круглосуточно. Замена и ремонт шин, запуск двигателя и помощь на дороге. Вызов мастера по вашему адресу.
- **h1(s):** `ВЫЕЗДНОЙ ШИНОМОНТАЖ 24/7`
- **canonical:** `https://xn----gtbcbqazdh2h.xn--p1ai/` (server-rendered)
- **JSON-LD @types present:** `WebPage`, `BreadcrumbList`, `WebSite`, `Organization` — **already present; do NOT recommend adding these**
- **Open Graph:** og:locale, og:type, og:title, og:description, og:url, og:site_name
- **Twitter:** twitter:card
- **images:** 11 total, **2 missing alt** (`/wp-content/themes/edemchinim/img/logo.svg`, `https://xn----gtbcbqazdh2h.xn--p1ai/wp-content/themes/edemchinim/img/logo.svg`) (Confirmed)
- **Already present (do NOT recommend adding):** <title>, meta description, canonical, Open Graph tags, Twitter card, JSON-LD schema — verified in the server `<head>`.

## https://xn----gtbcbqazdh2h.xn--p1ai/calculator
- HTTP 200 · 153 body words
- **title:** Калькулятор выездного шиномонтажа - расчёт стоимости
- **meta description:** Рассчитайте предварительную стоимость выездного шиномонтажа и помощи на дороге. Выберите услугу, укажите адрес и параметры автомобиля, оформите заявку.
- **h1(s):** `Выездной шиномонтаж 24/7`
- **canonical:** `https://xn----gtbcbqazdh2h.xn--p1ai/calculator/` (server-rendered)
- **JSON-LD @types present:** `WebPage`, `BreadcrumbList`, `WebSite`, `Organization` — **already present; do NOT recommend adding these**
- **Open Graph:** og:locale, og:type, og:title, og:description, og:url, og:site_name
- **Twitter:** twitter:card
- **images:** 10 total, **1 missing alt** (`https://xn----gtbcbqazdh2h.xn--p1ai/wp-content/themes/edemchinim/img/logo.svg`) (Confirmed)
- **Already present (do NOT recommend adding):** <title>, meta description, canonical, Open Graph tags, Twitter card, JSON-LD schema — verified in the server `<head>`.

## https://xn----gtbcbqazdh2h.xn--p1ai/data/reviews
- HTTP 200 · 16 body words
- **title:** Отзывы о ЕДЕМ-ЧИНИМ - выездной шиномонтаж
- **meta description:** Отзывы клиентов о выездном шиномонтаже и помощи на дороге ЕДЕМ-ЧИНИМ. Впечатления о приезде мастеров, ремонте колёс и выполнении заказов в Москве и МО.
- **robots:** noindex — the page asks search engines not to index it (Confirmed). On-page SEO findings do NOT apply, and it must NOT be added to sitemap.xml or llms.txt.
- **h1(s):** `Страница в разработке`
- **canonical:** _(none in server HTML)_
- **JSON-LD @types present:** `WebPage`, `BreadcrumbList`, `WebSite`, `Organization` — **already present; do NOT recommend adding these**
- **Open Graph:** og:locale, og:type, og:title, og:description, og:url, og:site_name
- **Twitter:** twitter:card
- **images:** 3 total, **2 missing alt** (`/wp-content/themes/edemchinim/img/logo.svg`, `https://xn----gtbcbqazdh2h.xn--p1ai/wp-content/themes/edemchinim/img/logo.svg`) (Confirmed)
- **Already present (do NOT recommend adding):** <title>, meta description, Open Graph tags, Twitter card, JSON-LD schema — verified in the server `<head>`.
