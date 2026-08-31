<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ContactMessage;
use App\Models\OnboardingCall;
use App\Services\Onboarding\CallSlotGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * The public marketing pages: about, FAQ, contact and the parent onboarding
 * call booking (AB-01..04, FQ-01..05, CU-01..02, OC-01..04).
 */
class PublicPageController extends Controller
{
    public function about(): View
    {
        return view('about');
    }

    public function faq(): View
    {
        return view('faq');
    }

    public function contact(): View
    {
        return view('contact');
    }

    public function terms(): View
    {
        return view('legal.terms');
    }

    public function privacy(): View
    {
        return view('legal.privacy');
    }

    /**
     * XML sitemap of the public, indexable pages — submitted to Google Search
     * Console so every marketing page is discovered and crawled.
     */
    public function sitemap(): Response
    {
        $urls = [
            url('/'),
            route('about'),
            route('faq'),
            route('contact'),
            route('book.call'),
            route('terms'),
            route('privacy'),
            route('blog.index'),
        ];

        foreach (Article::published()->orderByDesc('published_at')->pluck('slug') as $slug) {
            $urls[] = route('blog.show', $slug);
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($urls as $url) {
            $xml .= '  <url><loc>'.e($url).'</loc></url>'."\n";
        }
        $xml .= '</urlset>'."\n";

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    public function sendContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'topic' => ['required', 'in:onboarding,billing,technical,feedback,general'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        ContactMessage::create($validated);

        return redirect()
            ->route('contact')
            ->with('contact_sent', true);
    }

    public function book(CallSlotGenerator $slots): View
    {
        return view('book', [
            'days' => $slots->days(),
            'booked' => OnboardingCall::find(session('booked_call')),
        ]);
    }

    public function bookCall(Request $request, CallSlotGenerator $slots): RedirectResponse
    {
        $validated = $request->validate([
            'parent_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40'],
            'child_standard' => ['nullable', 'in:Standard 3,Standard 4,Standard 5'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'slot' => [
                'required', 'string',
                // The choice must be a currently-open slot — blocks tampering and re-checks
                // availability, so a just-taken slot is refused (OC-03).
                function (string $attribute, mixed $value, \Closure $fail) use ($slots): void {
                    if (! $slots->openKeys()->contains($value)) {
                        $fail('That time was just taken — please choose another slot.');
                    }
                },
            ],
        ]);

        [$date, $time] = explode('|', $validated['slot']);

        $call = OnboardingCall::create([
            ...collect($validated)->except('slot')->all(),
            'call_date' => $date,
            'call_time' => $time,
            'status' => 'requested',
        ]);

        return redirect()
            ->route('book.call')
            ->with('booked_call', $call->id);
    }
}
