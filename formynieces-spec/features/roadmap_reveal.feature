@mvp @student @system
Feature: Reveal and roadmap seeding
  When the diagnostic completes, its results seed the student's progress map
  from the diagnostic, compute the student's starting week, set her first weekly
  target, and present the result as an animated reveal so the map feels made for her.

  @scenario:RR-01
  Scenario: Completion seeds the student's progress map
    Given a student has answered the final diagnostic item
    When the diagnostic session completes
    Then a progress record is written for every module the diagnostic assessed or inferred
    And each record carries an engine status of "mastered", "inferred_mastered", or "needs_work"
    And every progress record is accounted for on the roadmap with no orphaned modules
    And modules the diagnostic did not reach appear as upcoming by absence of a record

  Rule: The diagnostic result is reconciled against the guardian's stated weak areas

    @scenario:RR-02
    Scenario: The diagnostic confirms the guardian's weak areas exactly
      Given a guardian stated known weak areas at child setup
      And the completed diagnostic identifies the same weak strands
      When her roadmap is generated
      Then the roadmap proceeds from the diagnostic result without a guardian decision

    @scenario:RR-03
    Scenario: The diagnostic finds additional weak areas beyond the guardian's list
      Given a guardian stated known weak areas at child setup
      And the completed diagnostic identifies those strands and further weak strands
      When her roadmap is generated
      Then the roadmap proceeds from the diagnostic result without a guardian decision

    @scenario:RR-04
    Scenario: The diagnostic finds fewer weak areas than the guardian stated
      Given a guardian stated known weak areas at child setup
      And the completed diagnostic clears one or more of those strands
      When her roadmap is generated
      Then the guardian is shown where the diagnostic and her input differ
      And she is offered to proceed with the diagnostic result or keep her stated weak areas
      And the reveal does not complete onboarding until she chooses

    @scenario:RR-05
    Scenario: The guardian keeps her stated weak areas over the diagnostic
      Given the guardian has been shown a diagnostic that cleared a strand she flagged
      When she chooses to keep her stated weak area
      Then that strand's modules are treated as not-started in the roadmap
      And the remaining diagnostic results are applied unchanged

    @scenario:RR-10
    Scenario: An unanswered reconciliation auto-proceeds after three days
      Given the guardian has been shown a diagnostic that cleared a strand she flagged
      And three days pass without the guardian choosing
      When the reconciliation is resolved automatically
      Then the roadmap proceeds from the diagnostic result
      And the student's onboarding completes so her progress is not halted

    @scenario:RR-11
    Scenario: A pending student is held on a waiting page across logins
      Given a student's diagnostic cleared a strand her guardian flagged
      And her guardian has not yet decided
      When the student logs in again
      Then she is shown a waiting page naming her guardian's login
      And she can log out from it
      And she is not sent back into the diagnostic while the hold is unresolved
      And once the hold times out her next login proceeds her to the map

  @scenario:RR-06
  Scenario: Completion computes the starting week and first target
    Given a student's diagnostic session has completed
    When her roadmap is generated
    Then her starting week is the earliest pacing week containing a not-started module
    And a weekly target for the current week is created from that week's not-started modules

  @scenario:RR-07
  Scenario: A late joiner receives a compressed, complete journey
    Given a student completes the diagnostic 12 weeks into the school year
    When her roadmap is generated
    Then her map spans from her computed starting week to exam week
    And no stop is labelled missed or overdue

  @scenario:RR-08
  Scenario: The reveal plays and unlocks the dashboard
    Given a student's roadmap has been generated
    When the reveal screen loads
    Then she watches the map populate and her flag plant at her starting stop
    And what she already knows is described in celebratory terms
    And her onboarding is marked completed

  @scenario:RR-09
  Scenario: An onboarded student logs in to the dashboard
    Given a student whose onboarding is completed
    When she logs in
    Then she is taken to her populated dashboard
