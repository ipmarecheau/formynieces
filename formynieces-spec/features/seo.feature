@mvp @seo
Feature: Discoverability — technical / on-page SEO
  The app-owned, testable half of SEO: every public page is legible to search
  engines and previews well when shared. This feature covers ONLY what the code
  emits and a test can assert — titles and meta descriptions, canonical URLs,
  Open Graph and Twitter cards, structured data, an XML sitemap, and robots
  rules. The non-testable half — keyword strategy, content, backlinks, and actual
  rankings — is NOT a feature; it lives in SEO_STRATEGY.md and is tracked in
  Google Search Console, because no test can honestly assert a ranking.

  @scenario:SEO-01
  Scenario: Every public page is share-ready with canonical, Open Graph and Twitter tags
    Given a public marketing page
    When it is rendered
    Then it carries a canonical URL
    And Open Graph tags (title, description, url, image, site name)
    And Twitter Card tags (summary_large_image, title, description, image)

  @scenario:SEO-02
  Scenario: Every public page has a unique, descriptive title and meta description
    Given the public pages — home, about, FAQ, contact, book-a-call, terms, privacy
    When each is rendered
    Then it has its own <title> naming the page and the SEA context
    And its own meta description
    And a canonical link to itself

  @scenario:SEO-03
  Scenario: The landing page carries structured data for rich results
    Given the landing page
    When it is rendered
    Then it emits JSON-LD structured data
    And describes the Organization, the WebSite, and an EducationalOrganization

  @scenario:SEO-04
  Scenario: An XML sitemap lists every public page
    Given a crawler requests /sitemap.xml
    Then it receives a valid XML urlset
    And it lists the home, about, FAQ, contact, book-a-call, terms and privacy URLs

  @scenario:SEO-05
  Scenario: robots.txt allows crawling and points to the sitemap
    Given a crawler requests robots.txt
    Then crawling of the public site is allowed
    And private app areas (dashboard, guardian, admin) are disallowed
    And the sitemap URL is declared
