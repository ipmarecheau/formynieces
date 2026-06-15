@mvp @student @system

# ─────────────────────────────────────────────────────────────────────────
# SEA SCORING MODEL — canonical reference (MoE Assessment Framework 2025–2028
# + SEA 2023 Information Booklet). Governs item allocation and ExamAgentInsight.
#
# Three papers, weighted 100:60:40 (Math:ELA:Writing) = 50/30/20 normalised.
# Pipeline: raw → scale Math/ELA to 100 → per-paper standard score (z against
# the year's national mean/SD) → weight → sum = Composite Standard Score.
#
# CRITICAL: the composite is NOT computable from public data. Per-paper national
# mean and SD are released per cohort, never published in advance, so the real
# composite (e.g. the sample report's 234.567) cannot be reproduced from a
# child's three scores. Therefore this platform produces an ESTIMATE OF
# PREPAREDNESS over the 50/30/20 weighting — never a predicted composite or
# school placement. (A four-coefficient linear fit could in theory recover the
# pipeline, but needs many real score→composite pairs we cannot obtain, so it
# is not an actionable path.)
#
# Two distinct senses of "subject impact" — both true, do not conflate:
#   • Total weight:   Math > ELA > Writing  → drives overall study PRIORITY.
#   • Per-raw-mark:   Writing is high-leverage (20-pt scale → each mark large);
#                     exact ranking depends on the unknown per-paper SD, so
#                     treat as "do not neglect Writing", NOT as a hard rule.
#
# Only MoE-defined anchor available: composite ≤ 30% triggers a mandatory
# re-sit ("not yet mastered basic numeracy/literacy"). Use as the honest floor
# for guardian messaging, never as a predicted score.
#
# Paper structure (for anchor-bank sizing, Slice 2):
#   Math    75 marks / 40 items — Number heaviest (19 items).
#   ELA     64 marks / 36 items — Section I 18 items; Section II (reading
#           comprehension) 18 items — an EVEN split.
#   Writing  1 sample / 20 marks — parallel track, 1 diagnostic sample only.
# ─────────────────────────────────────────────────────────────────────────

Feature: Adaptive diagnostic
  To start every girl at the right place on her adventure map, the system runs
  a warm adaptive multiple-choice diagnostic that infers mastery across all 90
  modules from roughly 30 anchor items plus one writing sample.

  Background:
    Given the anchor question bank is seeded from past papers
    And the module prerequisite graph is seeded

  Rule: The diagnostic is framed as an adventure, never as a test

    Scenario: The intro uses adventure framing
      Given a student whose onboarding is not completed
      When she opens the diagnostic intro
      Then it describes a quick adventure to find her starting point
      And no timer element is rendered
      And no score element is rendered

    Scenario: A question screen hides scoring
      Given a student is partway through a diagnostic session
      When a question is presented
      Then she sees the question, its options, and progress dots only
      And no running score or correctness history is shown

  Rule: The item walk adapts to performance per strand

    Scenario: A correct answer climbs the difficulty ladder
      Given a student is presented a medium-difficulty Math Number anchor
      When she answers it correctly
      Then the next Math Number anchor presented is harder
      And the answered anchor's prerequisite modules are marked as inferred mastered

    Scenario: A wrong answer descends the difficulty ladder
      Given a student is presented a medium-difficulty Math Number anchor
      When she answers it incorrectly
      Then the next Math Number anchor presented is easier
      And the misconception encoded by her chosen distractor is recorded

    Scenario: A contradicting harder item blocks lucky propagation
      Given a student answered a Math Number anchor correctly
      When she answers a harder anchor in the same prerequisite chain incorrectly
      Then the modules between the two anchors are not marked mastered

  Scenario: A new session plans items per the 50/30/20 paper weighting
    Given a student whose onboarding is not completed
    When a diagnostic session is started for her
    Then the item plan allocates approximately 15 Math anchors across the four strands with Number weighted heaviest
    And a roughly even split of ELA anchors between Section I (spelling, punctuation, grammar) and Section II (reading comprehension)
    And exactly one writing sample task

  Scenario: An encouraging interstitial appears every eighth item
    Given a student has answered 7 items in her session
    When she answers the 8th item
    Then a brief interstitial praising effort is shown before the next item

  Scenario: An interrupted session resumes
    Given a student closed the browser midway through a diagnostic session
    When she logs in again
    Then she is offered to continue the open session
    And her previous responses are preserved

  Scenario: The writing sample produces a rubric profile, not mastery
    Given a student has reached the writing sample task
    When she submits her writing sample
    Then it is scored against Content, Language Use, Grammar and Mechanics, and Organisation
    And no module's mastery status changes as a result

  @v1.1
  Scenario: A guardian initiates a diagnostic retake
    Given a student who completed her diagnostic more than 8 weeks ago
    When her guardian requests a retake
    Then a new diagnostic session is created for the student
    And progress earned through the learning loop is unchanged
