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
#   Writing  1 sample / 20 marks in the real SEA — a parallel paper. NOTE: our
#           diagnostic does NOT collect a free-text sample; it tests writing
#           KNOWLEDGE (modules 69–72) via dedicated multiple-choice anchors.
# ─────────────────────────────────────────────────────────────────────────

# ─────────────────────────────────────────────────────────────────────────
# PREREQUISITE GRAPH — design contract (Slice 2a, seeded & structurally tested)
#
# The graph (module_prerequisites) is DENSE by design: 150 directed edges
# (86 Math, 64 ELA), acyclic, every edge "B requires A". The density lives in
# the GRAPH; the CAUTION lives in this ENGINE. The engine propagates mastery
# CONSERVATIVELY — it infers a prerequisite mastered only on unambiguous
# evidence, and a failed harder item un-marks the inferred chain between
# (the conservative walk-back). Rationale: a false "mastered" makes a child
# SKIP needed practice (harmful); under-inference is safe.
#
# WRITING NODES (modules 69–72) are their OWN diagnostic track, tested by
# dedicated multiple-choice anchors ABOUT writing (essay types, topic sentences,
# figurative language, organisation) — NOT by a free-text sample. Answering a
# Writing anchor sets mastery on that Writing module DIRECTLY. However, the
# engine MUST NOT propagate mastery THROUGH a writing node into the ELA reading
# modules it references: covering module 86 must not infer writing module 71,
# and Writing mastery must not flow down into modules like 81/82 via 71. The
# graph carries full edges in and out of writing so they can be struck later;
# the engine honours this no-propagation-through-writing rule regardless.
#
# Module-id ranges: Math 1–51, ELA 52–90 (Writing 69–72, diagnosed separately).
# ─────────────────────────────────────────────────────────────────────────

Feature: Adaptive diagnostic
  To start every girl at the right place on her adventure map, the system runs
  a warm adaptive multiple-choice diagnostic that infers mastery across all 90
  modules from roughly 30 anchor items spanning Math, ELA, and Writing.

  Background:
    Given the anchor question bank is seeded
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

    Scenario: A wrong answer descends the difficulty ladder
      Given a student is presented a medium-difficulty Math Number anchor
      When she answers it incorrectly
      Then the next Math Number anchor presented is easier
      And the misconception encoded by her chosen distractor is recorded

  Rule: Mastery propagates conservatively along the prerequisite graph

    # Ids below are real edges in module_prerequisites (Slice 2a). They pin the
    # walk to the seeded graph: a regression in propagation fails these.

    Scenario: A correct anchor infers its direct prerequisites
      Given a student is presented an anchor for Math module 23 "Percent of a Quantity"
      When she answers it correctly
      Then module 22 "Convert F/D/Percent" is marked inferred mastered
      And module 15 "Fractions of a Collection" is marked inferred mastered

    Scenario: Inference is transitive along a prerequisite chain
      Given a student is presented an anchor for Math module 27 "Multi-step Money"
      When she answers it correctly
      Then its direct prerequisites 25, 21, 26 and 49 are marked inferred mastered
      And the chain beneath them — modules 23, 22 and 12 — is also marked inferred mastered

    Scenario: Inference requires unambiguous evidence, not a lucky guess
      Given a student is presented a Math Number anchor
      When she answers it correctly with low confidence flagged by a fast random-looking response
      Then no prerequisite module is marked inferred mastered on that evidence alone

  Rule: A contradicting harder item walks back earlier inference

    Scenario: A failed harder item un-marks a previously inferred module
      Given a student answered Math module 13 "Add/Subtract Fractions" correctly
      And module 12 "Equivalent Fractions" was inferred mastered as a result
      When she answers a harder anchor for module 16 in the same prerequisite chain incorrectly
      Then module 12 is no longer marked inferred mastered
      And module 13 is no longer marked inferred mastered

    Scenario: Walk-back is bounded to the contradicted chain
      Given a student has inferred mastery across the Fractions chain and the Geometry chain
      When she answers a harder Fractions anchor incorrectly
      Then only Fractions-chain modules between the two anchors are un-marked
      And inferred mastery in the unrelated Geometry chain is unchanged

  Rule: Writing modules are mastered by their own anchors, never by propagation

    # Writing (69–72) is diagnosed by dedicated MCQs about writing. A correct
    # Writing anchor masters its module directly. What must NOT happen is mastery
    # flowing THROUGH a writing node along the prerequisite graph — in either
    # direction — into the ELA reading modules it shares edges with.

    Scenario: A correct Writing anchor masters its module directly
      Given a student is presented a Writing anchor for module 71 "Figurative Language"
      When she answers it correctly
      Then writing module 71 is marked mastered
      And no ELA reading module is marked inferred mastered as a side effect

    Scenario: A writing node is not inferred from a poetry module above it
      Given the graph contains an edge from poetry module 86 to writing module 71
      And a student answered an anchor for module 86 correctly
      When prerequisite inference runs
      Then writing module 71 is not marked inferred mastered by that propagation

    Scenario: Mastery does not flow through a writing node to its prerequisites
      Given writing module 71 references poetry modules 81 and 82 in the graph
      And a student answered a Writing anchor for module 71 correctly
      When prerequisite inference runs
      Then poetry modules 81 and 82 are not marked inferred mastered through module 71

  Scenario: A new session plans items per the 50/30/20 paper weighting
    Given a student whose onboarding is not completed
    When a diagnostic session is started for her
    Then the item plan allocates approximately 15 Math anchors across the four strands with Number weighted heaviest
    And a roughly even split of ELA anchors between Section I (spelling, punctuation, grammar) and Section II (reading comprehension)
    And a small set of Writing anchors covering narrative, expository, figurative language, and organisation

  Scenario: An encouraging interstitial appears every eighth item
    Given a student has answered 7 items in her session
    When she answers the 8th item
    Then a brief interstitial praising effort is shown before the next item

  Scenario: An interrupted session resumes
    Given a student closed the browser midway through a diagnostic session
    When she logs in again
    Then she is offered to continue the open session
    And her previous responses are preserved

  Scenario: Writing is diagnosed by multiple-choice anchors, scoped to its own modules
    Given a student has reached the Writing anchors in her session
    When she answers them
    Then only Writing modules 69 to 72 change mastery as a result
    And no Math or ELA reading module's mastery status changes from the Writing answers

  @v1.1
  Scenario: A guardian initiates a diagnostic retake
    Given a student who completed her diagnostic more than 8 weeks ago
    When her guardian requests a retake
    Then a new diagnostic session is created for the student
    And progress earned through the learning loop is unchanged
