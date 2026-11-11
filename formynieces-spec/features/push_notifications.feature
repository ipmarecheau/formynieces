@mvp @mobile
Feature: Self-hosted push notifications — the teacher and the parent, reached in real time
  SmoothSeas promises a human teacher in the loop. The moment that promise becomes tangible
  is when a real teacher finishes marking a child's essay at night and the parent's phone
  lights up — on the parent's own device, not the child's tablet — with the teacher's words.
  Local notifications cannot do this: the timing and the content are decided by the server
  (or a human) after the fact, and the parent is on a different device. Push is the only
  mechanism that reaches a closed app on another phone at a moment we choose.

  The sender is self-hosted. SmoothSeas holds its own Apple APNs key (.p8) and its own
  Google FCM service-account credential and calls those gateways directly from Laravel — no
  Firebase SDK, no Firebase console, no child's data flowing through Google beyond the push
  payload itself (and, for iOS, not even that). Everything about who is notified, with what,
  and when — teacher feedback, parent nudges, re-engagement, billing, quiet hours and
  opt-outs — lives in the app, under our control, and is tested with Pest against an
  in-memory fake transport so a test never touches Apple or Google.

  Because the apps are Capacitor remote-URL shells, the device registers its push token with
  the live Laravel app on launch; the server stores it against the user and platform and
  prunes it the moment a gateway reports it dead.

  Rule: Tokens are registered and pruned so we never send into the void
    @scenario:PN-01
    Scenario: A native device registers its push token
      Given a signed-in user opens the native app and grants notification permission
      When the device sends its push token with its platform
      Then the token is stored against that user and platform
      And registering the same token again updates it instead of duplicating it

    @scenario:PN-02
    Scenario: A dead token is pruned when the gateway rejects it
      Given a user has a stored push token
      When the gateway reports that token as unregistered while sending
      Then the token is removed so nothing is ever sent to it again
      And a token the gateway merely rate-limits is kept and retried later

  Rule: The family stays in control of when and whether they are reached
    @scenario:PN-03
    Scenario: Opt-out and quiet hours suppress non-critical pushes
      Given a guardian has turned off nudges, or it is inside their quiet hours
      When a non-critical push — a streak, a reminder, a re-engagement nudge — is dispatched
      Then it is suppressed and recorded as suppressed, not delivered
      But a critical account push — billing, security — is still delivered

  Rule: The human-in-the-loop and the child's progress reach the right phone
    @scenario:PN-04
    Scenario: A teacher's feedback on an essay reaches the parent in real time
      Given a child has submitted an essay and a live teacher marks it later that evening
      When the teacher's feedback is published
      Then the parent is pushed on the parent's own device that feedback is ready
      And the push carries the child's name and a deep link to the feedback

    @scenario:PN-05
    Scenario: A child's milestone reaches the parent on another device
      Given a child reaches a milestone on their tablet — a streak, island mastery, a full day's quota
      When the milestone is recorded
      Then the parent is pushed on the parent's own device to celebrate it
      And the child's own device is not spammed with a push it does not need

    @scenario:PN-06
    Scenario: A child who has drifted away is gently re-engaged
      Given a child has not done any work for several days
      When the nightly re-engagement check runs
      Then a single re-engagement push is sent referencing what is slipping
      And it respects opt-out and quiet hours and is not repeated every night

    @scenario:PN-07
    Scenario: A billing or account event reaches the account owner
      Given an account has a billing or security event — an upcoming renewal, a failed payment
      When the event fires
      Then the account owner is pushed even if nudges are off and it is inside quiet hours
      And the push is recorded as a critical delivery

  Rule: The self-hosted sender is observable and provider-agnostic
    @scenario:PN-08
    Scenario: The sender dispatches through our own APNs and FCM and logs every attempt
      Given a push is dispatched to a user with an iOS token and an Android token
      When the sender runs
      Then the iOS token is sent through APNs directly and the Android token through FCM directly
      And no Firebase SDK is involved and iOS payloads never pass through Google
      And every attempt — delivered, suppressed, or failed — is logged so the admin panel can show it
