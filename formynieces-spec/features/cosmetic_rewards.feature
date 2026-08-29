@mvp @student
Feature: Smooth's Chest — the cosmetic marketplace
  A purely cosmetic reward economy sits alongside — and never on top of — the
  protective streak economy and the honest pace layer. The child earns a
  spendable treasure currency, Doubloons, by LEARNING (mastering modules, holding
  pace, reaching streak milestones), never by grinding time, and spends it in
  Smooth's Chest on outfits and poses for Smooth, ship skins, and island
  decorations. A Captain's rank climbs with her master streak and is never lost.
  The economy exists to make a long, honest habit feel like visible status and
  personal ownership — it changes NOTHING about pace, mastery, the daily minimum,
  or any functional perk, it is never purchasable with real money, and it lives
  only in the child's world, never in the guardian's honest layer.

  Background:
    Given an onboarded student on her Voyage

  Rule: Doubloons are earned by learning, never by grinding time

    @scenario:CR-01
    Scenario: Mastery and milestones mint Doubloons
      Given she masters a module, completes an on-pace week, or passes a streak milestone
      When the reward is applied
      Then she is awarded Doubloons for that achievement
      And no Doubloons are awarded for time spent, taps, or repeated easy activity alone

    @scenario:CR-02
    Scenario: Doubloons are a separate currency from XP and from streaks
      Given she has XP, an active streak, and a Doubloon balance
      When she earns or spends Doubloons
      Then her XP is unaffected
      And her streaks and functional perks (Shore Leave, Anchor, Tailwind, Lifebuoy) are unaffected
      And spending a Doubloon never spends a streak or a perk

  @scenario:CR-03
  Scenario: She spends Doubloons in Smooth's Chest
    Given she has a Doubloon balance
    When she opens Smooth's Chest from her Captain's Locker
    Then she can buy Smooth outfits and poses, ship skins, and island decorations
    And each item shows its price before she buys
    And buying an item deducts its price from her balance and adds it to her collection
    And she cannot buy an item she cannot afford

  @scenario:CR-04
  Scenario: Some items are unlocked by achievement, not just affordability
    Given an item is gated behind a rank or a milestone she has not reached
    When she views it in Smooth's Chest
    Then it is shown as locked with the achievement that unlocks it
    And it cannot be bought until that achievement is reached, even with enough Doubloons

  @scenario:CR-05
  Scenario: She equips and changes cosmetics freely
    Given she owns cosmetic items
    When she equips or swaps them
    Then Smooth, the ship, and the islands take on the equipped look
    And re-equipping an item she already owns costs nothing

  @scenario:CR-06
  Scenario: Cosmetics are purely visual
    Given she has equipped cosmetic items
    When her voyage runs
    Then the cosmetics change only how things look
    And they never affect pace, mastery, the daily minimum, XP, or any functional perk's effect

  @scenario:CR-07
  Scenario: A Captain's rank climbs with her streak and is never lost
    Given her master Voyage streak grows over time
    When her rank is read
    Then she rises through ranks such as Deckhand, First Mate, and Captain
    And a rank once earned is never taken away, even if a streak later breaks

  Rule: The economy is safe by design

    @scenario:CR-08
    Scenario: Nothing is ever bought with real money
      When she browses or buys in Smooth's Chest
      Then every item is priced only in Doubloons
      And there is no path to spend real money, and no request for payment details

    @scenario:CR-09
    Scenario: No randomised purchases, no pressure
      When she shops in Smooth's Chest
      Then every purchase is a deliberate, priced choice
      And there are no loot boxes, mystery packs, or randomised rewards
      And there are no countdown timers or fear-of-missing-out prompts aimed at the child

  @scenario:CR-10
  Scenario: The cosmetic economy stays out of the honest layer
    Given her guardian opens the guardian dashboard
    When the honest layer renders
    Then Doubloons, Smooth's Chest, and cosmetics never appear with celebration styling there
    And the guardian layer may report only plain figures (e.g. a Doubloon balance) as data
    # The child-facing chest and its styling live only on the student side (GD-05).
