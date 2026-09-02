<?php

namespace App\Console\Commands;

use App\Mail\SeaQuestionMail;
use App\Models\Lead;
use App\Models\PracticeQuestion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * The weekly "SEA Question of the Week" nurture email (lead_capture.feature LG-10) —
 * one AI/past-paper-style question with a worked solution and the SEA countdown, to every
 * opted-in lead, each with a one-tap path to start the free month.
 */
class SendWeeklyQuestion extends Command
{
    protected $signature = 'funnel:weekly-question';

    protected $description = 'Email the SEA Question of the Week to opted-in leads';

    public function handle(): int
    {
        $question = PracticeQuestion::query()->where('is_active', true)->inRandomOrder()->first();

        if ($question === null) {
            $this->warn('No active question to send.');

            return self::SUCCESS;
        }

        $leads = Lead::query()->where('weekly_opt_in', true)->get();

        foreach ($leads as $lead) {
            Mail::to($lead->email)->send(new SeaQuestionMail($question));
        }

        $this->info("Sent the weekly question to {$leads->count()} lead(s).");

        return self::SUCCESS;
    }
}
