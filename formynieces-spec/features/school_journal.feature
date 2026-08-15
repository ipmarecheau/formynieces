@mvp @guardian
Feature: School journal — graded papers from the classroom
  The platform works with the school, not against it. Report cards and graded
  papers carry evidence the app cannot see on its own: how she performs under
  a teacher's eye, in numbered scores, with handwritten comments. The school
  journal lets a guardian keep that evidence beside the platform's own picture
  of her, and lets the engine weigh it when planning her days. As everywhere
  else, two-layer separation holds: school grades live in the guardian layer
  and never appear in the child's.

  Background:
    Given a guardian with a linked student

  @scenario:SJ-01
  Scenario: A guardian files a graded paper in the journal
    When she uploads a photo or PDF of a graded school paper
    Then it is stored in that student's school journal with the date it was filed
    And she can view and download it again from the Parent Portal

  @scenario:SJ-02
  Scenario: An entry captures the classroom evidence in structure
    When she records the details of a graded paper
    Then the entry holds the assessment date, the term, the strand it tested
    And the assessment type, the score as written, and the teacher's comment
    And the original file stays attached for reference

  @scenario:SJ-03
  Scenario: The journal reads as a term timeline
    When she opens the school journal
    Then entries are listed newest first, grouped by term
    And each entry shows strand, assessment type, score and comment at a glance

  @scenario:SJ-04
  Scenario: School evidence appears in the weekly summary
    Given a school journal entry was filed this week
    When the weekly parent summary is composed
    Then it includes what the school evidence said, alongside the platform's own picture
    And the two sources are labelled so the parent knows which is which

  @scenario:SJ-05
  Scenario: A strand flagged weak at school steers her daily plan
    Given a school journal entry shows a weak strand for a skill her voyage covers
    When her next daily plan is composed
    Then the platform weighs that classroom evidence when choosing her focus
    And a strand the school flagged gets gentle priority without alarming the child

  @scenario:SJ-06
  Scenario: The child never sees school grades in her layer
    Given a school journal entry exists for a student
    When she uses any student screen
    Then no school score, grade, or teacher comment from the journal is shown to her
    And her voyage, streaks and celebrations carry on untouched by classroom marks
