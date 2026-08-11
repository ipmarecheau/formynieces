@student @admin @system
Feature: Writing prompt bank
  A bank of past-paper-style writing prompts — each with its own marking rubric —
  that feeds the parallel writing track. Imported from Moodle essay exports, keyed
  by genre and difficulty, and scored against each prompt's own rubric. Writing
  never enters the module mastery model and never reduces to a grade or pass/fail.

  @scenario:WB-01
  Scenario: Import a Moodle essay export into the writing-prompt bank
    Given an admin has a Moodle XML export of essay-type writing prompts
    When they import the file
    Then each essay prompt is stored in the writing-prompt bank
      And its genre, sub-genre and difficulty are read from the question name
      And its marking rubric is parsed and stored with the prompt
      And re-importing the same file updates rather than duplicates
      And non-essay questions in the file are reported and skipped

  @scenario:WB-02
  Scenario: The admin importer routes essay files to the writing bank
    Given an admin uploads a Moodle XML file containing essay questions
    When the import runs
    Then essay questions are stored as writing-bank prompts
      And any multichoice questions in the same file still go to the practice bank

  @scenario:WB-03
  Scenario: A student is served a bank writing prompt by genre and difficulty
    Given the writing bank holds active prompts across genres and difficulties
    When a student begins a writing task for a given genre and difficulty
    Then she receives an active bank prompt matching that genre and difficulty

  @scenario:WB-04
  Scenario: A submission is scored against the prompt's stored rubric
    Given a student has a bank writing prompt with a stored rubric
    When she submits her writing
    Then the response is scored against the prompt's own rubric criteria
      And the four-criterion profile and warm feedback are returned
      And no letter grade, pass/fail, or module mastery status changes
