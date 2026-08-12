@mvp
Feature: AI governance — cost, time and tailoring guardrails
  Every LLM the platform uses — essay grading, the interactive lesson's clarify chat, the
  worked-example tutorials, and the re-teach guided practice — is bounded, so a child's
  learning is tailored without runaway cost or screen time. Three guardrails:

  COST. Each student has a monthly LLM budget of USD 1.50, metered from real token usage.
  At USD 1.00 the DISCRETIONARY features (clarify chat, re-teach, worked-example generation)
  stop and fall back; the ESSENTIAL ones (essay grading, guardian exam-agent summaries) keep
  running until the USD 1.50 hard ceiling, after which everything degrades gracefully. Budget
  is always checked BEFORE a call, so spend can never overshoot by more than one capped call.

  TIME. Guided, LLM-tailored learning — lessons, tutorials, clarify chat, re-teach — draws
  from a 2-hour daily pool of ACTIVE time (the Alpha "2-hour learning" model: focused tailored
  teaching, then lots of practice). Practice (the MC climb) uses no LLM and is UNLIMITED. When
  the daily pool is spent, guided activities lock kindly for the day; practice stays open.

  TAILORING. A compact per-student learning profile — a handful of derived tags, never chat
  transcripts, never PII — is stored and injected into the tutor prompts so guidance stays
  personal across ephemeral sessions.

  Background:
    Given a student on the platform

  # --------------------------------------------------------------------- cost

  @system @scenario:AG-01
  Scenario: Every LLM call is metered to the student's monthly ledger
    Given the platform makes an LLM call attributed to her
    When the provider returns the call's token usage
    Then her month-to-date input tokens, output tokens and cost are recorded
    And usage from a previous month does not count against this month

  @system @scenario:AG-02
  Scenario: At the soft cap, discretionary AI stops but essential AI keeps running
    Given her month-to-date LLM spend has reached USD 1.00
    When a discretionary feature (clarify chat, re-teach, worked example) needs the LLM
    Then no call is made and it degrades to its fallback
    But an essential feature (essay grading, guardian summary) still calls the LLM

  @system @scenario:AG-03
  Scenario: At the hard ceiling, all LLM calls stop
    Given her month-to-date LLM spend has reached USD 1.50
    When any feature needs the LLM
    Then no call is made and it degrades to its fallback

  @system @scenario:AG-04
  Scenario: Budget is checked before the call, so spend never overshoots
    Given she is one capped call below her ceiling
    When a feature needs the LLM and would exceed the ceiling
    Then the budget is checked and the call is skipped before any request is sent
    And no tokens are billed for a call that was never made

  # --------------------------------------------------------------------- time

  @student @scenario:AG-05
  Scenario: Guided learning draws from a two-hour daily pool; practice does not
    Given she is working through a lesson, tutorial, clarify chat or re-teach
    When her active time on that guided activity accrues
    Then it counts against her 2-hour daily guided-time pool
    And time spent in practice never counts against the pool

  @student @scenario:AG-06
  Scenario: When the guided pool is spent, guided locks for the day but practice stays open
    Given she has used her full 2 hours of guided time today
    When she opens a lesson or tutorial
    Then it is kindly locked until tomorrow, framed as a rest, not a punishment
    And she can still open practice for the same module without limit

  @student @scenario:AG-07
  Scenario: Only active time burns the guided pool
    Given she has a guided activity open but is idle
    When she is not actively engaged
    Then idle time does not draw down her guided-time pool

  # ----------------------------------------------------------------- tailoring

  @system @scenario:AG-08
  Scenario: A compact learning profile personalises the tutor without storing conversations
    Given the platform holds a small profile of derived tags for her (weak strands, misconceptions, style)
    When an AI tutor prompt is built for her
    Then the profile tags are injected so the guidance is tailored to her
    And no chat transcript or personal information is stored in the profile

  # ----------------------------------------------------------------- reporting

  @admin @scenario:AG-09
  Scenario: The admin panel reports each student's token usage and spend against the caps
    Given students have accrued LLM usage this month
    When an admin opens the AI usage panel
    Then each student's month-to-date tokens and spend are shown against the USD 1.00 and 1.50 marks
    And a roll-up total of tokens and spend across all students is shown

  @admin @scenario:AG-10
  Scenario: The admin panel reports each student's guided-time used today
    Given students have spent guided time today
    When an admin opens the AI usage panel
    Then each student's guided minutes used against the 2-hour daily pool are shown
