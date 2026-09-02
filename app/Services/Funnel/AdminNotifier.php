<?php

namespace App\Services\Funnel;

use App\Models\Lead;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

/**
 * Routes all "WhatsApp" traffic to the TEAM's own number, not to parents (LG-12).
 * Config-gated: with no admin number set, nothing is sent and nothing errors. When a
 * number is configured and Twilio WhatsApp credentials exist, it sends there; otherwise
 * it records the notification to the log so the event is never silently lost.
 */
class AdminNotifier
{
    /** Notify the team that a new lead was captured. */
    public function leadCaptured(Lead $lead): void
    {
        $this->send("New SmoothSeas lead: {$lead->email}".
            ($lead->child_level ? " · {$lead->child_level}" : '').
            ($lead->whatsapp ? " · WA {$lead->whatsapp}" : ''));
    }

    /** Notify the team that a lead converted to a trial. */
    public function leadConverted(Lead $lead): void
    {
        $this->send("Lead converted to a trial: {$lead->email}");
    }

    private function send(string $message): void
    {
        $to = config('funnel.admin_whatsapp');

        if ($to === null || $to === '') {
            return; // channel not configured — no-op, no error (LG-12)
        }

        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.whatsapp_from');

        if ($sid && $token && $from && class_exists(Client::class)) {
            try {
                (new Client($sid, $token))->messages->create(
                    'whatsapp:'.$to,
                    ['from' => 'whatsapp:'.$from, 'body' => $message],
                );

                return;
            } catch (\Throwable $e) {
                Log::warning('Admin WhatsApp send failed: '.$e->getMessage());
            }
        }

        // Configured but no working transport — record it rather than lose it.
        Log::info('[admin-notify → '.$to.'] '.$message);
    }
}
