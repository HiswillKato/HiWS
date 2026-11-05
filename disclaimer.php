<?php
declare(strict_types=1);

/* The published disclaimer, rewritten for the site as it is: a Ugandan one-person
   project whose tools read live networks and show Google advertisements, so it
   says plainly what the tools cannot promise and who stands behind the ads. */
require __DIR__ . '/app/init.php';

$title = 'Disclaimer';

require __DIR__ . '/app/parts/header.php';
?>

<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <h1 class="mb-2">Disclaimer</h1>
            <p class="text-body-secondary small mb-4">Last updated 5 November 2026</p>

            <p>
                Everything on <a href="<?= url() ?>"><?= e(SITE_DOMAIN) ?></a> is published in good
                faith and for general information. It is offered free of charge, and you act on it
                at your own risk. If you need anything more than this page offers, or have a
                question about it, write to
                <a href="mailto:<?= e(SITE_EMAIL) ?>"><?= e(SITE_EMAIL) ?></a>.
            </p>

            <h2 class="h4 mt-4">Not professional advice</h2>
            <p>
                The tools here are calculators and lookups, not experts. A body-mass index, an age
                or a percentage works out the arithmetic you give it and nothing more — it is not a
                diagnosis, not medical advice, and not a substitute for a clinician who knows your
                history. Bible text and study pages are there for reading and research, not for
                legal, medical or financial decisions. When a decision matters, consult the
                professional whose job it is.
            </p>

            <h2 class="h4 mt-4">Tools that read live networks</h2>
            <p>
                DNS, WHOIS, SSL, HTTP and IP lookups ask other machines on the Internet, in real
                time, and report whatever comes back. Records change, registries differ in what
                they publish, and a result can be incomplete or out of date by the time you read
                it. We do not warrant that any lookup is complete, accurate or current.
            </p>

            <h2 class="h4 mt-4">Advertising</h2>
            <p>
                <?= e(SITE_NAME) ?> shows advertisements through <strong>Google AdSense</strong>.
                The advertisements are selected and delivered by Google from the advertisers
                bidding on the space — not written, chosen or checked by us. Their appearance here
                is not a recommendation, an endorsement or a promise of anything, and <?= e(SITE_NAME) ?>
                is not responsible for the offers, claims or products an advertisement makes, nor
                for what happens once you follow its link.
            </p>
            <p>
                How Google uses cookies to serve those advertisements, and how you can opt out of
                personalised advertising, is set out in our
                <a href="<?= url('privacy') ?>">Privacy Policy</a>.
            </p>

            <h2 class="h4 mt-4">External links</h2>
            <p>
                From here you can reach other websites. We link to what we judge useful and
                reputable, but we do not control those sites: their content can change, a link can
                go stale before we notice, and following one takes you out of our care and under
                theirs. Check a site's own terms and privacy policy before doing anything with it
                that costs money or shares information.
            </p>

            <h2 class="h4 mt-4">Availability</h2>
            <p>
                We keep the site running where we can, but we do not promise uninterrupted access.
                Maintenance, a fault on the network or a change made by a third party can take a
                page down without notice, and we are not liable for it being unavailable.
            </p>

            <h2 class="h4 mt-4">Consent</h2>
            <p>By using this website you consent to this disclaimer and to its terms.</p>

            <h2 class="h4 mt-4">Updates</h2>
            <p>
                Anything we update here is posted on this page, with the date at the top moved to
                show when it last changed. Our terms and privacy policy are updated the same way:
                <a href="<?= url('terms') ?>">Terms &amp; Conditions</a> ·
                <a href="<?= url('privacy') ?>">Privacy Policy</a>.
            </p>

            <p class="text-body-secondary small mt-4 mb-0">
                Nothing on this page limits rights you hold under the laws of Uganda or of the
                country you live in, including the Data Protection and Privacy Act, 2019.
            </p>
        </div>
    </div>
</section>

<?php require __DIR__ . '/app/parts/footer.php'; ?>
