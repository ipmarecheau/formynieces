@v1.1 @admin
Feature: Question bank — import, authoring, and export
  The admin grows and curates the practice question bank behind the curtain. Whole
  banks arrive as Moodle XML and are imported safely; individual questions are
  authored and edited by hand; and the bank can be exported back to Moodle XML so
  it stays portable. Every question is a single-answer, four-option multiple choice
  tied to one syllabus module and one of the three practice rungs — never anything
  the learning loop cannot use.

  Rule: A Moodle XML export is imported safely and idempotently

    @scenario:QB-01
    Scenario: A dry run previews an import without changing anything
      Given a Moodle XML export of practice questions
      When the admin previews it as a dry run
      Then it reports how many questions would be created, updated, and skipped
      And no question or image is written

    @scenario:QB-02
    Scenario: Committing an import adds questions mapped to module and rung
      Given a Moodle XML export whose skills are mapped to syllabus modules
      When the admin runs the import for real
      Then each question is stored against its mapped syllabus module
      And its difficulty rung reflects the source level, D1–D2 easy, D3 medium, D4–D5 hard
      And its four options and single correct answer are preserved

    @scenario:QB-03
    Scenario: Re-importing the same file updates in place rather than duplicating
      Given a Moodle XML export that has already been imported
      When the admin imports the same file again
      Then the matching questions are updated, not duplicated

    @scenario:QB-04
    Scenario: Embedded figures are extracted and their references rewritten
      Given a question whose prompt embeds a base64 figure
      When it is imported
      Then the figure is stored and served from a real URL
      And the prompt no longer contains a Moodle plugin-file placeholder

    @scenario:QB-05
    Scenario: Questions that cannot be placed are skipped with a reason
      Given a Moodle export containing an unmapped skill and a non-multiple-choice question
      When the admin previews the import
      Then those questions are skipped and listed with the reason each was skipped
      And the questions that can be placed are still reported as importable

  Rule: An admin authors and edits questions by hand

    @scenario:QB-06
    Scenario: An admin creates a practice question
      Given the admin is on the new-question screen
      When she submits a module, a difficulty rung, a prompt, four options, the correct
        option, and an explanation
      Then the question is saved to the practice bank against that module and rung

    @scenario:QB-07
    Scenario: An admin edits an existing question
      Given an existing practice question
      When the admin changes its prompt and saves
      Then the stored question reflects the change

    @scenario:QB-08
    Scenario: A question must have exactly four options and one correct answer
      Given the admin is authoring a question
      When she submits without four options or without exactly one correct option
      Then the question is rejected with a validation error
      And nothing is saved

  Rule: The bank exports back to the same Moodle XML format

    @scenario:QB-09
    Scenario: The admin exports the practice bank to Moodle XML
      Given practice questions exist in the bank
      When the admin exports the bank
      Then a Moodle XML file is produced grouping questions under their module
      And each question carries its four options with a single fully-correct answer

    @scenario:QB-10
    Scenario: An exported question can be re-imported
      Given a practice question exported to Moodle XML
      When that XML is imported again
      Then the question is present in the bank with its prompt, options, and correct answer

  Rule: The bank is backed up daily and can be restored to any date in the last month

    @scenario:QB-11
    Scenario: Emptying the bank takes a safety backup first
      Given practice questions exist in the bank
      When the admin deletes all questions
      Then a backup of the bank is taken before it is emptied
      And the bank is then empty

    @scenario:QB-12
    Scenario: A daily backup snapshots the whole bank
      Given practice questions exist in the bank
      When the daily backup runs
      Then a dated backup capturing every question is stored

    @scenario:QB-13
    Scenario: Backups older than a month are pruned
      Given a backup older than 30 days and a backup from this week
      When the daily backup runs
      Then the backup older than 30 days is removed
      And the recent backup is kept

    @scenario:QB-14
    Scenario: The admin restores the bank to a chosen backup
      Given a backup taken earlier and a bank that has changed since
      When the admin restores that backup
      Then the bank matches exactly the questions captured in that backup
