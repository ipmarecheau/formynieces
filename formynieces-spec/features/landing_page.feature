@mvp @guardian
Feature: Landing page — speak to Caribbean parents
  The landing page is a parent's first meeting with SmoothSeas. It names the
  worries a Caribbean parent actually carries — not knowing how her child is
  really doing, a curriculum that doesn't bend, a child who dreads practice —
  and answers each with what the platform does about it. Smooth the turtle is
  the face of the page: the same companion the child sails with, shown large
  and animated. Every claim on the page must be true of the product today or
  clearly marked as coming — nothing overpromises, because trust is the product.

  @scenario:LP-01
  Scenario: The hero speaks to the parent's pain, with Smooth beside it
    Given a visitor opens the landing page
    Then the headline names the parent's core worry — never having to guess how her child is doing
    And Smooth is visible in the hero, as the companion the child sails with
    And the page offers to start the child's voyage or sign in
    And a signed-in visitor is offered her dashboard instead of the sales pitch

  @scenario:LP-02
  Scenario: Meet Smooth — the captain is introduced properly
    Given a visitor scrolls to the companion section
    Then Smooth is introduced by name with his poses
    And the page shows what he does: he guides her through every screen
    And he explains the rule and re-teaches when she misses, without scolding
    And he celebrates her wins

  @scenario:LP-03
  Scenario: Visibility — the parent always knows where she stands
    Given a visitor reads the visibility section
    Then it promises a clear weekly picture in the Parent Portal
    And it explains that a struggling rule reroutes her plan through a gentle re-teach the parent can see
    And no claim depends on a feature that is not built

  @scenario:LP-04
  Scenario: Control and adaptability — the curriculum plans itself around her
    Given a visitor reads the adaptability section
    Then it explains that the platform plans her curriculum and re-plans it daily around her pace and misses
    And it mentions the parent can pause and resume when life happens

  @scenario:LP-05
  Scenario: Enjoyment — she will want to log in
    Given a visitor reads the enjoyment section
    Then it shows the gamified voyage map of islands and streaks and celebrations
    And it is honest that the fun serves the learning, not the other way around

  @scenario:LP-06
  Scenario: Convenience — lessons, tutorials and practice in one harbour
    Given a visitor reads the convenience section
    Then it promises lessons, tutorials and practice in one place
    And no paid add-ons or external tools are implied

  @scenario:LP-07
  Scenario: Coverage — every strand of ELA
    Given a visitor reads the coverage section
    Then it lists the strands covered: grammar, vocabulary, reading comprehension and writing
    And it ties coverage to the SEA framework

  @scenario:LP-08
  Scenario: Effectiveness — a daily rhythm that compounds
    Given a visitor reads the effectiveness section
    Then it describes the short daily study plan
    And the morning vocabulary ritual and reading assignments

  @scenario:LP-09
  Scenario: Reinforcement — her effort pays off at home
    Given a visitor reads the reinforcement section
    Then it explains that the parent sets the treasure: streaks and mastery become the currency for rewards the parent chooses
    And the platform's role stops at showing the effort honestly

  @scenario:LP-10
  Scenario: Consolidation — the platform works with her school
    Given a visitor reads the consolidation section
    Then it explains the school journal: graded papers from school kept beside the platform's own picture
    And that classroom evidence will weigh into her daily plan
    And the section is clearly marked as coming in the MVP, not live today
