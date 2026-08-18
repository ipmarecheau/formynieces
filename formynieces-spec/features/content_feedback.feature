@roadmap @student @guardian @system
Feature: Content feedback — a light reaction from the people who use it
  A tiny, skippable reaction on the surfaces a child actually meets — three face
  emojis, from "did not enjoy it" to "loved it" — tied to the specific piece of
  content in front of her. Guardians have their own, separate channel about the
  weekly experience. It exists to tell the team where the app needs improvement,
  and it feeds the Content Audit's quality axis. It never judges the child, never
  touches her streaks or pace, never blocks her, and never becomes a score she sees.

  @scenario:CF-01
  Scenario: A student reacts to a piece of content with a face
    Given a student has just finished a lesson, a reading passage, or a practice set
    When she is offered a quick reaction
    Then she can pick one of three faces — 🙁 did not enjoy it, 🙂 liked it, 😍 loved it
    And her reaction is recorded against that exact piece of content

  @scenario:CF-02
  Scenario: The reaction is optional, occasional, and never in the way
    Given a student moving through her day
    When a reaction prompt might appear
    Then it appears only at a natural end-point, never mid-task
    And it is easy to skip, never blocks her, and is not asked on every item

  @scenario:CF-03
  Scenario: A reaction is captured with its context
    Given a student reacts to a piece of content
    When the reaction is stored
    Then it records the content item, the surface it came from, and whether she had
      just answered correctly or not
    And that context lets a frustrated tap be told apart from an honest signal about the content

  @scenario:CF-04
  Scenario: A reaction never enters the child's motivational world
    Given a student has reacted to content
    When she uses her Voyage, streaks and celebrations
    Then her reaction never changes a streak, XP, or her pace
    And it is never shown back to her as a judgement of her or as a running score

  @scenario:CF-05
  Scenario: A guardian gives experience feedback on a separate channel
    Given a guardian on her dashboard
    When she is offered a brief feedback prompt
    Then she can say whether the week's summary was useful, in her own honest layer
    And this is a distinct channel from the child's content reactions, shaped for a parent

  @scenario:CF-06
  Scenario: Reactions are stored for the honest and admin layer only
    Given students and guardians have left reactions
    When the feedback is read
    Then it is available to the team, aggregated per content item
    And no child ever sees another child's reactions or her own as a running score
