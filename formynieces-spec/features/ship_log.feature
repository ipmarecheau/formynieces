@mvp @student
Feature: Ship's Log — her story so far
  The Ship's Log is the second tab of the Captain's Orders sidebar and the
  child's own record of her voyage: how many days she has sailed, the streaks she
  is keeping, the milestones she has passed, and the rewards in her Captain's
  Locker. It exists for one reason — to motivate — so it speaks only in
  encouragement and never carries a grade, a pace number, or anything from the
  guardian's honest layer or the school journal. It is where a streak becomes a
  story she is proud of. It is distinct from the guardian's school journal, which
  archives graded classroom papers in the guardian layer.

  Background:
    Given an onboarded student on her Voyage

  @scenario:SL-01
  Scenario: The Ship's Log is the sidebar's second tab
    When she opens the Captain's Orders sidebar
    Then she can switch between the Captain's Brief and the Ship's Log
    And the Ship's Log is her own record, shown only in her layer

  @scenario:SL-02
  Scenario: The log shows her Voyage streak and the streaks beneath it
    Given she has an active Voyage streak and one or more sub-streaks
    When she opens her Ship's Log
    Then her master Voyage streak is shown as the headline
    And her sub-streaks — reading, vocabulary, writing and map — are shown beneath it
    # Streak definitions live in streak_economy (SE-*); this is the view.

  @scenario:SL-03
  Scenario: The log shows how she has been doing over time
    Given she has sailed on several days
    When she opens her Ship's Log
    Then she sees a record of her recent days and the milestones she has reached
    And the record is framed as progress and encouragement, never as a report card

  @scenario:SL-04
  Scenario: Passing a milestone is celebrated, and a quiet spell is never shamed
    Given she has just reached a streak or progress milestone
    When she opens her Ship's Log
    Then the milestone is celebrated
    And after a gap, the log welcomes her back without referencing what was missed

  @scenario:SL-05
  Scenario: The Captain's Locker shows the rewards she holds
    Given she has earned one or more rewards
    When she opens the Captain's Locker in her Ship's Log
    Then she sees each reward she holds and how many — Shore Leave, Anchor, Tailwind and Lifebuoy
    And each reward explains, in child-kind words, what it does

  @scenario:SL-06
  Scenario: She can use a reward from her Locker
    Given she holds at least one usable reward
    When she uses it from the Captain's Locker
    Then the reward is applied and its count decreases
    And the Ship's Log reflects the change
    # What each reward does, and when it may be used, is defined in streak_economy (SE-*).

  @scenario:SL-07
  Scenario: The log never shows guardian-layer or school information
    When she reads her Ship's Log
    Then no percentage, pace number, module tally, or school grade is shown
    And nothing from the guardian's honest layer or the school journal appears
