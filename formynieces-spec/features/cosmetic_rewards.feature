@roadmap @student
Feature: Cosmetic rewards — Smooth's wardrobe and the Captain's rank
  A separate, purely cosmetic reward track sits alongside the protective streak
  economy: outfits and poses for Smooth, ship and island decorations, and a
  Captain's rank that climbs with her streaks. It gives XP somewhere to go and
  turns a long streak into visible status. It changes nothing about pace, mastery,
  or the daily minimum. It is designed in its own pass; captured here so the gap
  is known and registered.

  Background:
    Given an onboarded student on her Voyage

  @scenario:CR-01
  Scenario: XP and milestones unlock cosmetic items
    Given she has earned XP or passed a milestone
    When she opens the cosmetics in her Captain's Locker
    Then she can unlock Smooth outfits, ship skins, and island flags
    And unlocking a cosmetic spends the earned currency, never her streaks

  @scenario:CR-02
  Scenario: A Captain's rank climbs with her streak
    Given her master Voyage streak grows over time
    When her rank is read
    Then she rises through ranks such as Deckhand, First Mate, and Captain
    And a rank once earned is never taken away

  @scenario:CR-03
  Scenario: Cosmetics are purely visual
    Given she has equipped cosmetic items
    When her voyage runs
    Then the cosmetics change only how things look
    And they never affect pace, mastery, the daily minimum, or any reward's effect
