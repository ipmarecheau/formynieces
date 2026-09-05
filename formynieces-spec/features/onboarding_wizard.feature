@onboarding
Feature: Guided onboarding wizard
  Registration, child setup, the diagnostic and the child's tour already exist as their own
  capabilities (guardian_onboarding, diagnostic, app_tour). This feature is the connective layer
  above them: a single guided wizard that walks a NEW family through the whole first-run lifecycle
  end to end, so no one is ever left on a page wondering what to do next.

  For the GUARDIAN it is a persistent getting-started checklist — create the account, add a child,
  set her exam date, have her take the diagnostic, see her first lesson, and find the insights
  dashboard. It always shows where she is, points at the single next step, and resumes exactly where
  she left off, on any device. For the CHILD it is a guided first run — welcomed, walked into her
  diagnostic, shown her map, and handed into her first lesson — reusing the app tour, not replacing it.

  The wizard GUIDES, it never blocks: a family can wander off and explore, and the wizard waits. It
  reflects real progress (a step ticks itself off only when the underlying thing actually happened,
  including things the CHILD did), it celebrates once the full lifecycle is complete, and then it
  steps aside for good — reopenable on demand, but never nagging. It carries no new child data of its
  own; it only reads the state the other features already record.

  Background:
    Given a guardian has registered and verified her email
    And the onboarding wizard tracks her family's first-run progress

  # --------------------------------------------------------- the guardian's checklist

  @scenario:WZ-01
  Scenario: A new guardian is greeted by a getting-started wizard
    Given she has just verified her email and has no child yet
    When she lands on her dashboard
    Then a getting-started wizard shows the whole first-run journey as a checklist
    And each step says plainly what it is and why it matters

  @scenario:WZ-02
  Scenario: The wizard always points at the single next step
    Given some onboarding steps are done and some are not
    When she opens the wizard
    Then it shows her overall progress
    And it highlights exactly ONE next step with a button that takes her straight there

  @scenario:WZ-03
  Scenario: A step ticks itself off only when the real thing happens
    Given the "add a child" step is not yet done
    When she creates a child profile
    Then that step is marked complete on the wizard
    And the next step, "take the diagnostic", becomes the highlighted one

  @scenario:WZ-04
  Scenario: The wizard resumes where she left off, on any device
    Given she completed some steps yesterday on her phone
    When she returns today on a laptop
    Then the wizard shows the same completed steps and the same next step
    And her progress was never lost between sessions

  @scenario:WZ-05
  Scenario: Adding the child captures her exam date and unlocks pacing
    Given adding a child asks for her SEA exam year as part of setup
    When the guardian completes the "add a child" step
    Then that child's exam year is saved without a separate wizard step
    And pacing can now keep her ahead of where she needs to be

  # --------------------------------------------------------- the child's progress, reflected

  @scenario:WZ-06
  Scenario: The wizard reflects what the CHILD has done, back to the guardian
    Given her child has completed the diagnostic and opened her first lesson
    When the guardian opens the wizard
    Then the "take the diagnostic" and "see her first lesson" steps show as complete
    And she can see her child is genuinely underway, without standing over her

  # --------------------------------------------------------- the child's guided first run

  @scenario:WZ-07
  Scenario: A child is walked through her own first run
    Given a newly created child logs in for the first time
    When her first session begins
    Then she is welcomed, taken into her diagnostic, shown her map, and handed her first lesson
    And this reuses the app tour rather than a second, competing tour

  # --------------------------------------------------------- guides, never blocks

  @scenario:WZ-08
  Scenario: The wizard never blocks exploring the app
    Given the onboarding is only part done
    When she navigates away to explore the app
    Then nothing is locked or forced
    And the wizard can be minimised and reopened whenever she wants it

  # --------------------------------------------------------- completion, then out of the way

  @scenario:WZ-09
  Scenario: Finishing the lifecycle celebrates, then retires the wizard
    Given every onboarding step is complete
    When she next visits her dashboard
    Then the wizard congratulates her that the family is fully set up
    And it retires itself and does not appear again unless she reopens it

  @scenario:WZ-10
  Scenario: The wizard can be reopened any time to check remaining steps
    Given she dismissed the wizard with steps still incomplete
    When she chooses to reopen getting-started
    Then it returns showing the same progress and remaining steps
    And nothing she already did was reset
