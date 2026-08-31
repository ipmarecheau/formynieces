# Session Handoff — 2026-08-31 · Guardian Bridge, registration, legal, SEO, blog

Durable note so the context window can be cleared. Readable by both Claude agents on this repo.

## Standing state
- Branch `main`. **All this session's work is COMMITTED + PUSHED** (HEAD `c3c5cff`). Prod is LIVE at
  https://smoothseas.org and auto-deploys on push (GitHub Actions → VPS Docker `formynieces`).
- Working tree: only `.claude/settings.json` (M, not ours) and untracked `scripts/reels/*.mjs`
  (reel-screenshot scripts, not ours) — leave both alone.
- Full test suite green except a KNOWN pre-existing flake: `SpecsTraceMvpFilterTest` can time out
  under full-suite load (git subprocess) — passes in isolation.
- Dev server: `php artisan serve` on :8000 (dev SQLite `database/database.sqlite`). Prod is the
  Docker container on :8080 — NEVER touch it from here.
- Review accounts (dev DB, pw `Guardian123!`): `verify-guardian@smoothseas.test` → child **Amara**
  (rich data). `demo-guardian@smoothseas.test` → thin. (DemoStudentSeeder may reset the password.)

## Shipped this session (commit → what)
- `4d64ab3`,`37ac013` — **Guardian Bridge** dashboard rebuild: sidebar app (Overview/This week/Pace/
  Progress/Estimator/Rewards), light editorial theme, exam-agent estimator, **pace-calc bug fixed**
  (analyse() now uses per-student PacingClock, was "behind = whole syllabus"), weekly pace recalc
  command + `pace_recalculated_at` "Progress updated" stamp. Specs GD-12..15 (verified).
- `d789b4a` — **Secure registration**: Cloudflare Turnstile CAPTCHA, phone capture, email verify by
  link OR 6-digit code, `VerifyAccount` Livewire screen, auto-advance to `/child-setup`. Twilio Verify
  behind `PhoneVerifier` interface (stub off-prod). **Phone verification OFF by default (free launch)**
  via `PHONE_VERIFICATION_ENABLED`. Specs GO-12..15. Dep added: `twilio/sdk`.
- `c9b815d` — **T&C + Privacy Policy** (`/terms`, `/privacy`; 64-Bit Software Solutions entity; strong
  children's-data protections) with scroll-gated signup acceptance (`terms_accepted_at`+version).
  **Cosmetic marketplace spec** promoted to @mvp (`cosmetic_rewards.feature` = Smooth's Chest,
  Doubloons; NOT built). **Landing**: Sign-up is now the primary CTA everywhere; sticky mobile CTA
  bar; mobile polish; nav right-grouped. Spec GO-16.
- `a6fd2c3` — fix: froze the clock in `ParentDailySummaryTest` (weekend-fragile, pre-existing).
- `e86e38b` — landing demo reel is full-bleed (edge-to-edge).
- `fb06800` — Google Search Console verification file (verified; DO NOT delete `public/google6f…html`).
- `caa1bb1` — **Technical SEO**: `<x-seo>` component (title/meta/canonical/OG/Twitter) on all public+
  legal pages, landing JSON-LD (Organization/WebSite), `/sitemap.xml` + `robots.txt`. Spec seo.feature
  (SEO-01..05) + `SEO_STRATEGY.md`. Also fixed a latent Blade bug (welcome used inline `@php(...)`).
- `271d97e`,`ecd8275` — **Brand**: student login domain → `@smoothseas.org`; safe in-code
  formynieces→smoothseas fixes (child-setup display bug, support/admin emails). A full audit ran; the
  DANGEROUS/structural renames (git remote, deploy path `/opt/formynieces`, Docker container name,
  `formynieces-spec/` dir, `.claude/settings.json` docker prefixes) were intentionally NOT changed —
  they need a coordinated OWNER infra rename.
- `45f9f6c`,`bf4d0ba` — **Auto-generated student username** = first initial + first 4 of last name,
  numeric suffix on collision (server), with a **live preview** as the guardian types the name.
- `c3c5cff` — **Blog / resources library** LIVE at `/blog`: 30 backdated markdown articles
  (`database/data/blog/*.md`) → `articles` table via `ArticleSeeder` (idempotent, registered in
  DatabaseSeeder so prod auto-seeds). Per-article SEO + Article JSON-LD, category filter, drafts hidden,
  in sitemap, "Resources" nav link. Spec blog.feature (BLOG-01..05). NOTE: articles mostly written by a
  subagent — NOT hand-reviewed for accuracy/tone; sanity-check cited stats before relying on them.

## Open items / next up
1. **Review the 30 blog articles** for tone/accuracy + verify any cited stats. Add cover images (field
   exists, empty). Then Isaac submits `/sitemap.xml` in Google Search Console.
2. **Child-setup page copy** still says "Set Up Your **Niece**" + feminine voice ("Her Name") — legacy
   ForMyNieces framing; neutralise to "your child"/"them" for brand consistency (behind auth, not SEO).
3. **`$200/month` pricing copy** on the landing vs the free-launch direction — reconcile.
4. **Legal review** of T&C + Privacy Policy by a T&T attorney (they're good-faith drafts).
5. **Owner infra rename** (formynieces→smoothseas): GitHub repo, VPS `/opt/formynieces` + Docker
   container/image name (edit `deploy.sh`, `.github/workflows/deploy.yml`, `.claude/settings.json`
   docker prefixes IN LOCKSTEP), optionally the `formynieces-spec/` dir (+ update SpecsVerify/Trace
   `base_path()`). Ensure `support@`/`admin@smoothseas.org` mailboxes exist. Prod `.env` is separate.
6. **Cosmetic marketplace (Smooth's Chest, CR-01..10)** is specced @mvp but NOT built.
7. Tested-but-UNVERIFIED scenarios awaiting Isaac's browser sign-off (do NOT `specs:verify` without
   his confirmation): GO-12..16, SEO-01..05, BLOG-01..05.

## Prod config to set for full features (owner, on the server)
- `TURNSTILE_SITE_KEY`/`TURNSTILE_SECRET_KEY` (free) → turns the CAPTCHA on.
- `PHONE_VERIFICATION_ENABLED=true` + `TWILIO_ACCOUNT_SID`/`AUTH_TOKEN`/`VERIFY_SERVICE_SID` → turns on
  WhatsApp/SMS phone OTP (currently off = free launch, phone captured unverified).
