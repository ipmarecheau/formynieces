@mvp @seo
Feature: Blog / resources library
  A public library of articles that answer the questions Caribbean SEA parents
  actually search for — results and placement, study and pacing, the problems
  with traditional lessons, the case for tracking and consolidating learning
  data, and the honest good and bad of AI in a child's learning. It exists to
  earn organic search traffic (each article targets keyword combinations) and to
  build trust; content is authored as markdown in database/data/blog and seeded
  into the articles table. Every article page is fully search-legible and
  share-ready, and drafts (future-dated) stay hidden until their date arrives.

  @scenario:BLOG-01
  Scenario: The index lists published articles, newest first
    Given several published articles with different publish dates
    When a visitor opens /blog
    Then the articles are listed newest first
    And each shows its title and excerpt

  @scenario:BLOG-02
  Scenario: Future-dated drafts stay hidden
    Given an article whose publish date is in the future
    When a visitor opens the blog
    Then the draft does not appear in the index
    And opening its URL directly returns 404

  @scenario:BLOG-03
  Scenario: Each article page is search-legible and share-ready
    Given a published article
    When a visitor opens its page
    Then the page has the article's own title and meta description
    And a canonical URL and Open Graph tags
    And Article structured data with a datePublished

  @scenario:BLOG-04
  Scenario: Published articles appear in the sitemap
    Given a published article
    When a crawler requests /sitemap.xml
    Then the article's URL is listed

  @scenario:BLOG-05
  Scenario: The index can be filtered by category
    Given published articles in more than one category
    When a visitor filters the index by a category
    Then only articles in that category are shown
