@mvp @guardian
Feature: FAQ page — answer the questions parents actually ask
  Caribbean parents hesitate for predictable reasons: is this for a child
  who is behind, will it shame them, how much screen time, what does it
  cost, what if it doesn't work, is the AI safe. The FAQ answers each
  plainly, in the same honest voice as the rest of the site, and links the
  money questions straight to the guarantees.

  @scenario:FQ-01
  Scenario: The programme questions are answered
    Given a visitor opens the FAQ page
    Then it answers what SmoothSeas is and which exam it prepares for
    And who it is for — on track, behind, or ahead
    And whether it replaces school or lessons — it complements them

  @scenario:FQ-02
  Scenario: The child-experience questions are answered
    Given a visitor reads the FAQ page
    Then it answers what happens when a child is behind — catch-up pacing without shame
    And how much time it takes each day — as little as 20 minutes, up to two hours, unlimited practice
    And what a re-teach looks like when a rule is missed

  @scenario:FQ-03
  Scenario: The parent questions are answered
    Given a visitor reads the FAQ page
    Then it answers how progress is tracked — weekly reports and the Parent Portal
    And what happens when life interrupts — pause and resume without penalty
    And it addresses screen-time worry honestly — a bounded daily plan, no feeds to scroll

  @scenario:FQ-04
  Scenario: The money and safety questions are answered
    Given a visitor reads the FAQ page
    Then it states the price — $200 per month per family, cancel anytime
    And the refund policy — 14 days, full refund, no questions asked
    And it explains how the AI companion is governed and that data is never sold

  @scenario:FQ-05
  Scenario: The FAQ funnels to a conversation
    Given a visitor reaches the end of the FAQ page
    Then the page invites them to book a parent onboarding call for anything unanswered
