@mvp @student
Feature: Smooth's guide — contextual how-to coaching
  A new student does not know the app's rules — that practice climbs three
  difficulty levels, that each question gives two attempts, or how the Voyage map
  unlocks. Smooth, the companion, teaches each screen in her own warm voice: a
  short how-to that appears on the first visit, can always be reopened, and never
  nags. The guide is child-layer only — it never shows pace, percentages, targets,
  or any guardian-gauge metric. It complements the companion's greeting
  (voyage_companion) — greeting narrates what is true; the guide explains what to do.

  Background:
    Given a signed-in student

  @scenario:SG-01
  Scenario: Smooth explains a screen on the first visit
    Given a student opens a screen she has not seen before
    When the screen loads
    Then Smooth appears with a short how-to for that screen
    And she can dismiss it and continue

  @scenario:SG-02
  Scenario: The guide never nags but is always reopenable
    Given a student has already dismissed a screen's guide
    When she returns to that screen
    Then the guide does not appear automatically again
    And she can reopen it at any time from Smooth's help control

  @scenario:SG-03
  Scenario: The practice guide explains the levels and the two attempts
    Given a student opens practice for a module for the first time
    When Smooth's guide appears
    Then it explains the climb has three levels — Level 1, Level 2, Level 3
    And that each question gives her two tries
    And that mastering needs three first-try-correct answers at the top level

  @scenario:SG-04
  Scenario: The Voyage guide explains how to progress on the map
    Given a student opens her Voyage for the first time
    When Smooth's guide appears
    Then it explains that this week's glowing islands are where to sail
    And that finishing a stop's loop unlocks the next stop
    And it shows no pace, percentage, or target count

  @scenario:SG-05
  Scenario: The guide stays in the child layer
    Given Smooth's guide is shown on any student screen
    When its content is composed
    Then it never shows a percentage, pace position, deficit, or target count
