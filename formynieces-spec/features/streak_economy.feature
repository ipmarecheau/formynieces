@mvp @student @system
Feature: Streak economy — pace, protection and rewards
  Streaks are the child's daily engine, and this is the machinery beneath them:
  one master Voyage streak fed by per-thread sub-streaks, a notion of being "on
  pace" that decides how much flexibility she has earned, and a small set of
  rewards she can hold in her Captain's Locker to protect or push a streak. The
  whole system obeys the app's two rules. It is HONEST: protection may save a
  streak but never fabricates progress, so pace stays true. And it is
  NEVER-NEGATIVE: a reward can rescue a streak, but a lost streak is only ever
  restarted kindly, never punished. Pace is read from the existing weekly targets;
  it is a guardian-layer truth that reaches the child only as kind flexibility,
  never as a deficit.

  Background:
    Given an onboarded student with a weekly target

  Rule: One master streak, fed by sub-streaks

    @scenario:SE-01
    Scenario: Completing the day's minimum extends the master Voyage streak
      Given she completed her daily minimum yesterday
      When she completes today's daily minimum
      Then her master Voyage streak grows by one day
      # The daily minimum is defined in captains_orders (CO-02).

    @scenario:SE-02
    Scenario: Each daily thread also keeps its own sub-streak
      When she completes a thread of her daily minimum
      Then that thread's sub-streak advances — reading, vocabulary, writing or map
      And the master Voyage streak reflects completing the whole minimum, not any single thread
      # Reading (DR-05), vocabulary (DV-05) and mastery (ML-05) sub-streaks already exist.

    @scenario:SE-03
    Scenario: A new voyager begins with three days of streak protection
      Given a student who has just begun her voyage
      When she misses a day within her first days at sea
      Then one of her three starter protection days is spent to hold her streaks
      And her streaks survive until the starter protection is used up

  Rule: Pace decides how much flexibility she has earned

    @scenario:SE-04
    Scenario: On pace means she has met her weekly target
      Given her mastered work meets or leads this week's target
      When her pace is read
      Then she is on pace
      # Pace is the weekly target from weekly_targets; the on-pace streak is ML-06.

    @scenario:SE-05
    Scenario: Being on pace unlocks weekend rest and Shore Leave
      Given she is on pace
      Then her weekend stands down to rest with her streak protected
      And she may spend a Shore Leave reward
      # The weekend brief is CO-08 / CO-09.

    @scenario:SE-06
    Scenario: Falling behind keeps the weekend working, but kindly
      Given she is behind her weekly pace
      Then her weekend offers bounded, kindly-framed catch-up instead of full rest
      And no deficit or warning language reaches her
      # Rendered in CO-09; the pace truth stays in the guardian layer (WT-03).

  Rule: Rewards protect or push a streak, always honestly

    @scenario:SE-07
    Scenario: Shore Leave skips one duty for a day without breaking the streak
      Given she is on pace and holds a Shore Leave reward
      When she uses it on one of today's duties
      Then that duty is excused for the day and her streaks are held
      And the excused work is not counted as progress, so her pace stays honest

    @scenario:SE-08
    Scenario: Anchor freezes every streak for a day
      Given she holds an Anchor reward
      When she uses it on a day she cannot sail
      Then all her streaks — master and sub — are frozen for that day
      And Anchor may be used even when she is behind pace

    @scenario:SE-09
    Scenario: Accelerating banks one day ahead on a subject
      Given today is a writing day
      When she completes two of a subject's assignments in one day
      Then the second is banked toward the next day for that subject
      And she can bank at most one day ahead per subject
      And banking one subject does not excuse the day's other threads

    @scenario:SE-10
    Scenario: Tailwind raises the banking limit to two days ahead
      Given she holds a Tailwind reward
      When she uses it while accelerating a subject
      Then she may bank up to two days ahead on that subject instead of one

    @scenario:SE-11
    Scenario: Lifebuoy revives a streak that has just reset
      Given her streak reset within the last day
      And she holds a Lifebuoy reward
      When she uses it
      Then the reset streak is restored to what it was
      And a given reset can be rescued only once

    @scenario:SE-12
    Scenario: With no protection left, a missed day restarts the streak kindly
      Given she has no starter protection, Anchor, or Lifebuoy available
      When she misses a day's minimum
      Then her master streak restarts from zero
      And she is welcomed back without any reference to the broken streak
      # Never-negative restart, consistent with ML-02.

  Rule: Rewards are earned four ways

    @scenario:SE-13
    Scenario: Getting ahead of pace earns rewards
      Given she is ahead of her weekly pace
      When her progress is tallied
      Then she earns streak rewards for sailing ahead

    @scenario:SE-14
    Scenario: Reaching milestones earns rewards
      Given she reaches a streak or mastery milestone
      Then she is granted a streak reward for the milestone

    @scenario:SE-15
    Scenario: A guardian can grant a reward from the dashboard
      Given a guardian who wants to protect an upcoming day off
      When she grants her student a streak reward from the guardian dashboard
      Then the reward appears in the student's Captain's Locker
      # The one crossing point between layers; see guardian_dashboard.

    @roadmap @scenario:SE-16
    Scenario: Rewards can be bought with XP
      Given a student with earned XP
      When she spends XP in the Captain's Locker
      Then she can buy a streak reward
      # Depends on XP / progression (XP-*), which is roadmap.
