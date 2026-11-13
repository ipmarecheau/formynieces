@mvp
Feature: Per-student activity log — one child's journey at a glance
  To catch a struggling child early, the team needs to see what a specific child has actually
  been doing — not an aggregate, but their own recent journey. The admin panel offers a
  read-only timeline per student: practice attempts and whether they landed, modules mastered,
  essays submitted and marked, days completed, and any help they reached out for — newest
  first. It is composed from records the app already keeps, so it adds no new tracking and is
  available the moment a child is picked.

  @scenario:AL-01
  Scenario: The team picks a child and sees their recent activity, newest first
    Given a child has been active in the app
    When an admin opens the student log and chooses that child
    Then the child's recent activity is shown newest first

  @scenario:AL-02
  Scenario: The log spans the whole journey
    Given a child has practised, mastered a module, written an essay, finished a day, and asked for help
    When the admin views that child's log
    Then each of those moments appears in the timeline with a clear summary

  @scenario:AL-03
  Scenario: The log is admin-only and adds no new tracking
    Given the activity log is composed from existing records
    Then only the team can see it
    And nothing new is written to produce it
