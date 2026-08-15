@mvp @guardian
Feature: Parent onboarding call — book 15 minutes with the founder
  The clearest conversion path is a conversation. A parent books a free
  15-minute onboarding call directly in the founder's calendar: weekdays
  5:00pm–8:00pm and Saturdays 8:00am–5:00pm, Trinidad and Tobago time.
  Slots are 15 minutes, shown for the coming two weeks, never double-booked,
  and every booking lands in the admin panel.

  @scenario:OC-01
  Scenario: The booking page shows real availability
    Given a visitor opens the booking page
    Then the next two weeks of days are shown with open slots
    And weekday slots run from 5:00pm to 8:00pm, ending with a 7:45pm start
    And Saturday slots run from 8:00am to 5:00pm, ending with a 4:45pm start
    And Sundays show no slots, and past days are never offered

  @scenario:OC-02
  Scenario: A parent books a call
    Given a visitor on the booking page
    When they choose an open slot and submit their name and email
    Then the booking is stored
    And the page confirms the day and time of their call

  @scenario:OC-03
  Scenario: A taken slot cannot be double-booked
    Given a slot is already booked
    When another visitor submits the same slot
    Then the booking is refused and they are asked to choose another time
    And the taken slot disappears from the offered availability

  @scenario:OC-04
  Scenario: The landing page funnels to the call
    Given a visitor on the landing page
    Then the hero's primary action invites them to book a free 15-minute onboarding call
    And it links to the booking page

  @scenario:OC-05
  Scenario: The team sees bookings in the admin panel
    Given a parent has booked an onboarding call
    When an admin opens the admin panel
    Then the booking appears with day, time, parent and child's standard
    And its status can be moved from requested to confirmed, completed or cancelled
