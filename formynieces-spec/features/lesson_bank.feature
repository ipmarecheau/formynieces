@mvp @admin @system
Feature: Lesson bank import and export
  Lessons are authored in advance (LE-05); at scale they are managed in bulk, not one form at a
  time. A lesson bundle is a JSON list of lessons, each bound to a module by its stable code
  (MATH-001, ELA-001, …) and carrying its ordered interaction blocks. Import upserts by module
  (one lesson per module), validates every block against the block schema, and never half-saves a
  bad lesson. Export dumps the same shape back, so authoring round-trips and every export is a
  restore point. A self-service guide and a downloadable template keep the format understandable
  long after it was built.

  @scenario:LB-01
  Scenario: Import a JSON lesson bundle, upserting by module code
    Given an admin has a JSON bundle of lessons keyed by module code
    When they import it
    Then each valid lesson is stored for its module, its blocks intact
      And re-importing the same bundle updates rather than duplicates
      And a lesson whose module code is unknown, or whose blocks are invalid, is skipped and reported
      And a preview run validates and reports the same outcome without saving anything

  @scenario:LB-02
  Scenario: Export lessons back to the bundle format
    Given the bank holds authored lessons
    When an admin exports them
    Then they receive a JSON bundle in the same shape the importer reads
      And re-importing that export reproduces the same lessons

  @scenario:LB-03
  Scenario: Seed version-controlled lessons from the repository
    Given lesson bundles live in database/data/lessons as JSON files
    When the lesson seeder runs
    Then each bundle is imported by module code
      And running the seeder again is idempotent

  @scenario:LB-04
  Scenario: A guide and a downloadable template explain the format
    Given an admin opens the lesson import guide
    Then it lists every supported block type and its required fields
      And it offers a downloadable template bundle that uses every block type
      And that template is itself a valid, importable bundle
