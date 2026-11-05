<?php
declare(strict_types=1);

/* The published privacy policy, written for HiWS as it runs today: a static
   site served from Uganda with a contact form, ordinary server logs and
   Google AdSense advertising. Uganda's Data Protection and Privacy Act, 2019
   comes first because that is where the operator is; the GDPR, the UK GDPR
   and the CCPA are answered for the visitors they cover. Every practice named
   here is one the site actually performs. */
require __DIR__ . '/app/init.php';

$title = 'Privacy Policy';

require __DIR__ . '/app/parts/header.php';
?>

<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <h1 class="mb-2">Privacy Policy</h1>
            <p class="text-body-secondary small mb-4">Last updated 5 November 2026</p>

            <div class="alert alert-secondary rounded-4" role="note">
                <strong>In short:</strong> we keep what you type into the contact form and what any
                web server records on its own. There are no accounts, nothing is sold, and no one is
                followed across the web. When advertisements appear they are Google's, and you can
                switch them off.
            </div>

            <p>
                HiWS — <a href="<?= url() ?>"><?= e(SITE_DOMAIN) ?></a> and its project subdomains —
                is run by Hiswill Kato from Mityana, Uganda. In data-protection terms we are the
                <em>data controller</em>: we decide what is collected and what it is used for.
                Questions go to
                <a href="mailto:<?= e(SITE_EMAIL) ?>"><?= e(SITE_EMAIL) ?></a>, to
                <a href="tel:<?= e(SITE_PHONE) ?>"><?= e(SITE_PHONE) ?></a>, or through the
                <a href="<?= url('contact') ?>">contact page</a>.
            </p>

            <p>
                This policy covers only what happens through this website. It does not apply to
                other sites we link to, which keep their own policies.
            </p>

            <h2 class="h4 mt-4">Information we collect</h2>

            <p>
                <strong>What you send us.</strong> The contact form asks for your name, email
                address, subject and message. That message is handed to the mail server on this
                machine and answered — it is not written into any database on the site. We keep the
                exchange only as long as it takes to deal with it.
            </p>

            <p>
                <strong>What the server records.</strong> Every web server keeps log files: the IP
                address of the request, the browser and device, the page asked for, the time, and
                where the link came from. Our host and Cloudflare — the network that delivers and
                protects the site — hold these. They are used for security, for finding faults, and
                for counting what is read in total, never for identifying who read it.
            </p>

            <p>
                <strong>What is kept on your device.</strong> Two small things: a session cookie
                that carries the contact form's anti-forgery token, so a forged submission is
                refused; and your light-or-dark theme choice, kept in your browser's local storage
                rather than in a cookie. Neither of them says anything about who you are, and both
                disappear when you clear your browser's site data.
            </p>

            <p>
                <strong>What our tools pass on.</strong> Several tools work by asking somebody else:
                a WHOIS lookup asks the domain registry, an IP lookup asks a geolocation service, a
                word lookup asks Wiktionary, exchange rates come from a public rates service, and
                screenshots and short links go to those services. Only the value you typed — a
                domain, an address, a word — is sent, along with the ordinary request our server
                makes. Those services handle it under their own policies, not ours.
            </p>

            <h2 class="h4 mt-4">How we use it</h2>
            <p>We use what we collect to:</p>
            <ul>
                <li>answer you, and nothing else, when you write to us</li>
                <li>keep the site secure, working and quick to load</li>
                <li>read the log totals in aggregate, so we know which pages are worth keeping</li>
                <li>show advertising, if it is shown, through Google AdSense</li>
                <li>meet a legal duty if one applies</li>
            </ul>
            <p>
                We do not build profiles of visitors, we do not send marketing email, and we do not
                sell, rent or trade personal information to anyone.
            </p>

            <h2 class="h4 mt-4">Why we are allowed to process it</h2>
            <p>
                Uganda's <strong>Data Protection and Privacy Act, 2019</strong> requires personal
                data to be processed lawfully, fairly and transparently, for a stated purpose, and
                kept no longer than that purpose needs. This policy is how we tell you what those
                purposes are; everything listed above stays within them.
            </p>
            <p>
                Where the GDPR or the UK GDPR applies to you, the basis is one of: your consent,
                where we ask for it; the performance of a contract, when you have sent us something
                expecting a reply; or our legitimate interest in running and securing a website in
                a way that does not override your rights.
            </p>

            <h2 class="h4 mt-4">Cookies</h2>
            <p>
                A cookie is a small file a site asks your browser to keep on your device. This site
                sets very few:
            </p>
            <ul>
                <li>
                    <strong>The session cookie</strong> — strictly necessary. It holds the token
                    that proves the contact form was really submitted from this page, and it ends
                    when you close the browser.
                </li>
                <li>
                    <strong>The theme preference</strong> — written to local storage rather than a
                    cookie, so your light or dark choice survives a visit.
                </li>
                <li>
                    <strong>Advertising cookies</strong> — set by Google and its advertising
                    partners when an advertisement is shown. They are described in the next
                    section.
                </li>
            </ul>
            <p>
                We run no analytics cookies and no social-media trackers. You can block or delete
                cookies in your browser's settings at any time; blocking the session cookie simply
                means the contact form will refuse to send.
            </p>

            <h2 class="h4 mt-4">Advertising</h2>
            <p>
                HiWS displays advertisements through <strong>Google AdSense</strong>. Google acts as
                a third-party vendor in serving them, and Google's own technologies and policies
                govern what those advertisements do:
            </p>
            <ul>
                <li>
                    Google uses cookies, including the DoubleClick DART cookie, to serve
                    advertisements based on your visits to this and to other websites.
                </li>
                <li>
                    Google's use of advertising cookies allows Google and its partners to serve
                    advertisements based on visits to this and/or other sites on the Internet.
                </li>
                <li>
                    Advertisements may be personalised where the law permits it. HiWS does not
                    choose which advertisements appear, does not see who clicks them, and does not
                    receive the data behind them.
                </li>
            </ul>
            <p>You can control personalised advertising, or switch it off entirely, here:</p>
            <ul>
                <li>Google Ads Settings — <a href="https://adssettings.google.com/" rel="noopener">adssettings.google.com</a></li>
                <li>Network Advertising Initiative opt-out — <a href="https://optout.networkadvertising.org/" rel="noopener">optout.networkadvertising.org</a></li>
                <li>Digital Advertising Alliance opt-out — <a href="https://optout.aboutads.info/" rel="noopener">optout.aboutads.info</a></li>
                <li>European opt-out (EDAA) — <a href="https://www.youronlinechoices.com/" rel="noopener">youronlinechoices.com</a></li>
            </ul>
            <p>Google's own documents explain the rest:</p>
            <ul>
                <li><a href="https://policies.google.com/privacy" rel="noopener">Google's privacy policy</a></li>
                <li><a href="https://policies.google.com/technologies/ads" rel="noopener">How Google uses advertising technologies</a></li>
                <li><a href="https://policies.google.com/technologies/partner-sites" rel="noopener">How Google uses information from sites that use its services</a></li>
            </ul>
            <p>
                Should other advertising partners be added, they will be listed here with links to
                their policies. We have no access to and no control over the cookies that third-party
                advertisers set; their policies, rather than this one, cover them.
            </p>

            <h2 class="h4 mt-4">Who we share it with</h2>
            <p>We share nothing beyond what these few need to do their work:</p>
            <ul>
                <li>our hosting provider, which stores the server logs</li>
                <li>Cloudflare, which delivers the site and sees each request's address</li>
                <li>the mail provider that carries messages you send us</li>
                <li>Google, for advertising, as set out above</li>
                <li>the lookup services named under “Information we collect”</li>
            </ul>
            <p>
                Beyond that, we disclose information only when a law requires it — an order of a
                Ugandan court, or a body with the power to ask.
            </p>

            <h2 class="h4 mt-4">Data outside Uganda</h2>
            <p>
                This site is hosted and delivered from infrastructure outside Uganda, so information
                may be processed abroad simply by your visiting. Uganda's Data Protection and
                Privacy Act, 2019 requires safeguards for personal data transferred out of the
                country, and we rely on the contractual protections our providers give us. Where the
                GDPR or the UK GDPR applies to a transfer, an equivalent safeguard is relied on for
                the same reason.
            </p>

            <h2 class="h4 mt-4">How long we keep it</h2>
            <ul>
                <li>A message you sent stays only as long as the exchange needs, then a short record of it is kept.</li>
                <li>Server logs are held for a short period by our host and by Cloudflare, for security and fault-finding.</li>
                <li>There are no accounts, so nothing about you sits in a database waiting.</li>
                <li>Advertising data is kept by Google under Google's own retention rules.</li>
            </ul>

            <h2 class="h4 mt-4">Your rights</h2>

            <h3 class="h5 mt-4">In Uganda — the Data Protection and Privacy Act, 2019</h3>
            <p>
                You may ask what we hold about you and for a copy of it, have anything inaccurate
                corrected, object to or ask for the deletion of what we hold, and withdraw consent
                wherever processing rests on consent. Write to us using the details at the bottom
                of this page and we will answer within 30 days.
            </p>
            <p>
                If our answer does not satisfy you, you may complain to the
                <strong>Personal Data Protection Office (PDPO)</strong>, the office established
                under the National Information Technology Authority — Uganda (NITA-U) to enforce
                the Act: <a href="https://pdpo.go.ug/" rel="noopener">pdpo.go.ug</a>.
            </p>

            <h3 class="h5 mt-4">If you are in the European Economic Area or the United Kingdom</h3>
            <p>
                The GDPR and the UK GDPR give you the rights of access, rectification, erasure,
                restriction, objection and portability, and the right to complain to your own
                supervisory authority. We answer within one month.
            </p>

            <h3 class="h5 mt-4">If you are in California</h3>
            <p>
                The CCPA, as amended by the CPRA, gives you the right to know what personal
                information is collected about you, to have it deleted and corrected, and to opt out
                of its sale or sharing. HiWS does not sell or share personal information as those
                laws define those terms.
            </p>

            <h2 class="h4 mt-4">Children's information</h2>
            <p>
                HiWS is not aimed at children under 13, and we do not knowingly collect personal
                information from them. If you believe a child has sent us something, write to us and
                it will be removed. Where the GDPR applies, the same protection extends to children
                below the age your country sets for consenting to information-society services.
            </p>

            <h2 class="h4 mt-4">Security</h2>
            <p>
                The site is served over HTTPS, messages are not stored in a database, and access to
                the machine is limited to the people who run it. No method of sending information
                over the Internet is completely secure, so we promise care rather than perfection.
            </p>

            <h2 class="h4 mt-4">Other websites</h2>
            <p>
                Pages here link outward to registries, documentation and other projects. Once you
                leave, their privacy policies apply — check them before you hand over anything.
            </p>

            <h2 class="h4 mt-4">Changes to this policy</h2>
            <p>
                When our practices change, this page changes with them and the date at the top is
                updated. Continuing to use the site after a change means you accept the policy as
                it then stands.
            </p>

            <h2 class="h4 mt-4">Contact and complaints</h2>
            <p>
                Exercise any right above, or ask anything about this policy, by email at
                <a href="mailto:<?= e(SITE_EMAIL) ?>"><?= e(SITE_EMAIL) ?></a>, by phone on
                <a href="tel:<?= e(SITE_PHONE) ?>"><?= e(SITE_PHONE) ?></a>, or through the
                <a href="<?= url('contact') ?>">contact page</a>.
            </p>
            <p>
                You may also complain to the Personal Data Protection Office at
                <a href="https://pdpo.go.ug/" rel="noopener">pdpo.go.ug</a>, or to the supervisory
                authority of the country you live in where one has jurisdiction over this site.
            </p>
        </div>
    </div>
</section>

<?php require __DIR__ . '/app/parts/footer.php'; ?>
