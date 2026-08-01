@mvp @student
Feature: Student home — the Voyage is the front door
  The student lives on one surface: her Voyage. It is her home, not a side room.
  Everything a child touches day to day — this week's focus, her streak, her
  writing, the way into practice — is reached from the Voyage, so she meets one
  coherent world and never the percentages-and-pace roadmap that belongs to the
  guardian's honest layer. These scenarios close the gap between the built Voyage
  and the surfaces that still compete with it.

  Rule: The Voyage is the only student home; the percentage roadmap is never shown to a child

    @scenario:SH-01
    Scenario: An onboarded student lands on her Voyage
      Given an onboarded student with a generated roadmap
      When she logs in
      Then she arrives on her voyage
      And she is never shown the percentage-and-tally roadmap
      And the honest completion data remains available only in the guardian layer

    @scenario:SH-06
    Scenario: The welcome-back celebration flows into the Voyage
      Given a returning student with an active streak
      When she logs in and continues past her welcome-back celebration
      Then she is taken to her voyage
      And she is not taken to the percentage roadmap

  Rule: The daily threads are reached from the Voyage, in child-kind language

    @scenario:SH-02
    Scenario: This week's focus is highlighted on the Voyage
      Given a weekly target naming specific modules for this week
      When she opens her voyage
      Then the levels for those modules are highlighted as this week's focus
      And no target count, pace position, or percentage is shown
      # AM-05 (@roadmap) is the richer treatment: a suggested-this-week star that
      # explicitly never blocks the rest. SH-02 is the MVP surfacing.

    @scenario:SH-03
    Scenario: Practice is entered by tapping a level on an island
      Given an open island with a playable level
      When she starts practising that module
      Then she reaches practice by tapping the level on the island
      And no separate percentage roadmap offers a competing way into practice

    @scenario:SH-04
    Scenario: Her streak lives on the Voyage
      Given a student with an active login or practice streak
      When she opens her voyage
      Then her streak is shown within the voyage
      And she is not sent to a separate dashboard to see it

    @scenario:SH-05
    Scenario: The Writer's Log stop opens this week's writing prompt
      Given a student on an island whose Writer's Log stop is shown
      And a writing prompt exists for the current week
      When she opens the Writer's Log stop
      Then she is taken to this week's writing prompt
      And the stop is no longer a "coming soon" placeholder
      # This relocates WR-01's entry point from the dashboard writing card to the
      # Writer's Log stop on the Voyage.
