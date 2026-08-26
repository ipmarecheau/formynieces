@mvp @student
Feature: Interactive module lesson
  When a student does not test out of a module's competency check, one of the ways in is
  the LESSON: an interactive teaching page for that module — as illustrative and captivating
  as a video, but as responsive and interactive as H5P content. Lessons are AUTHORED IN
  ADVANCE, one per module; they are never generated in real time. As she walks through a
  lesson she can open a clarify chat — an LLM tutor that explains and answers her questions
  but never generates the lesson and never scores her. The lesson is scaffolding: it changes
  nothing about her progress until she reaches practice. The ways in are stepped: she works the
  lesson, then the worked examples, then practice — each stage unlocking the next, for a module
  that has both a lesson and worked examples. The seam ships first as an interactive
  placeholder with the clarify chat wired up, so the loop can be proven end to end; the
  rich authoring/interaction engine (LE-05) is the same MVP feature's larger second build.

  Background:
    Given a student has completed her diagnostic
    And her map shows a module with status "needs_work"
    And she has chosen the lesson after not testing out of the competency check

  @scenario:LE-01
  Scenario: A module's lesson teaches the whole idea on-platform, authored in advance
    When she opens the module's lesson
    Then she sees a self-contained, textbook-style lesson authored for that module
    And it teaches the idea in ordered blocks (explanation, worked examples, key points, self-checks)
    And she never has to leave the platform to understand it
    And the page is served from stored lesson content, not generated in real time

  @scenario:LE-02
  Scenario: The lesson is never scored
    Given she is working through the module's lesson
    When she completes it
    Then her module progress is unchanged
    And her mastery status is unchanged

  @scenario:LE-03
  Scenario: Completing the lesson unlocks worked examples, which unlock practice
    Given a module that has both a lesson and worked examples
    When she does not test out and enters the module
    Then the worked examples stay locked until she completes the lesson once
    And practice stays locked until she completes the worked examples once
    And once unlocked, a stage stays open for that module
    And a module missing either the lesson or the worked examples is never gated

  @scenario:LE-04
  Scenario: A clarify chat sits beside the lesson and pushes her understanding
    Given she is working through the module's lesson
    When she asks the clarify chat a question about the lesson
    Then the LLM tutor answers Socratically — a hint or guiding question first, then confirmation
    And it stays scoped to this lesson, gently redirecting anything unrelated
    And it never gives away practice answers, never authors the lesson content
    And it is tailored by her learning profile and leaves her progress and mastery unchanged

  @scenario:LE-05
  Scenario: Lessons are authored with rich, H5P-grade interactions
    Given an author is building a module's lesson
    When they compose the lesson from the supported interaction types
    Then the lesson can mix explanation, media and interactive steps like H5P content
    And the finished lesson is stored for that module and served to every student who opens it

  @scenario:LE-06
  Scenario: Tapping a locked stage kindly points her to finish the earlier part
    Given practice is locked because she has not finished the lesson and worked examples
    When she taps practice, or opens its link directly
    Then she is not taken into practice
    And a friendly, child-language message asks her to finish the lesson and worked examples first

  @scenario:LE-07
  Scenario: A fill-in-the-blank interaction gates on the right word
    Given a lesson block asks her to fill in a blank
    When she gives the wrong word
    Then the lesson does not advance past the block
    And when she gives the right word (any case, trimmed) the block is satisfied and she can go on

  @scenario:LE-08
  Scenario: A mark-the-words interaction gates on tapping the target words
    Given a lesson block asks her to tap the target words in a sentence
    When she taps the wrong set of words
    Then the block is not satisfied
    And when she taps exactly the target words the block is satisfied and she can go on

  @scenario:LE-09
  Scenario: A match-pairs interaction gates on matching every pair
    Given a lesson block asks her to match pairs
    When any pair is mismatched
    Then the block is not satisfied
    And when every left is matched to its right the block is satisfied and she can go on

  @scenario:LE-10
  Scenario: An order-the-steps interaction gates on the correct sequence
    Given a lesson block asks her to put steps in order
    When her order is wrong
    Then the block is not satisfied
    And when her order matches the intended sequence the block is satisfied and she can go on

  # ------------------------------------------------------------ admin verification

  @scenario:LE-11 @admin
  Scenario: An admin can preview and edit each lesson to verify it on an ongoing basis
    Given an admin is on the Lessons admin page
    When the admin opens a lesson's "Preview" in student mode
    Then the lesson opens in the real student renderer with all its interactions
    And nothing about the walk is recorded — no stage completion, no guided-time lock
    And the admin can also open the lesson in "Re-teach" preview mode
    And the admin can edit the lesson's blocks from the same page
