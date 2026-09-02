@roadmap @student @guardian @monetization
Feature: Free plan — the map and mastery quizzes, with teaching behind the wall
  A permanently-free plan is the top of the funnel — it neutralises the competitor's
  "free forever" tier while deliberately withholding the product's crown jewels so the
  gap it creates drives the upgrade.

  A free student gets exactly two things: the Voyage MAP (they can navigate every island
  and see the whole SEA journey) and the MASTERY QUIZZES (they may attempt a module's
  competency check and, if they clear it first try, TEST OUT and earn the mastery star —
  the same "test out" path paid students have). Nothing that TEACHES is included: no
  interactive lessons, no Smooth tutorials, no level explainer, and — critically — no
  AI re-teach when they miss. When a free student misses, they see an honest "not yet"
  and a single, contextual upgrade nudge naming the very thing they'd unlock; there is no
  scaffolding and no second-chance remediation. The daily rituals (writing/essay,
  vocabulary and reading — Morning Tide), the pace insights and the SEA placement
  Estimator, every AI feature (Ask-Smooth, writing feedback, summaries), and the full
  honest-layer parent reporting are all paid-only.

  A free student still gets a SHORT diagnostic so the map is seeded and meaningful, and
  quizzes are UNLIMITED (the quiz bank is static, so acquisition is not rate-limited).
  A free guardian sees only a bare mastery count and last-active date — none of the
  honest-layer analytics, AI summary, pace, Estimator, journal or weekly report.

  Upgrading to paid unlocks everything at once. The paywall is never a dead end: every
  locked surface explains what it teaches and offers the upgrade in one tap.

  Background:
    Given a guardian on the free plan with a child "Aanya"
    And Aanya has completed the short free-plan diagnostic
    And her map shows a module "Fractions" with status "needs_work"

  # --------------------------------------------------- what the free plan includes

  @scenario:FP-01
  Scenario: A free student can navigate the whole Voyage map
    When Aanya opens her Voyage
    Then she sees the full island map for every SEA strand
    And she can open any unlocked island to view its modules
    And no island is hidden or blurred behind the paywall

  @scenario:FP-02
  Scenario: A free student may attempt a module's mastery quiz
    When Aanya opens the "Fractions" module
    Then she is offered the mastery quiz (the competency check)
    And she can answer its questions and submit them
    And the level explainer, lesson and tutorials are not shown to her

  @scenario:FP-03
  Scenario: A free student who tests out earns the mastery star
    Given the "Fractions" competency check has six questions across D1, D3 and D5
    When Aanya answers all six correctly on the first try
    Then the module is marked "mastered"
    And she earns the mastery star without ever opening a lesson
    And her map reflects the newly mastered island

  @scenario:FP-13
  Scenario: Mastery quizzes are unlimited on the free plan
    When Aanya attempts and completes ten different mastery quizzes in one day
    Then she is never blocked by a daily quiz cap
    And no "come back tomorrow" limit is shown

  @scenario:FP-14
  Scenario: A free student is seeded with a short diagnostic so the map is alive
    Given a brand-new free-plan child "Kai" who has not been assessed
    When Kai starts his Voyage for the first time
    Then he is given a SHORT diagnostic (not the full paid diagnostic)
    And its result seeds his map with a realistic spread of statuses
    And his map is never empty on first open

  # --------------------------------------------------- the teaching wall

  @scenario:FP-04
  Scenario: Lessons are locked on the free plan
    When Aanya tries to open the interactive lesson for "Fractions"
    Then she is shown the upgrade wall instead of the lesson
    And the wall names what the lesson would teach her
    And it offers a one-tap path to upgrade

  @scenario:FP-05
  Scenario: Tutorials (Smooth's worked examples) are locked
    When Aanya tries to open the tutorials for "Fractions"
    Then she is shown the upgrade wall instead of the tutorials

  @scenario:FP-06
  Scenario: The level explainer intro is locked — the free plan is purely test-yourself
    When Aanya opens the "Fractions" module
    Then the plain-language level explainer is not shown
    And she goes straight to the mastery quiz option

  @scenario:FP-07
  Scenario: Missing a quiz on free shows an honest "not yet" and a single upgrade nudge
    When Aanya answers a "Fractions" quiz question incorrectly
    Then she sees a "not yet" message framed as not-failure
    And she sees one contextual nudge: Smooth can re-teach this if she upgrades
    And she is offered no scaffolding, hint or second-chance remediation

  @scenario:FP-08
  Scenario: The AI re-teach never triggers on the free plan
    Given Aanya misses several "Fractions" questions in a row
    When the paid learning loop would normally pull her into an AI-assisted re-teach
    Then no re-teach is started on the free plan
    And she instead reaches the upgrade wall for the re-teach

  # --------------------------------------------------- paid-only surfaces

  @scenario:FP-09
  Scenario: The daily writing / essay track is locked on free
    When Aanya opens the daily writing task
    Then she is shown the upgrade wall for the writing track

  @scenario:FP-10
  Scenario: The vocabulary and reading rituals are locked on free
    When Aanya opens Morning Tide (the daily vocabulary and reading ritual)
    Then she is shown the upgrade wall for the daily rituals

  @scenario:FP-11
  Scenario: Pace insights and the SEA placement Estimator are locked on free
    When the guardian opens Pace or the Estimator
    Then they are shown the upgrade wall for pace and placement insights
    And no projected first-choice placement is calculated on the free plan

  @scenario:FP-12
  Scenario: Every AI feature is locked on free
    When Aanya tries to open Ask-Smooth, request AI writing feedback, or view an AI summary
    Then each is shown the upgrade wall
    And no LLM call is made for a free-plan account

  # --------------------------------------------------- limited parent reporting

  @scenario:FP-15
  Scenario: A free guardian sees only a bare mastery count, not the honest layer
    When the free-plan guardian opens their portal
    Then they see only "X of 90 modules mastered" and Aanya's last-active date
    And they do NOT see the readiness verdict, exam-agent summary, pace, Estimator,
      school journal or weekly report
    And each withheld panel shows an upgrade prompt rather than the data

  # --------------------------------------------------- the upgrade

  @scenario:FP-16
  Scenario: Upgrading unlocks the whole product at once
    Given the guardian is on the free plan
    When they upgrade to a paid plan
    Then lessons, tutorials, explainers and the AI re-teach unlock for Aanya
    And the daily writing, vocabulary and reading rituals unlock
    And pace, the Estimator and the full honest-layer reporting unlock for the guardian
    And Aanya keeps every mastery star she earned while on the free plan

  @scenario:FP-17
  Scenario: The upgrade wall is always contextual, never a generic dead end
    When Aanya reaches the upgrade wall from a specific locked surface
    Then the wall names that exact surface (its lesson, ritual or feature)
    And it offers the upgrade in one tap
    And it offers a way back to the free surfaces she can still use
