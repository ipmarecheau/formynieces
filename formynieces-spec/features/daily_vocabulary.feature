@mvp @student @system
Feature: Daily vocabulary
  A daily vocabulary list drawn from that morning's reading passage, so reading, comprehension
  and vocabulary all reinforce one another. Each word is met in the context it appeared in and
  then used in the student's own sentence — another quiet feed into the writing skill. Words she
  has seen before resurface on a spaced-repetition schedule so they are retained, not just met
  once. Like reading and writing, vocabulary is FORMATIVE: warm feedback and streak credit, never
  a grade, never pass/fail, never a change to module mastery. It completes the roughly
  fifteen-minute morning routine that begins with daily reading.

  Background:
    Given a student has completed her morning reading passage
    And words in that passage have been marked as vocabulary

  @scenario:DV-01
  Scenario: The day's words come from the day's passage
    When she begins her daily vocabulary
    Then today's new words are drawn from this morning's passage
    And each word is shown in the sentence it appeared in
    And the words are level-appropriate because the passage matched her reading level

  @scenario:DV-02
  Scenario: Each word is met in context and then used in her own sentence
    Given she is working through today's vocabulary words
    When she practises a word
    Then she first confirms its meaning in the context of the passage
    And she is then asked to use the word in a sentence of her own
    And her sentence is treated as writing practice, not a scored answer

  @scenario:DV-03
  Scenario: Earlier words resurface on a spaced schedule until retained
    Given she has learned vocabulary words on previous days
    When she begins her daily vocabulary
    Then words that are due for review resurface alongside today's new words
    And a word she keeps getting right returns less often
    And a word she keeps missing returns sooner

  @scenario:DV-04
  Scenario: Vocabulary is formative and feeds writing, not mastery
    Given she has finished today's vocabulary
    When her results are returned
    Then she sees warm feedback that celebrates the words she has taken on
    And no letter grade or pass/fail status is shown
    And no module's mastery status changes

  @scenario:DV-05
  Scenario: Vocabulary completes the fifteen-minute morning ritual on a device
    Given she has just finished her morning reading and comprehension
    When she moves straight into daily vocabulary
    Then the vocabulary set is short enough to finish the whole morning routine in about fifteen minutes
    And completing it advances her daily streak alongside reading
    And the full ritual can be done on a phone during her commute
