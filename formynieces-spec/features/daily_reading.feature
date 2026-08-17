@mvp @student @admin @system
Feature: Daily reading and comprehension
  A daily reading ritual — one passage plus a comprehension check — provided by the app and
  sized so a Std-5 student finishes in about ten to fifteen minutes. Its goals are to
  strengthen reading, deepen comprehension, and — through a short written response — feed the
  writing skill. Passages are AUTHORED OR IMPORTED IN ADVANCE into a level-keyed pool, never
  generated in real time; the day's vocabulary is drawn from the same passage so words are met
  and tested in context. Comprehension IS scored and the score is KEPT: the platform tracks
  each student toward a 95% comprehension goal and a healthy reading pace for her age band.
  This progress is a KIND signal — it earns perks and never becomes a letter grade, a
  pass/fail, or a gate, and never changes module mastery. The child sees only warm
  encouragement; the numbers live with the system and the guardian.
  # For now the LLM helps carry content, scoring and examples; a curated,
  # syllabus-aligned essay & word bank is the roadmap successor (essay_word_bank).

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

  @scenario:DR-07
  Scenario: Comprehension is scored and the score is kept
    Given she has answered the comprehension check
    When her answers are graded
    Then her comprehension score for that passage is recorded against the day
    And her running comprehension average is updated
    And the raw score is a system/guardian-layer number, never shown to her as a grade

  @scenario:DR-08
  Scenario: Progress is tracked toward a 95% comprehension goal and a healthy reading pace
    Given she has completed morning reading on several days
    When her reading progress is read
    Then her comprehension average is tracked against a 95% goal
    And her reading pace in words per minute is tracked against an age-appropriate band
    And both trends are available in the guardian/honest layer over time

  @scenario:DR-09
  Scenario: Strong reading progress earns perks, never a gate
    Given she reaches or holds the 95% comprehension goal, or improves her reading pace
    When her progress is tallied
    Then she earns a perk for it (a streak reward or XP)
    And falling short of the goal never blocks the map, breaks a streak, or shames her

  @scenario:DR-10
  Scenario: The reading and comprehension are sized to ten to fifteen minutes
    Given a passage served to a Std-5 reader at her level
    When the assignment is prepared
    Then the passage length and question count are sized for a ten-to-fifteen-minute session
    And the daily vocabulary that follows is additional to this reading time
