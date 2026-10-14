<?php
declare(strict_types=1);

/* The published privacy policy, carried over from the previous HiWS site with
   the name and the address read from config instead of hard-coded. */
require __DIR__ . '/app/init.php';

$title = 'Privacy Policy';

require __DIR__ . '/app/parts/header.php';
?>

<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <h1 class="mb-4">Privacy Policy</h1>

            <p>
                At <?= e(SITE_NAME) ?>, reachable at <a href="<?= url() ?>"><?= e(SITE_DOMAIN) ?></a>,
                one of our main priorities is the privacy of our visitors. This document explains the
                kinds of information <?= e(SITE_NAME) ?> collects and records, and how we use it.
            </p>

            <p>If you have questions or need more information about our Privacy Policy, contact us.</p>

            <p>
                This policy applies only to our online activities and covers what visitors share and
                what we record on this website. It does not apply to information collected offline or
                through any channel other than this website.
            </p>

            <h2 class="h4 mt-4">Consent</h2>
            <p>By using our website, you consent to this Privacy Policy and agree to its terms.</p>

            <h2 class="h4 mt-4">Information we collect</h2>
            <p>
                The personal information you are asked to provide, and the reason you are asked for
                it, is made clear at the point we ask.
            </p>
            <p>
                If you contact us directly, we may receive your name, email address, phone number,
                the contents of the message and any attachments, and anything else you choose to
                send.
            </p>
            <p>
                If accounts open on this website later, we may ask for contact details such as your
                name, company, address, email address and telephone number.
            </p>

            <h2 class="h4 mt-4">How we use your information</h2>
            <p>We use the information we collect to:</p>
            <ul>
                <li>Provide, operate and maintain our website</li>
                <li>Improve, personalise and expand our website</li>
                <li>Understand and analyse how you use our website</li>
                <li>Develop new products, services, features and functionality</li>
                <li>Communicate with you, directly or through a partner, for support, updates and related information</li>
                <li>Send you emails</li>
                <li>Find and prevent fraud</li>
            </ul>

            <h2 class="h4 mt-4">Log files</h2>
            <p>
                <?= e(SITE_NAME) ?> follows the standard practice of using log files. These record
                visitors when they visit websites — every hosting company does this, and it is part
                of hosting analytics. The information in the logs includes IP addresses, the browser
                type, the internet service provider, the date and time, the referring and exit pages,
                and possibly the number of clicks. None of it is linked to personally identifiable
                information. It is used to analyse trends, administer the site, track movement on the
                site, and gather demographic information.
            </p>

            <h2 class="h4 mt-4">Cookies and web beacons</h2>
            <p>
                Like any other website, <?= e(SITE_NAME) ?> uses cookies. They store information
                such as your preferences, and the pages you visited. The information is used to
                improve your experience by tailoring our page content to your browser type and other
                information.
            </p>

            <h2 class="h4 mt-4">Advertising</h2>
            <p>
                Some advertisers on our site may use cookies and web beacons. Our advertising
                partners are listed below, and each has its own privacy policy covering how it handles
                user data. For convenience, the policies are linked.
            </p>
            <ul>
                <li>Google: <a href="https://policies.google.com/technologies/ads" rel="noopener">policies.google.com/technologies/ads</a></li>
            </ul>
            <p>
                Third-party ad servers or ad networks use technologies such as cookies, JavaScript or
                web beacons in their advertisements and in links that appear on <?= e(SITE_NAME) ?>,
                which are sent directly to your browser. They receive your IP address automatically
                when this happens. These technologies measure the effectiveness of advertising
                campaigns and personalise the advertising you see on the websites you visit. Note that
                <?= e(SITE_NAME) ?> has no access to, or control over, cookies used by third-party
                advertisers.
            </p>

            <h2 class="h4 mt-4">Third-party privacy policies</h2>
            <p>
                <?= e(SITE_NAME) ?>'s Privacy Policy does not apply to other advertisers or websites.
                Please consult the privacy policies of those third-party ad servers for more detail.
                They may include their practices and instructions on how to opt out of certain
                options.
            </p>
            <p>
                You can disable cookies in your browser's settings. For more detail on cookie
                management in a particular browser, look at that browser's own documentation.
            </p>

            <h2 class="h4 mt-4">CCPA privacy rights (do not sell my personal information)</h2>
            <p>Under the CCPA, among other rights, California consumers have the right to:</p>
            <p>
                Ask a business that collects personal data to disclose the categories and the specific
                pieces of personal data it has collected about them.
            </p>
            <p>Ask a business to delete any personal data it has collected about them.</p>
            <p>Ask a business that sells personal data not to sell it.</p>
            <p>
                If you make a request, we have one month to respond. To exercise any of these rights,
                please contact us.
            </p>

            <h2 class="h4 mt-4">GDPR data protection rights</h2>
            <p>Every user is entitled to the following:</p>
            <p>
                <strong>The right to access</strong> — you may request copies of your personal data.
                We may charge a small fee for this service.
            </p>
            <p>
                <strong>The right to rectification</strong> — you may ask us to correct information
                you believe is inaccurate, and to complete information you believe is incomplete.
            </p>
            <p>
                <strong>The right to erasure</strong> — you may ask us to erase your personal data,
                under certain conditions.
            </p>
            <p>
                <strong>The right to restrict processing</strong> — you may ask us to restrict the
                processing of your personal data, under certain conditions.
            </p>
            <p>
                <strong>The right to object to processing</strong> — you may object to our processing
                of your personal data, under certain conditions.
            </p>
            <p>
                <strong>The right to data portability</strong> — you may ask us to transfer the data
                we have collected to another organisation, or to you, under certain conditions.
            </p>
            <p>
                If you make a request, we have one month to respond. To exercise any of these rights,
                please contact us.
            </p>

            <h2 class="h4 mt-4">Children's information</h2>
            <p>
                Protecting children online is another priority. We encourage parents and guardians to
                observe, take part in, and monitor and guide their children's online activity.
            </p>
            <p>
                <?= e(SITE_NAME) ?> does not knowingly collect personally identifiable information
                from children under 13. If you believe your child provided such information on this
                website, contact us immediately and we will remove it from our records.
            </p>
        </div>
    </div>
</section>

<?php require __DIR__ . '/app/parts/footer.php'; ?>
