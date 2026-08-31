# SmoothSeas — SEO Strategy

The **non-testable** half of SEO. The technical/on-page guarantees live in
`formynieces-spec/features/seo.feature` (spec + Pest tests). This document holds
the ongoing practice — keywords, content, off-page, and measurement — because no
test can honestly assert a Google ranking. Revisit this quarterly.

> **Honest expectation:** we can make the site technically excellent and fully
> crawlable (done). Ranking then depends on content depth, site authority
> (backlinks), competition, and time. No one can *guarantee* a #1 position.

## 1. Who we're ranking for
Caribbean (primarily Trinidad & Tobago) **parents/guardians** of Standard 3–5
children preparing for the **SEA (Secondary Entrance Assessment)**. They search on
phones, in the evenings, worried about exam readiness and school placement.

## 2. Target keywords (draft — refine with Search Console data)
**Primary (high intent):**
- SEA exam practice / SEA practice online
- SEA past papers online / SEA past papers Trinidad
- SEA preparation app / SEA prep app Trinidad and Tobago
- online SEA classes / SEA tutoring online

**Secondary / long-tail (easier to rank, high conversion):**
- how to prepare my child for SEA
- SEA Mathematics practice questions
- SEA creative writing practice
- SEA English Language Arts practice
- SEA exam 2027 preparation
- best SEA prep for Standard 4 / Standard 5

**Branded:** SmoothSeas, Smooth the turtle SEA.

Map one primary keyword to each core page; build long-tail with content (below).

## 3. Content plan (the biggest lever once the tech is done)
Google ranks pages that genuinely answer parent questions. Priorities:
- A **/blog** or resource section with articles targeting long-tail queries
  ("How to help your child with SEA creative writing", "The SEA maths topics that
  trip children up", "How SEA placement works in Trinidad & Tobago").
- Expand the **FAQ** with the real questions parents ask (each becomes a rich
  result via FAQ structured data — a good next feature).
- Keep pages **substantive and unique** — thin/duplicate pages don't rank.
- Natural internal linking between the blog, FAQ, and the sign-up page.

## 4. Off-page (authority) checklist
- Google Business Profile for 64-Bit Software Solutions / SmoothSeas.
- Listings/mentions in T&T parenting and education communities, school groups,
  local directories.
- Reach out for backlinks from Caribbean education blogs and parent forums.
- Encourage genuine reviews.
(Never buy links — it risks penalties.)

## 5. What's already in place (technical, shipped)
- Unique `<title>` + meta description per public page.
- Canonical, Open Graph, Twitter Card tags (good link previews).
- JSON-LD structured data (Organization, WebSite, EducationalOrganization).
- `/sitemap.xml` and `robots.txt` (allows crawl, blocks private app areas).
- Mobile-friendly, responsive, fast landing page.
- Google Search Console verified.

## 6. Manual steps in Google Search Console (only the owner can do these)
1. **Submit the sitemap:** Search Console → Sitemaps → add `sitemap.xml`.
2. **Request indexing** of the homepage and key pages (URL Inspection tool).
3. Watch **Performance** (queries, clicks, impressions, position) and **Pages**
   (indexed/excluded) monthly.
4. Fix anything flagged under **Page experience / Core Web Vitals**.

## 7. Measurement (KPIs, not pass/fail tests)
- Impressions & clicks per query (Search Console).
- Average position for the target keywords above.
- Indexed page count.
- Organic sign-ups (tie to the register CTA).

Track these over time; they are outcomes, not acceptance criteria.
