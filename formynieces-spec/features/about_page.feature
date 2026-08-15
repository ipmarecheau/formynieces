@mvp @guardian
Feature: About page — why SmoothSeas exists
  Parents buy from people they trust. The about page tells the true origin
  story — a platform built for family first — and the beliefs that shape
  every screen: honest progress, no shame in re-learning, working with
  schools, and a Caribbean-first lens. It ends by inviting the parent to
  talk to a human.

  @scenario:AB-01
  Scenario: The origin story is told honestly
    Given a visitor opens the about page
    Then it tells how SmoothSeas began — built for family, for two children preparing for the SEA
    And that it is made in the Caribbean, for Caribbean families
    And it never claims credentials, results, or a team it does not have

  @scenario:AB-02
  Scenario: The beliefs are spelled out
    Given a visitor reads the about page
    Then it states the platform shows progress honestly — no rosy spin for parents
    And that re-learning carries no shame for the child
    And that it works with the child's school, not against it

  @scenario:AB-03
  Scenario: The page funnels to a conversation
    Given a visitor reaches the end of the about page
    Then the page invites them to book a parent onboarding call
    And links to the booking page

  @scenario:AB-04
  Scenario: Smooth introduces the page
    Given a visitor opens the about page
    Then Smooth appears with his real artwork
    And the page explains why the companion is a turtle — steady and calm
