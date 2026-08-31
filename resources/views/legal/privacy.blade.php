{{-- MAINTAINER NOTE: Thorough good-faith draft, NOT legal advice. Have it reviewed
     by an attorney licensed in Trinidad & Tobago (and, if you serve users abroad,
     a data-protection specialist) before relying on it. --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <x-seo title="Privacy Policy — SmoothSeas"
           description="How SmoothSeas collects, uses, and protects personal information — with special protections for children's data." />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --ink:#12222e; --ink-soft:#40566a; --ink-faint:#6b8199; --paper:#fbf8f2; --paper-2:#fff; --line:#e7ddcd; --teal:#0d7d8c; --teal-deep:#0a5c68; --amber-tint:#fdf1d6; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { background:var(--paper); color:var(--ink); font-family:'Nunito',system-ui,sans-serif; line-height:1.6; }
        .wrap { max-width:760px; margin:0 auto; padding:40px 22px 80px; }
        .top { display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:24px; }
        .brand { display:flex; align-items:center; gap:10px; text-decoration:none; }
        .brand-mark { width:36px; height:36px; border-radius:10px; background:linear-gradient(135deg,var(--teal),var(--teal-deep)); display:flex; align-items:center; justify-content:center; font-size:18px; }
        .brand-name { font-family:'Fredoka',sans-serif; font-weight:700; font-size:19px; color:var(--ink); }
        .back { font-size:14px; font-weight:800; color:var(--teal); text-decoration:none; }
        h1 { font-family:'Fredoka',sans-serif; font-weight:700; font-size:30px; margin-bottom:6px; letter-spacing:-.01em; }
        .meta { font-size:13px; font-weight:700; color:var(--ink-faint); margin-bottom:20px; }
        h2 { font-family:'Fredoka',sans-serif; font-weight:600; font-size:19px; color:var(--ink); margin:26px 0 8px; }
        p { font-size:15px; color:var(--ink-soft); margin-bottom:10px; }
        ul { margin:0 0 12px 20px; }
        li { font-size:15px; color:var(--ink-soft); margin-bottom:6px; }
        strong { color:var(--ink); }
        a { color:var(--teal); }
        .card { background:var(--paper-2); border:1px solid var(--line); border-radius:18px; padding:26px 28px; box-shadow:0 1px 2px rgba(18,34,46,.06),0 4px 12px rgba(18,34,46,.05); }
        .highlight { background:var(--amber-tint); border:1px solid #f2d69a; border-radius:14px; padding:16px 18px; margin:14px 0; }
        .highlight p { color:#6b4e12; margin:0; }
        .highlight strong { color:#5a3d00; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="top">
            <a href="{{ url('/') }}" class="brand"><span class="brand-mark">⚓</span><span class="brand-name">SmoothSeas</span></a>
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : url('/') }}" class="back">← Back</a>
        </div>
        <div class="card">
            <h1>Privacy Policy</h1>
            <p class="meta">Version {{ config('legal.privacy_version') }} · Last updated {{ \Illuminate\Support\Carbon::parse(config('legal.privacy_version'))->isoFormat('D MMMM YYYY') }}</p>

            <p>SmoothSeas is a study aid that helps children prepare for the Secondary Entrance Assessment (SEA). Because our users include children, protecting their information is central to how we operate. This Policy explains what we collect, why, how we protect it, and the choices you have. It forms part of our <a href="{{ route('terms') }}">Terms &amp; Conditions</a>.</p>

            <div class="highlight">
                <p><strong>Our promise about children's data.</strong> A child only uses SmoothSeas under the control of a parent or guardian who has agreed to this Policy. We collect the least information needed to teach; we use a child's information only to provide and improve the learning service; we <strong>never sell it, never use it for advertising, and never build advertising or marketing profiles of a child</strong>; and a guardian can review, correct, or delete it at any time.</p>
            </div>

            <h2>1. Who we are</h2>
            <p>SmoothSeas is operated by <strong>64-Bit Software Solutions</strong>, of 180 Upper 7th Avenue, Malick, Barataria, {{ config('legal.jurisdiction') }} ("we", "us", "our"), which is the data controller for the information described here. You can reach us about privacy at <a href="mailto:{{ config('legal.contact_email') }}">{{ config('legal.contact_email') }}</a>.</p>

            <h2>2. Whose information this covers</h2>
            <ul>
                <li><strong>Guardians</strong> — the adult who creates and controls the account.</li>
                <li><strong>Children</strong> — each child a guardian adds and supervises. A child's information is provided and controlled by their guardian.</li>
            </ul>
            <p>The account and all consents are held by the guardian. Children do not create accounts on their own, are not asked for payment or contact details, and are not marketed to.</p>

            <h2>3. Information we collect</h2>
            <p><strong>From the guardian:</strong> name, email address, phone number, password (stored only in hashed form), the fact and version of consents you give (age attestation, terms, this Policy), and technical data such as IP address and device/browser information for security.</p>
            <p><strong>About the child (provided by the guardian, or generated as the child learns):</strong> the child's name and chosen username, target SEA year, any weak areas you flag, and the child's learning activity — lessons and practice attempted, answers, mastery and pacing, writing submissions, reading and vocabulary activity, streaks and rewards, and any school-journal material (including assessment images) you or the child upload.</p>
            <p><strong>Automatically:</strong> essential cookies and similar technology to keep you signed in and keep the Service secure, plus basic usage and error logs. We do not use third-party advertising or cross-site tracking cookies.</p>

            <h2>4. How we use information</h2>
            <ul>
                <li>To provide the Service — deliver lessons, run the diagnostic, set pacing, track progress, and generate feedback and estimates.</li>
                <li>To provide AI-assisted feedback on a child's writing and progress (see section 6).</li>
                <li>To verify a guardian's email and phone, and to communicate with the guardian about the account.</li>
                <li>To keep the Service safe — prevent abuse, fraud, and unauthorised access (this is why we use a CAPTCHA and verification).</li>
                <li>To maintain, debug, and improve the Service.</li>
                <li>To comply with legal obligations.</li>
            </ul>
            <p>We do <strong>not</strong> sell personal information, and we do not use a child's information for advertising, ad targeting, or profiling.</p>

            <h2>5. Our legal bases</h2>
            <p>We process information where we have a lawful basis to do so: to <strong>perform our contract</strong> with the guardian (running the account), with the guardian's <strong>consent</strong> (including consent on behalf of a child, and for optional messaging), for our <strong>legitimate interests</strong> in securing and improving the Service (balanced against your rights), and to meet <strong>legal obligations</strong>. We handle personal data in line with applicable data-protection law, including the Data Protection Act of {{ config('legal.jurisdiction') }} to the extent in force, and, where it applies to users abroad, comparable law. A guardian may withdraw consent at any time by contacting us or closing the account.</p>

            <h2>6. AI-assisted features</h2>
            <p>Some features use third-party artificial-intelligence services to generate feedback and summaries (for example, marking a practice essay or writing a guardian briefing). To do this, the relevant content — such as a writing submission and its prompt — is sent to the AI provider to produce a response. We use providers under agreements intended to keep this data confidential and, where the provider offers it, we do not permit your content to be used to train their models. AI output may contain errors and is guidance only. If you would rather a child's writing not be processed by AI features, contact us.</p>

            <h2>7. When we share information</h2>
            <p>We share information only as needed to run SmoothSeas, and never to sell it:</p>
            <ul>
                <li><strong>Service providers (processors)</strong> acting on our instructions — for example email delivery, phone/WhatsApp/SMS verification, anti-abuse CAPTCHA, hosting, and the AI providers above — bound by confidentiality and data-protection obligations.</li>
                <li><strong>Legal and safety</strong> — where required by law, court order, or to protect the rights, safety, and security of users or the public.</li>
                <li><strong>Business transfer</strong> — if the Service is merged or acquired, information may transfer as part of that transaction, subject to this Policy.</li>
            </ul>

            <h2>8. International transfers</h2>
            <p>Some of our providers process data outside {{ config('legal.jurisdiction') }}. Where information is transferred across borders, we take reasonable steps to ensure it is protected by appropriate safeguards and handled consistently with this Policy.</p>

            <h2>9. How long we keep information</h2>
            <p>We keep personal information only for as long as needed to provide the Service and for legitimate legal, security, and operational purposes. When you close an account or ask us to delete a child's information, we delete or anonymise it within a reasonable period, except where we must retain limited records to meet legal obligations.</p>

            <h2>10. How we protect information</h2>
            <p>We use technical and organisational measures appropriate to the sensitivity of the data — including encryption in transit, hashed passwords, access controls, and separation of the child's motivational world from the guardian's honest reporting layer. No system is perfectly secure, but we work to protect children's information with particular care and will notify affected guardians and any required authority of a breach as the law requires.</p>

            <h2>11. Your rights and choices</h2>
            <p>As a guardian, on your own behalf and on behalf of your child, you may:</p>
            <ul>
                <li>access and review the information we hold;</li>
                <li>correct inaccurate information;</li>
                <li>delete a child's information or close the account;</li>
                <li>withdraw a consent you have given;</li>
                <li>object to or restrict certain processing;</li>
                <li>request a copy of information you provided.</li>
            </ul>
            <p>To exercise any of these, email <a href="mailto:{{ config('legal.contact_email') }}">{{ config('legal.contact_email') }}</a>. We may need to verify your identity as the account guardian first. You also have the right to complain to the data-protection authority in {{ config('legal.jurisdiction') }}.</p>

            <h2>12. Children's privacy — additional commitments</h2>
            <ul>
                <li>We do not knowingly collect personal information directly from a child without guardian consent. Children register through, and are controlled by, a consenting guardian.</li>
                <li>We collect the minimum needed to teach, and never ask a child for contact details, location, or payment.</li>
                <li>We never advertise to children, never sell or share their data for marketing, and never build advertising profiles of them.</li>
                <li>If we learn we have collected a child's information without the required guardian consent, we will delete it promptly.</li>
                <li>A guardian is always in control and can review or delete their child's information at any time.</li>
            </ul>

            <h2>13. Changes to this Policy</h2>
            <p>We may update this Policy from time to time. If we make material changes, we will take reasonable steps to notify guardians (for example by email or in-app) and, where appropriate, ask you to review the update. The version and date at the top show the current Policy.</p>

            <h2>14. Contact</h2>
            <p>Questions or requests about privacy? Contact 64-Bit Software Solutions at <a href="mailto:{{ config('legal.contact_email') }}">{{ config('legal.contact_email') }}</a>.</p>
        </div>
    </div>
</body>
</html>
