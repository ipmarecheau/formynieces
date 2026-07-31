@roadmap @student
Feature: XP, multipliers and weekly leagues
  Moving through the learning loop earns experience points. XP rewards the whole
  loop — showing up, reading, focusing and answering — not accuracy alone, and it
  only ever rises: a mistake costs a combo multiplier, never banked points. A
  weekly league sets her among a small group of similar peers under playful
  nicknames, so there is a reason to return without a global ranking or a
  permanent bottom. XP is a separate currency from her streaks.

  Background:
    Given a student is working through her modules

  Rule: XP only ever rises

    @scenario:XP-01
    Scenario: Desirable actions across the whole loop earn XP
      When she opens a lesson, completes a tutorial, answers correctly, and masters a module
      Then each of those actions adds XP
      And her total XP increases

    @scenario:XP-02
    Scenario: Consecutive correct answers build a multiplier
      Given she answers practice questions correctly in a row
      When each correct answer is scored
      Then a combo multiplier rises with the streak up to a cap
      And later correct answers are worth more XP

    @scenario:XP-03
    Scenario: A wrong answer drops the multiplier but never the banked XP
      Given she has built up a combo multiplier
      When she answers a question incorrectly
      Then the multiplier returns to its base
      And her total XP does not decrease
      And the moment is framed as not-yet, with no failure language

    @scenario:XP-04
    Scenario: Focus blocks and mastery pay a one-off bonus
      When she completes a focus block or masters a module
      Then she is awarded a one-off XP bonus on top of her per-answer XP

  Rule: A weekly league, never a global ranking

    @scenario:XP-05
    Scenario: Weekly XP resets while lifetime XP is kept
      Given a new league week begins
      When her weekly XP is reset to zero
      Then her lifetime XP is unchanged

    @scenario:XP-06
    Scenario: She is placed in a small league of similar peers
      Given the league week begins
      When leagues are formed
      Then she is grouped with a small number of peers of similar recent activity

    @scenario:XP-07
    Scenario: The league shows nicknames, never real names
      When she views her league standings
      Then every member is shown by a playful nickname
      And no student's real name is revealed

    @scenario:XP-08
    Scenario: The top are promoted and the bottom relegated at week's end
      Given the league week ends
      When the standings are settled
      Then the highest-ranked members move up a league
      And the lowest-ranked members move down a league
      And no member is shown a permanent, all-time bottom rank

    @scenario:XP-09
    Scenario: The leaderboard is guardian opt-in
      Given a student whose guardian has not opted her into leagues
      When she uses the app
      Then she still earns XP as normal
      But she is not placed in a league and sees no leaderboard
