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

  @scenario:SJ-10
  Scenario: A guardian opens her student's journal from the dashboard
    Given a guardian whose student is linked to her
    When she follows the "open the journal" link from her guardian dashboard
    Then her student's school journal opens successfully in the guardian layer
    And it renders its term timeline without error, even when no entries exist yet
    # Guards the guardian journal view (guardian.journal) against a missing-layout crash
    # that currently returns a 500 when the link is followed.

  @scenario:SJ-06
  Scenario: A school mark is never a judgement metric in the child's world
    Given a school journal entry exists for a student
    When she uses her voyage, streaks and celebrations
    Then no school score is used to gate, penalise, or shame her there
    And her motivational world carries on untouched by classroom marks
    And a weak result never breaks a streak or blocks the map

  @scenario:SJ-11
  Scenario: Each question on the paper is stored with its syllabus topic
    Given a digitised assessment containing several questions
    When the per-question breakdown is stored
    Then each question holds its prompt, the student's answer, the correct answer as marked, and whether it was marked correct
    And each question is aligned to a syllabus topic, so classroom evidence speaks the same language as the voyage
    And an alignment the pipeline could not make confidently is flagged, never guessed

  @scenario:SJ-12
  Scenario: A screenshot of each question and its solution is saved
    Given a digitised assessment whose questions carry their region on the page
    When the breakdown is stored
    Then each question keeps a clipped image of itself — the question and its worked solution as the teacher marked it
    And a clip the pipeline could not cut cleanly falls back to the full page, never a broken image
    And the clips are viewable from the journal for years to come

  @scenario:SJ-13
  Scenario: The AI reads the student's answer and their reasoning — for the honest layer only
    Given a question where the student answered incorrectly
    When the breakdown is stored
    Then the pipeline records what the student's answer suggests they were thinking — the misconception, not just the wrong string
    And that reasoning note is visible to the guardian, beside the clip
    And it is never shown in the child's world, where no question-level analysis ever appears
    And a per-topic weak signal from wrong answers steers the daily plan's gentle focus more precisely than a strand alone
