@admin
Feature: Admin content management
  Every learning resource is human-vetted. Filament gives the admin the tools
  to author modules, vetted resources, and diagnostic anchor questions.

  @mvp
  Scenario: Editing a module updates the student-facing lesson
    Given the admin is editing a syllabus module in Filament
    When she saves a changed description and vetted resources
    Then students opening that module see the updated lesson content

  @v1.1
  Scenario: Authoring an anchor question makes it available to the diagnostic
    Given the admin is on the anchor question form in Filament
    When she saves a question with options, correct index, difficulty, strand, and distractor notes, marked active
    Then the question becomes available to new diagnostic sessions

  @v1.1
  Scenario: The diagnostics monitor surfaces session health
    Given completed and in-progress diagnostic sessions exist
    When the admin opens the diagnostics monitor
    Then she sees recent sessions, completion rates, and items flagged for unusual response patterns
