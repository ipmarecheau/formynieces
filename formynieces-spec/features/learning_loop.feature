@mvp @student
Feature: Module learning loop
  A student earns a competency one module at a time through a repeating loop —
  Explainer → Competency Check → (Lesson → Tutorials → Practice) → Competency Check.
  Opening a level first explains the loop in her own language, then offers a fast
  COMPETENCY CHECK: one question at each real difficulty — D1, D3, D5. Clear all three
  on the first try and she has tested out — the module is mastered without ever touching
  the lesson. If she does not test out, she CHOOSES her way in: the interactive LESSON,
  the worked-example TUTORIALS (walked through by Smooth), or straight to PRACTICE. The
  lesson and tutorials are OPTIONAL scaffolding she pulls in only when she needs them.
  Every wrong answer is framed as not-yet, and remediation is offered — never forced as
  failure. This feature encapsulates the tutorial stage; the interactive lesson itself is
  specified in lesson.feature.

  Practice climbs the question bank's real difficulty levels D1 → D3 → D5 as three rungs;
  advancing a rung takes three distinct first-try-correct in a row, and true mastery is
  three first-try-correct in a row at D5. Every question allows a SECOND attempt so she can
  learn and move on, but mastery is reserved for FIRST-TRY success — you cannot master on
  retries. When practice shows she is struggling — two questions missed on both attempts in
  a row at D3 or D5, or five of her last seven missed — she is pulled back into an
  AI-assisted re-teach (lesson and/or tutorial) that pushes her until she understands. Once
  she proves it, she re-enters practice at D3, never punished back to the bottom.

  Background:
    Given a student has completed her diagnostic
    And her map shows a module with status "needs_work"

  # ------------------------------------------- opening a level: explainer + check

  @scenario:LL-19
  Scenario: Opening a level explains the loop in her own language
    When she opens a module from her map
    Then she is greeted with a short student-language explanation of how the loop works
    And the explanation leads her into the competency check

  @scenario:LL-20
  Scenario: The competency check is a fast test-out at D1, D3 and D5
    Given she has read the loop explanation for a needs_work module
    When she is given one question at each difficulty — D1, then D3, then D5
    And she answers all three correctly on the first try
    Then the module's status becomes "mastered"
    And she never had to open the lesson or the tutorial
    And the check only ever serves her questions she has not seen before

  @scenario:LL-21
  Scenario: Failing the competency check offers a choice of lesson, tutorial or practice
    Given she has taken the competency check for a module
    When she does not clear all three questions on the first try
    Then the module is not mastered
    And she is offered a choice of the lesson, the tutorial, or practice
    And nothing about the miss is framed as failure

  # ------------------------------------------------------------------ the climb

  @scenario:LL-01
  Scenario: The module lesson shows its human-vetted description and resources
    When she opens the module's lesson
    Then she sees the module's description and its human-vetted resources
    And she can start the tutorial or practice from the lesson

  @scenario:LL-02
  Scenario: She can reach practice for a needs_work module from her map
    When she views her map
    Then the needs_work module offers a way to start practising

  @scenario:LL-03
  Scenario: Practice climbs the three difficulty rungs D1, D3, D5
    Given she is practising a module at the easiest rung, difficulty 1
    When she answers three distinct questions correctly in a row at her current rung
    Then she advances to the next rung, following the order D1 -> D3 -> D5
    And her progress increases

  @scenario:LL-12
  Scenario: Every question allows a second attempt, and a recovery still advances the climb
    Given she answers a practice question incorrectly on her first try below the hardest rung
    When she is given a second attempt and answers it correctly
    Then the recovered question counts toward advancing the rung
    And she sees the explanation framed as not-yet, with no failure language

  @scenario:LL-04
  Scenario: Failing a question resets the current streak but keeps the rung
    Given she has answered two questions correctly in a row at her current rung
    When she answers the next question incorrectly on both attempts
    Then her streak returns to zero
    And she remains on the same rung
    And she sees the question's explanation framed as not-yet, with no failure language

  @scenario:LL-05
  Scenario: A repeated question does not pad the streak
    Given she has correctly answered a question that is part of her current streak
    When she answers that same question correctly again
    Then her streak does not increase

  @scenario:LL-06
  Scenario: Mastery is earned by three first-try-correct in a row at difficulty 5
    Given she is practising at the hardest rung, difficulty 5
    When she answers three distinct questions correctly on the first try in a row
    Then the module's status becomes "mastered"
    And her progress reaches one hundred
    And her prior score is preserved as the previous score
    And a celebration is shown

  @scenario:LL-18
  Scenario: A question is never repeated for a student across the loop
    Given a student has already been shown a question anywhere in the loop
    When she is served her next question
    Then it is a question she has not seen before
    And every question shown to her is recorded so it is never repeated

  @scenario:LL-13
  Scenario: A retry never earns mastery
    Given she is practising at the hardest rung, difficulty 5
    When she answers a question correctly only on her second attempt
    Then that question does not count toward the mastery streak
    And her first-try miss resets any mastery streak in progress
    And she stays unmastered until she strings three first-try-correct answers

  # ---------------------------------------------------- the tutorial stage (folded)

  @scenario:TU-01
  Scenario: The tutorial sits between the lesson and practice
    Given the module has human-vetted worked examples
    When she finishes reading the module's lesson
    Then she can start the worked examples from the lesson
    And from the worked examples she can move on to practice

  @scenario:TU-02
  Scenario: A worked example reveals the method one step at a time
    Given she is viewing a worked example walked through by Smooth
    When she advances through it
    Then each step of the solution is revealed in order
    And the final answer is shown at the end

  @scenario:TU-03
  Scenario: The tutorial is never scored
    Given she is working through the module's worked examples
    When she completes them
    Then her module progress is unchanged
    And her mastery status is unchanged

  @scenario:TU-04
  Scenario: The tutorial can be revisited freely
    Given she has already been through the module's worked examples
    When she returns to them later
    Then she can work through them again with no penalty and no limit

  @v1.1 @scenario:TU-05
  Scenario: An interactive worked example asks her to drive the method
    Given a worked example has interactive steps
    When she is asked to choose the next step
    And she chooses a step that is not the expected one
    Then she sees a gentle nudge toward the method
    And her choice is not scored and does not affect her progress

  # --------------------------------------------------------- pushed remediation

  @scenario:LL-14
  Scenario: Two missed questions in a row at D3 or D5 trigger an AI-assisted re-teach
    Given she is practising at difficulty 3 or difficulty 5
    When she misses two questions in a row on both attempts
    Then she is pulled back into an AI-assisted re-teach for the module
    And she may redo the lesson and/or the tutorial with AI assistance
    And it is framed as a re-teach, not a failure

  @scenario:LL-22
  Scenario: Five misses in her last seven questions also trigger the re-teach
    Given she is practising a module
    When five of her seven most recent questions were missed on both attempts
    Then she is pulled back into an AI-assisted re-teach for the module
    And she may redo the lesson and/or the tutorial with AI assistance
    And it is framed as a re-teach, not a failure

  # The re-teach re-walks the REAL interactive lesson. Smooth stays quiet while she is on track and
  # only engages when she stumbles — one driver at a time (the lesson, or Smooth's chat), handing the
  # baton back and forth. All remediation content is drawn from the LESSON BLOCK she missed (its
  # authored rule + same-rule practice items), never from elsewhere in the module.

  @scenario:LL-15
  Scenario: A missed lesson step in the re-teach opens Smooth's same-rule remediation
    Given she is re-walking the lesson in an AI-assisted re-teach
    And she answers an interactive step wrong on both of her two tries
    Then the lesson pauses and Smooth's chat takes over, framed as help, not failure
    And Smooth checks and explains ONLY the rule that step teaches
    And when the remediation is complete the lesson resumes and re-asks that same step

  @scenario:LL-24
  Scenario: The re-teach chat only ever tests what the lesson block teaches
    Given a lesson block teaches one rule and carries its own same-rule practice items
    When Smooth remediates that block in a re-teach
    Then every word Smooth tests uses that block's rule and its practice items
    And Smooth never tests a different rule from elsewhere in the module

  @scenario:LL-25
  Scenario: Smooth explains the rule, then the child says it back in her own words
    Given she answered Smooth's same-rule check wrong
    Then Smooth explains the rule in plain words
    And asks her to say the rule back in her own words
    And when her answer is close enough the lesson quiz returns
    And after a few unclear tries Smooth accepts it and moves on, never leaving her stuck

  @scenario:LL-26
  Scenario: A wrong re-asked quiz shows the answer and tries another same-rule word
    Given the lesson quiz returned to her after remediation
    When she answers it wrong
    Then the correct answer is shown to her, kindly
    And Smooth tests another word that uses the SAME rule

  @scenario:LL-27
  Scenario: After three remediation cycles the lesson is left "in progress"
    Given she has been through three remediation cycles on the same block
    Then she may finish or leave the lesson and move on to other lessons
    And this lesson is marked "in progress" for her
    And it returns for her each day until she completes it
    # Phase 2 (roadmap): her parent is notified with action items and given a worksheet printout.

  @scenario:LL-16
  Scenario: Proving understanding returns her to practice at difficulty 3
    Given she is in an AI-assisted re-teach after struggling in practice
    When she proves she understands by answering three questions correctly
    Then the AI assistance steps back
    And she resumes solo practice at difficulty 3
    And she is never sent back to the easiest rung as a punishment

  # ------------------------------------------------------------------ maintenance

  # Once mastered, a level is LOCKED for a two-week maintenance window: opening it shows a
  # "come back in N days" confirmation, never the loop. On the due day (mastered + 2 weeks)
  # the re-mastery check unlocks for a FIVE-DAY grace, and the level glows on her map. Re-
  # master in the grace and the window resets; let the grace expire and it decays to review.

  @scenario:LL-23
  Scenario: A mastered level greets her with a maintenance confirmation, not the loop
    Given she has mastered a module
    And its two-week maintenance window has not yet come due
    When she opens that level
    Then she sees a confirmation that she has mastered it
    And she is told to come back in N days to keep it
    And she is shown neither the loop explainer nor the competency check

  @scenario:LL-24
  Scenario: Re-mastery unlocks only on the due day, for a five-day grace
    Given she mastered a module two weeks ago
    When she opens that level on or after its due day
    Then the re-mastery check is available
    And re-mastering it (three difficulty-5 questions first-try-correct) resets the two-week window
    And the check could not have been started before the due day

  @scenario:LL-25
  Scenario: A level due for review glows on her map during its grace period
    Given a mastered module has reached its due day
    And its five-day grace period has not expired
    When she views her map
    Then that level glows with a pulsing red outline to show it needs review

  @scenario:LL-17
  Scenario: A mastered competency must be maintained or it slips to review
    Given a module was mastered
    When its two-week maintenance window comes due
    And its five-day grace passes without three difficulty-5 questions answered first-try-correct
    Then its status becomes "mastered_review"
    And it becomes eligible for a future weekly target
    And answering three difficulty-5 questions first-try-correct restores it to "mastered"

  @v1.1 @scenario:LL-07
  Scenario: Stale mastery in a weak strand decays into review
    Given a module was mastered more than 6 weeks ago in a strand the exam agent flags as weak
    When the weekly agent review runs
    Then the module's status becomes "mastered_review"
    And the module becomes eligible for a future weekly target

  # ---------------------------------------------------- the loop is visible (stepper)

  @scenario:LL-08
  Scenario: A loop stepper shows her current stage for every module
    Given she has opened a module
    When she views the module
    Then she sees a stepper of the loop stages: lesson, tutorial, practice, check
    And her current stage is highlighted
    And the stepper has the same layout for every module

  @scenario:LL-09
  Scenario: A wrong answer offers a correction targeted to her mistake
    Given she answers a practice question incorrectly
    When the correction is shown
    Then it names the specific misconception behind the option she chose
    And it shows a worked example addressing that misconception
    And it is framed as not-yet, with no failure language

  @scenario:LL-10
  Scenario: Failing the check offers another tutorial before she retries
    Given she has not yet mastered the module
    And she has just answered incorrectly at the check stage
    When she is returned to the loop
    Then she is offered the module's tutorial again before her next attempt
    And taking it is optional and never scored

  @scenario:LL-11
  Scenario: The competency check is the mastery climb, shown as its own stage
    Given she is at the check stage of the loop
    When she answers one question at each of D1, D3 and D5 correctly on the first try
    Then the module's status becomes "mastered"
    And the check stage is marked complete on the stepper
    # The test-out shape is defined in LL-20; this scenario is the same check seen
    # as the loop's final stage. Maintenance re-checks draw three D5 (LL-24).
