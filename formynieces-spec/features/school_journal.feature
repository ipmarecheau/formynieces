@mvp @student @guardian @system
Feature: School journal — assessments from the classroom
  The platform works with the school, not against it. Graded papers and report cards
  carry evidence the app cannot see on its own: how she performs under a teacher's eye.
  In the school journal a student (or her guardian) uploads a photo of a school
  assessment; a vision/OCR pipeline digitises it into structured data — subject, strand,
  score, and comments — so it can be tracked over time and weighed by the engine. Strong
  school performance on a topic her voyage covers CORROBORATES her understanding as an
  extra, honest signal; a weak result gently steers her focus. It is never a punishment,
  never a gate, and never overrides the platform's own mastery — it only adds confidence.
  School numbers stay in the honest (guardian/system) layer and are never turned into a
  judgement metric inside the child's motivational world.

  Background:
    Given a student linked to a guardian

  @scenario:SJ-01
  Scenario: A student or guardian uploads a photo of a school assessment
    When a student or her guardian uploads a photo or PDF of a graded assessment
    Then it is stored in that student's school journal with the date it was filed
    And the original file stays attached and can be viewed again
    And an upload from either the student or the guardian lands in the same journal

  @scenario:SJ-07
  Scenario: A vision/OCR pipeline digitises the uploaded assessment
    Given an assessment image has been uploaded
    When the digitisation pipeline processes it
    Then it extracts structured fields — subject, strand, assessment type, score, and comments
    And the digitised text is stored alongside the original image
    And low-confidence fields are flagged for a quick human confirmation, not trusted blindly

  @scenario:SJ-02
  Scenario: An entry captures the classroom evidence in structure
    Given an uploaded assessment has been digitised
    When the entry is saved
    Then it holds the assessment date, the term, the strand it tested
    And the assessment type, the score as written, and any teacher comment
    And a guardian can correct any field the pipeline read wrongly

  @scenario:SJ-03
  Scenario: The journal reads as a term timeline
    When the school journal is opened
    Then entries are listed newest first, grouped by term
    And each entry shows strand, assessment type, score and comment at a glance

  @scenario:SJ-08
  Scenario: Strong school performance corroborates topic understanding
    Given a digitised assessment shows strong performance on a strand her voyage covers
    When her learning signals are updated
    Then that result is recorded as a corroborating confidence signal for the strand
    And it may support an inferred understanding, in the honest layer
    And it never on its own marks a module mastered, and never overrides the platform's own mastery

  @scenario:SJ-05
  Scenario: A strand flagged weak at school steers her daily plan
    Given a school journal entry shows a weak strand for a skill her voyage covers
    When her next daily plan is composed
    Then the platform weighs that classroom evidence when choosing her focus
    And a strand the school flagged gets gentle priority without alarming the child

  @scenario:SJ-09
  Scenario: School performance is tracked over time
    Given several digitised assessments across terms
    When her school progress is read
    Then her performance per strand is tracked as a trend across terms
    And the trend is available in the guardian/honest layer alongside the platform's own picture

  @scenario:SJ-04
  Scenario: School evidence appears in the weekly summary
    Given a school journal entry was filed this week
    When the weekly parent summary is composed
    Then it includes what the school evidence said, alongside the platform's own picture
    And the two sources are labelled so the parent knows which is which

  @scenario:SJ-06
  Scenario: A school mark is never a judgement metric in the child's world
    Given a school journal entry exists for a student
    When she uses her voyage, streaks and celebrations
    Then no school score is used to gate, penalise, or shame her there
    And her motivational world carries on untouched by classroom marks
    And a weak result never breaks a streak or blocks the map
