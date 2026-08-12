@mvp @student @admin @system
Feature: Daily reading and comprehension
  A short morning reading ritual: one passage plus a comprehension check, sized to be
  done on a device on the way to school. Its goals are to strengthen reading, deepen
  comprehension, and — through a short written response each day — feed the writing skill.
  Passages are AUTHORED OR IMPORTED IN ADVANCE into a level-keyed pool; they are never
  generated in real time. Each morning the student is served one unseen passage matched to
  her reading level. Like the writing track, reading is FORMATIVE: it earns warm feedback and
  a daily streak, but never a grade, never pass/fail, and never a change to module mastery.
  Together with daily vocabulary it is the roughly fifteen-minute morning routine.

  Background:
    Given a student has completed her diagnostic
    And she has an established reading level
    And the reading pool holds unseen passages across reading levels

  @scenario:DR-01
  Scenario: A daily morning reading assignment is served from the pool
    When she opens her morning reading
    Then she receives one unseen passage matched to her reading level
    And it is served from stored pool content, not generated in real time
    And she is given a single assignment for the day, not a backlog to binge
    And the passage is sized to be read in a few minutes on a phone

  @scenario:DR-02
  Scenario: A comprehension check follows the passage
    Given she has read the morning passage
    When she moves on to the comprehension check
    Then she answers a short set of questions about the passage
    And the questions mix literal recall with inference that makes her think
    And at least one question asks for a short written response in her own words

  @scenario:DR-03
  Scenario: Reading and comprehension are formative and feed writing, not mastery
    Given she has answered the comprehension check
    When her answers are returned
    Then she sees warm feedback that names what she understood well and one thing to look for
    And no letter grade or pass/fail status is shown
    And no module's mastery status changes
    And her short written response is treated as writing practice, not a scored answer

  @scenario:DR-04
  Scenario: Reading difficulty adapts to her level over time
    Given she has completed morning reading on several days
    When the next morning assignment is prepared
    Then its passage difficulty tracks her current reading level
    And the level nudges up as she consistently comprehends well
    And it eases back if she consistently struggles

  @scenario:DR-05
  Scenario: The ride-to-school ritual is mobile-first and resumable
    Given she starts her morning reading on a phone during her commute
    When her session is interrupted before she finishes
    Then her place in the passage and her answers so far are preserved
    And she can resume the same assignment later that morning without losing progress
    And completing the reading advances her daily reading streak
    And the streak is visible on her Voyage home

  @scenario:DR-06
  Scenario: An admin stocks the level-keyed reading pool in advance
    Given an admin is building the daily reading pool
    When they author or import passages with their comprehension questions
    Then each passage is stored with its reading level and its questions
    And passages become available to be served on future mornings
    And the same passage is never served to a student who has already seen it
