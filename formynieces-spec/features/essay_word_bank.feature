@roadmap @admin @system
Feature: Essay & word banks — curated, syllabus-aligned content
  For now the daily reading essays and vocabulary lean on the LLM to carry the
  load — fast to start, but it drifts from the syllabus and re-covers the same
  ground each session. The essay bank and word bank replace that with a curated,
  pre-authored corpus: reading essays/passages and vocabulary words written or
  imported once, tagged to the SEA syllabus strands and reading levels, and reused
  across students and days — so content is consistent, aligned to the curriculum,
  and never regenerated per session. The LLM still assists (comprehension scoring,
  example sentences, gentle tailoring) but no longer invents the core content.
  This is the durable successor to the current reading pool (reading_passages /
  vocabulary_words) and complements the existing question and writing banks.

  @scenario:EW-01
  Scenario: An admin curates the essay/passage bank, tagged to level and strand
    Given an admin building the essay bank
    When they author or import a reading essay
    Then it is stored with its reading level and the SEA syllabus strand(s) it serves
    And it becomes available to be served on future mornings without regeneration

  @scenario:EW-02
  Scenario: The word bank aligns vocabulary to the syllabus
    Given an admin building the word bank
    When they add a vocabulary word
    Then the word is tagged to a syllabus strand and a reading level
    And the word is drawn from, or linked to, essays that use it in context
    And vocabulary is chosen for curriculum alignment, never at random

  @scenario:EW-03
  Scenario: Daily reading serves from the curated bank first, LLM only fills gaps
    Given a student due a morning passage at her level
    When the daily reading is served
    Then it comes from the curated essay bank when a suitable unseen essay exists
    And the LLM is used only to fill a gap when the bank has none at her level
    And a gap-filled passage is flagged for an admin to curate into the bank later

  @scenario:EW-04
  Scenario: Content is reused, not regenerated each session
    Given essays and words already in the banks
    When many students take their daily reading
    Then they are served the same curated content appropriate to their level
    And the same passage is never re-generated or re-served to a student who has seen it
    And spaced-repetition and mastery run over the curated word bank

  @scenario:EW-05
  Scenario: The banks support bulk import and export
    Given an admin maintaining the banks
    When they import or export the essay and word banks
    Then essays and words move in a documented bulk format, like the lesson and question banks
    And an import upserts by a stable key so re-imports do not duplicate content
