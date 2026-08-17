@mvp @student @system
Feature: Captain's Orders — the daily brief and the day's minimum
  A child opens her Voyage to one question: what must I do today? The Captain's
  Orders answers it. It is a collapsible sidebar on the Voyage — and only there,
  never layered over a lesson or a reading passage — where Smooth, as captain,
  hands down the day's brief: the small set of duties that count as today's
  minimum, shown as a checklist. The minimum is three threads — the Morning Tide
  (a single linked reading-and-vocabulary ritual), forward progress on the map,
  and, on Monday, Wednesday and Friday, a piece of writing. The brief is delivered twice: a
  morning brief that sets the orders and an evening brief that shows what is left
  and offers a short review. Orders are always framed kindly and never expose the
  guardian layer's pace, percentages, or deficits. The sidebar carries two tabs:
  the Captain's Brief (today) and the Ship's Log (her story so far).

  Background:
    Given an onboarded student on her Voyage

  @scenario:CO-01
  Scenario: The Captain's Orders is a collapsible sidebar on the Voyage
    When she opens her Voyage
    Then a Captain's Orders panel is available on the left of the Voyage
    And she can collapse and expand it
    And it is present on the Voyage home but is not layered over a lesson or a reading passage

  @scenario:CO-02
  Scenario: The morning brief lists the day's minimum as a checklist
    Given a weekday she has not yet started
    When she opens the Captain's Brief
    Then Smooth greets her with the day's orders
    And the orders list her daily minimum as a checklist: the Morning Tide (reading and vocabulary) and progress on the map
    And each item shows whether it is still to do or already done

  @scenario:CO-03
  Scenario: Writing joins the orders only on Monday, Wednesday and Friday
    Given today is a Monday, Wednesday or Friday
    When she opens the Captain's Brief
    Then a writing duty appears in the day's orders
    And on any other weekday no writing duty appears

  @scenario:CO-04
  Scenario: The minimum can be completed in any order
    Given the day's orders list several duties
    When she completes them in whatever order she chooses
    Then each duty checks off as she finishes it
    And no duty is blocked behind another, except the writing gate on the map

  @scenario:CO-05
  Scenario: On a writing day, sailing to a new level waits until writing is done
    Given today is a writing day and she has not yet done her writing
    When she tries to open a new, not-yet-started level on the map
    Then Smooth invites her to complete today's writing first
    And the map stays visible and explorable, and already-started levels remain playable
    And once her writing is done the new level opens, with no lockout language
    # Writing is no longer a stop on the map (see writing_track, adventure_map);
    # it is a same-day precondition for advancing, framed as a nudge, not a wall.

  @scenario:CO-06
  Scenario: The evening brief shows what is left and offers a review
    Given she returns to her Voyage in the evening
    When she opens the Captain's Brief
    Then it shows which orders are done and which remain
    And it offers a short review of the day's work
    And the review is optional and the evening carries no duty beyond the day's one minimum

  @scenario:CO-07
  Scenario: Completing the day's minimum closes the day and feeds the Voyage streak
    Given she has some orders still open
    When she completes the last item in the day's minimum
    Then the day is marked complete in the Captain's Brief
    And her Voyage streak is advanced for the day
    # The streak mechanics live in streak_economy (SE-*).

  @scenario:CO-08
  Scenario: A weekend is shore leave when she is on pace
    Given it is Saturday or Sunday and she is on pace
    When she opens the Captain's Brief
    Then the brief stands her down for the weekend with no required orders
    And her streak is protected across the weekend
    # "On pace" = meeting her weekly target (weekly_targets, ML-06, SE-04).

  @scenario:CO-09
  Scenario: A weekend shows kind catch-up orders only when she has fallen behind
    Given it is a weekend and she is behind her weekly pace
    When she opens the Captain's Brief
    Then the brief offers a gentle set of catch-up orders drawn from this week's unfinished work
    And it uses encouraging, tide-and-voyage language, never deficit, warning, or failure language
    And the catch-up is bounded to this week's remaining target, never an unbounded backlog

  @scenario:CO-10
  Scenario: The brief never shows the child the guardian layer's numbers
    When she reads any Captain's Brief
    Then she is never shown a percentage, a pace deficit, or a weeks-behind figure
    And the orders speak only in duties and encouragement
    # Two-layer separation (adventure_map AM-06, weekly_targets WT-03). A kind
    # weekly goal count is allowed and lives in CO-11; pace/deficit numbers do not.

  @scenario:CO-11
  Scenario: The brief names this week's goal and her progress toward it
    Given a student with a weekly target of several modules, some already mastered
    When she opens the Captain's Brief
    Then the brief names this week's goal as a friendly count of islands to conquer
    And it shows how many she has conquered so far this week
    And the progress is framed as an encouraging quest, never a percentage or a deficit

  @scenario:CO-12
  Scenario: On a phone the orders do not bury the sea
    Given a student opens her Voyage on a small screen
    When the Captain's Orders sidebar is shown
    Then the map stays reachable without dismissing the whole brief
    And the sidebar opens keeping some of the sea in view, or collapses to a peek she can expand
    And her companion's help affordance never covers the island list
    # Observed on mobile: the expanded orders panel filled the screen and pushed the map
    # below the fold, and the help bubble overlapped the island legend.
