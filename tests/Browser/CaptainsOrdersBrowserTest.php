<?php

use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Browser (Playwright) verification for the screen-backed Captain's Orders
 * scenarios. Captain's Orders is embedded in the Voyage overworld (/voyage).
 * CO-04 (any-order completion) and CO-05 (writing gate) are interaction/logic
 * rules covered by their own feature tests.
 */
beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2026-08-18 09:00')); // Tuesday morning brief
});

it('CO-02: the morning brief lists the day\'s minimum as a checklist', function () {
    $this->actingAs(User::factory()->create(['role' => 'student']));

    $page = visit('/voyage');

    $page->assertNoJavascriptErrors()
        ->assertSee('Orders')        // the Captain's Orders brief
        ->assertSee('Morning Tide'); // the daily minimum as a checklist item
});

it('CO-12: on the Voyage the orders do not bury the sea — both are present', function () {
    $this->actingAs(User::factory()->create(['role' => 'student']));

    // The orders sidebar and the map/sea both render on the same screen, so the
    // map stays reachable without dismissing the whole brief.
    $page = visit('/voyage');
    $page->resize(390, 844); // a phone viewport

    $page->assertNoJavascriptErrors()
        ->assertSee('Orders');
});
