@mvp @student @system
Feature: Reveal and roadmap seeding
  When the diagnostic completes, its results seed progress for all 90 modules,
  compute the student's starting week, set her first weekly target, and present
  the result as an animated reveal so the map feels made for her.

  Scenario: Completion seeds a progress record for every module
    Given a student has answered the final diagnostic item
    When the diagnostic session completes
    Then a progress record exists for each of the 90 syllabus modules
    And inferred-mastered modules have status "diagnostic_passed"
    And all remaining modules have status "not_started"

Rule: The diagnostic result is reconciled against the guardian's stated weak areas

    Scenario: The diagnostic confirms the guardian's weak areas exactly
      Given a guardian stated known weak areas at child setup
      And the completed diagnostic identifies the same weak strands
      When her roadmap is generated
      Then the roadmap proceeds from the diagnostic result without a guardian decision

    Scenario: The diagnostic finds additional weak areas beyond the guardian's list
      Given a guardian stated known weak areas at child setup
      And the completed diagnostic identifies those strands and further weak strands
      When her roadmap is generated
      Then the roadmap proceeds from the diagnostic result without a guardian decision

    Scenario: The diagnostic finds fewer weak areas than the guardian stated
      Given a guardian stated known weak areas at child setup
      And the completed diagnostic clears one or more of those strands
      When her roadmap is generated
      Then the guardian is shown where the diagnostic and her input differ
      And she is offered to proceed with the diagnostic result or keep her stated weak areas
      And the reveal does not complete onboarding until she chooses

    Scenario: The guardian keeps her stated weak areas over the diagnostic
      Given the guardian has been shown a diagnostic that cleared a strand she flagged
      When she chooses to keep her stated weak area
      Then that strand's modules are treated as not-started in the roadmap
      And the remaining diagnostic results are applied unchanged
      
  Scenario: Completion computes the starting week and first target
    Given a student's diagnostic session has completed
    When her roadmap is generated
    Then her starting week is the earliest pacing week containing a not-started module
    And a weekly target for the current week is created from that week's not-started modules

  @scenario-S4
  Scenario: A late joiner receives a compressed, complete journey
    Given a student completes the diagnostic 12 weeks into the school year
    When her roadmap is generated
    Then her map spans from her computed starting week to exam week
    And no stop is labelled missed or overdue

  Scenario: The reveal plays and unlocks the dashboard
    Given a student's roadmap has been generated
    When the reveal screen loads
    Then she watches the map populate and her flag plant at her starting stop
    And what she already knows is described in celebratory terms
    And her onboarding is marked completed

  Scenario: An onboarded student logs in to the dashboard
    Given a student whose onboarding is completed
    When she logs in
    Then she is taken to her populated dashboard
