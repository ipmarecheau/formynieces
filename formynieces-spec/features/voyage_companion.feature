@v1.1 @student
Feature: The Voyage companion — a warm voice over honest data
  A friendly companion greets the student on her Voyage and narrates her sea in
  her own language: welcome back, her streak, the plan for this week. It never
  invents progress she has not earned, and it never borrows the guardian's honest
  gauge — no percentages, no pace, no deficits ever reach the child through it. It
  speaks only from what is already true on her map. An optional AI voice may make
  the words richer, but it is bound to the same facts, degrades to a plain
  template, and never keeps her waiting for the sea to appear.

  Rule: The companion narrates only what is real

    @scenario:VC-01
    Scenario: The companion greets a returning student by name with her live streak
      Given a student with an active streak
      When she opens her Voyage
      Then a companion greeting welcomes her by name
      And it names her current streak warmly, never as a judgement metric

    @scenario:VC-03
    Scenario: The companion invents nothing when there is no streak or target
      Given a student with no active streak and no weekly target set
      When she opens her Voyage
      Then the companion still gives a warm, truthful greeting
      And it claims no streak, plan, or progress that does not exist

  Rule: The companion speaks the child's language, never the guardian's gauge

    @scenario:VC-02
    Scenario: The companion names this week's plan by topic, never by pace
      Given a weekly target naming specific modules for this week
      When she opens her Voyage
      Then the companion names this week's focus by topic
      And it shows no target count, pace position, or percentage

  Rule: The optional AI voice is bound to the same facts and never blocks the sea

    @roadmap @scenario:VC-04
    Scenario: The AI voice never blocks the Voyage and falls back to the template
      Given the companion is set to use the AI voice
      When the AI service is slow or unavailable
      Then the Voyage still loads immediately
      And the student sees the deterministic template greeting instead

    @roadmap @scenario:VC-05
    Scenario: The AI voice speaks only from the facts it is handed
      Given the companion is composing an AI-enhanced note
      When it generates the message
      Then it is constrained to the supplied facts, being her name, streak,
        this week's topics, and mastery count
      And it never surfaces pace, percentages, or any guardian-layer metric
