<?php
declare(strict_types=1);

/* The published terms, rewritten for HiWS as it stands: a free site run from
   Uganda, so the governing law is Uganda's rather than the borrowed wording
   the old copy carried. What is said about the material on the site is
   specific to it — the King James text is public domain, SiFM is MIT
   licensed — and the linking policy is condensed to what is actually used. */
require __DIR__ . '/app/init.php';

$title = 'Terms & Conditions';

require __DIR__ . '/app/parts/header.php';
?>

<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <h1 class="mb-2">Terms &amp; Conditions</h1>
            <p class="text-body-secondary small mb-4">Last updated 5 November 2026</p>

            <p>Welcome to <?= e(SITE_NAME) ?>.</p>

            <p>
                These terms govern your use of <a href="<?= url() ?>"><?= e(SITE_DOMAIN) ?></a> and
                its project subdomains. By using the site you accept them; if you do not, please
                stop using it. They sit alongside our
                <a href="<?= url('privacy') ?>">Privacy Policy</a> and our
                <a href="<?= url('disclaimer') ?>">Disclaimer</a>, which are part of the same
                agreement.
            </p>

            <p>
                <?= e(SITE_NAME) ?> is run by Hiswill Kato from Mityana, Uganda. In these terms,
                “we”, “us” and “our” mean <?= e(SITE_NAME) ?>, and “you” means whoever is using
                the site.
            </p>

            <h2 class="h4 mt-4">The service</h2>
            <p>
                <?= e(SITE_NAME) ?> offers free web tools, a Bible reader and a browser file
                manager, with no account to open and nothing to pay. We may add, change or retire
                a tool at any time, and we do not promise that any particular one will always be
                available.
            </p>

            <h2 class="h4 mt-4">Acceptable use</h2>
            <p>You may use the site for personal and ordinary business purposes. You must not:</p>
            <ul>
                <li>use it for anything unlawful, fraudulent or harmful, or to help someone else do so</li>
                <li>try to break into, overload or disrupt it — including hammering a tool with automated requests</li>
                <li>attempt unauthorised access to the machine, the database or other visitors' information</li>
                <li>upload or send malware, or use a tool to hide the origin of harmful material</li>
                <li>reproduce the site itself as a competing service, or pass our work off as your own</li>
                <li>use the content in a way that infringes someone else's rights</li>
            </ul>
            <p>
                Unauthorised access to a computer system is an offence under Uganda's
                Computer Misuse Act, 2011, and we will report it.
            </p>

            <h2 class="h4 mt-4">What you send us</h2>
            <p>
                Anything you write to us — through the contact form or by email — stays yours. You
                give us the permission needed to read it, answer it and keep a short record of the
                exchange, which is all we do with it.
            </p>

            <h2 class="h4 mt-4">Intellectual property</h2>
            <p>
                The design, the code and the written explanation of each tool on this site belong
                to <?= e(SITE_NAME) ?> unless a page says otherwise. You may read, use and link to
                them freely for your own purposes; you may not republish them as a service, sell
                them, or redistribute the site's own material as though it were yours.
            </p>
            <p>Parts of the site are deliberately free of any such claim:</p>
            <ul>
                <li>the Bible text is the <strong>King James Version</strong>, which is in the public domain</li>
                <li><strong>SiFM</strong> is distributed under the MIT licence at <a href="https://github.com/HiswillKato/SiFM" rel="noopener">github.com/HiswillKato/SiFM</a></li>
                <li>Bootstrap and the Font Awesome icons are used under their own open licences</li>
            </ul>
            <p>
                <?= e(SITE_NAME) ?> and its logos are ours; do not use them without written
                permission.
            </p>

            <h2 class="h4 mt-4">Cookies and advertising</h2>
            <p>
                We use only the small cookies described in the
                <a href="<?= url('privacy') ?>">Privacy Policy</a>. Advertisements, when shown, are
                served by Google AdSense under Google's own terms, and we do not choose or endorse
                what they promote. You can opt out of personalised advertising through the links in
                that policy.
            </p>

            <h2 class="h4 mt-4">External links</h2>
            <p>
                This site links to other websites — registries, documentation, projects we use.
                We are not responsible for their content, their availability or what they do with
                your data, and a link is not our endorsement of them.
            </p>

            <h2 class="h4 mt-4">Linking to us</h2>
            <p>You may link to our home page provided that you:</p>
            <ul>
                <li>do not present the link in a misleading or deceptive way</li>
                <li>do not imply that we sponsor, approve or endorse you or your product</li>
                <li>keep it within the context of a genuine, relevant page of yours</li>
            </ul>
            <p>
                Framing our pages inside yours, or using our name and logos as a link, needs our
                written permission first. Anything else, ask us — we usually say yes to good
                projects.
            </p>

            <h2 class="h4 mt-4">Disclaimers and liability</h2>
            <p>
                The site is provided as it is, free of charge and without warranties of any kind.
                We do not promise that it will be uninterrupted, error-free or suitable for a
                particular purpose, and we are not responsible for decisions you make on the
                strength of what you read here — see the
                <a href="<?= url('disclaimer') ?>">Disclaimer</a>.
            </p>
            <p>
                To the maximum extent the laws of Uganda allow, we are not liable for loss or
                damage arising from your use of the site. Nothing in these terms limits or
                excludes liability that the law does not permit us to limit — for death or personal
                injury caused by negligence, for fraud, or for anything else that may not lawfully
                be excluded.
            </p>

            <h2 class="h4 mt-4">Governing law</h2>
            <p>
                These terms are governed by the laws of <strong>Uganda</strong>, and the courts of
                Uganda have jurisdiction over any dispute arising from them or from your use of the
                site. If you live elsewhere, the protections of your own country's consumer and
                data-protection law still apply to you as a matter of that law.
            </p>

            <h2 class="h4 mt-4">Changes to these terms</h2>
            <p>
                We may update these terms as the site changes; the date at the top moves when we
                do. Using the site after an update means you accept the terms as they then stand.
            </p>

            <h2 class="h4 mt-4">Contact</h2>
            <p>
                Questions about these terms go to
                <a href="mailto:<?= e(SITE_EMAIL) ?>"><?= e(SITE_EMAIL) ?></a>, to
                <a href="tel:<?= e(SITE_PHONE) ?>"><?= e(SITE_PHONE) ?></a>, or through the
                <a href="<?= url('contact') ?>">contact page</a>.
            </p>
        </div>
    </div>
</section>

<?php require __DIR__ . '/app/parts/footer.php'; ?>
