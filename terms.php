<?php
declare(strict_types=1);

/* The published terms, carried over from the previous HiWS site. The text is
   the site's own legal copy, so it is reproduced rather than rewritten; only
   the name, the address and the link targets are read from config. */
require __DIR__ . '/app/init.php';

$title = 'Terms & Conditions';

require __DIR__ . '/app/parts/header.php';
?>

<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <h1 class="mb-4">Terms &amp; Conditions</h1>

            <p>Welcome to <?= e(SITE_NAME) ?>!</p>

            <p>
                These terms and conditions outline the rules and regulations for the use of
                <?= e(SITE_NAME) ?>'s website, located at
                <a href="<?= url() ?>"><?= e(SITE_DOMAIN) ?></a>.
            </p>

            <p>
                By accessing this website we assume you accept these terms and conditions. Do not
                continue to use <?= e(SITE_NAME) ?> if you do not agree with all of the terms stated
                on this page.
            </p>

            <p>
                The following terminology applies to these Terms and Conditions, the Privacy
                Statement and Disclaimer Notice and all Agreements: "Client", "You" and "Your" refer
                to you, the person who logs on this website and complies with the Company's terms and
                conditions. "The Company", "Ourselves", "We", "Our" and "Us" refer to our Company.
                "Party", "Parties" or "Us" refer to both the Client and ourselves. All terms refer to
                the offer, acceptance and consideration of payment necessary to undertake the process
                of our assistance to the Client in the most appropriate manner, for the express
                purpose of meeting the Client's needs in respect of the provision of the Company's
                stated services, in accordance with, and subject to, the prevailing law of the
                Netherlands. Any use of the above terminology in the singular, plural, capitalisation
                and/or he, she or they is taken as interchangeable and therefore as referring to the
                same.
            </p>

            <h2 class="h4 mt-4">Cookies</h2>
            <p>
                We use cookies. By accessing <?= e(SITE_NAME) ?>, you agree to the use of cookies in
                agreement with the <?= e(SITE_NAME) ?> Privacy Policy.
            </p>
            <p>
                Most interactive websites use cookies to let us retrieve the user's details on each
                visit. Cookies enable the functionality of certain areas of our website, which makes
                it easier for visitors. Some of our affiliate or advertising partners may also use
                cookies.
            </p>

            <h2 class="h4 mt-4">Licence</h2>
            <p>
                Unless otherwise stated, <?= e(SITE_NAME) ?> and/or its licensors own the intellectual
                property rights for all material on <?= e(SITE_NAME) ?>. All intellectual property
                rights are reserved. You may access this material from <?= e(SITE_NAME) ?> for your own
                personal use, subject to the restrictions set in these terms and conditions.
            </p>
            <p>You must not:</p>
            <ul>
                <li>Republish material from <?= e(SITE_NAME) ?></li>
                <li>Sell, rent or sub-licence material from <?= e(SITE_NAME) ?></li>
                <li>Reproduce, duplicate or copy material from <?= e(SITE_NAME) ?></li>
                <li>Redistribute content from <?= e(SITE_NAME) ?></li>
            </ul>
            <p>This Agreement begins on the date it is first accepted.</p>

            <h2 class="h4 mt-4">Hyperlinking to our content</h2>
            <p>The following organisations may link to our website without prior written approval:</p>
            <ul>
                <li>Government agencies</li>
                <li>Search engines</li>
                <li>News organisations</li>
                <li>Online directory distributors, in the same manner as they hyperlink to the websites of other listed businesses</li>
                <li>System-wide accredited businesses, except soliciting non-profit organisations, charity shopping malls and charity fundraising groups, which may not hyperlink to our website</li>
            </ul>
            <p>
                These organisations may link to our home page, to publications or to other website
                information, so long as the link: (a) is not deceptive in any way; (b) does not
                falsely imply sponsorship, endorsement or approval of the linking party and its
                products or services; and (c) fits the context of the linking party's site.
            </p>
            <p>We may consider and approve other link requests from these types of organisations:</p>
            <ul>
                <li>Commonly known consumer and/or business information sources</li>
                <li>Dot-com community sites</li>
                <li>Associations or other groups representing charities</li>
                <li>Online directory distributors</li>
                <li>Internet portals</li>
                <li>Accounting, law and consulting firms</li>
                <li>Educational institutions and trade associations</li>
            </ul>
            <p>
                We will approve link requests from these organisations if we decide that: (a) the link
                would not make us look unfavourably to ourselves or to our accredited businesses;
                (b) the organisation has no negative records with us; (c) the benefit to us from the
                visibility of the hyperlink compensates the absence of <?= e(SITE_NAME) ?>; and
                (d) the link is in the context of general resource information.
            </p>
            <p>
                These organisations may link to our home page so long as the link: (a) is not
                deceptive in any way; (b) does not falsely imply sponsorship, endorsement or approval
                of the linking party and its products or services; and (c) fits the context of the
                linking party's site.
            </p>
            <p>
                If you are one of the organisations listed above and want to link to our website,
                inform us by email. Include your name, your organisation's name, contact information,
                the URL of your site, a list of the URLs from which you intend to link to our website,
                and a list of the URLs on our site you would like to link to. Allow two to three weeks
                for a response.
            </p>
            <p>Approved organisations may hyperlink to our website:</p>
            <ul>
                <li>By use of our corporate name, or</li>
                <li>By use of the uniform resource locator being linked to, or</li>
                <li>By use of any other description of our website that makes sense within the context and format of the linking party's site</li>
            </ul>
            <p>
                No use of <?= e(SITE_NAME) ?>'s logo or other artwork is allowed for linking without a
                trademark licence agreement.
            </p>

            <h2 class="h4 mt-4">iFrames</h2>
            <p>
                Without prior approval and written permission, you may not create frames around our
                webpages that alter the visual presentation or appearance of our website in any way.
            </p>

            <h2 class="h4 mt-4">Content liability</h2>
            <p>
                We are not responsible for any content that appears on your website. You agree to
                protect and defend us against all claims arising from your website. No link should
                appear on any website that may be interpreted as libellous, obscene or criminal, or
                which infringes, otherwise violates, or advocates the infringement or other violation
                of, any third-party rights.
            </p>

            <h2 class="h4 mt-4">Your privacy</h2>
            <p>Please read our <a href="<?= url('privacy') ?>">Privacy Policy</a>.</p>

            <h2 class="h4 mt-4">Reservation of rights</h2>
            <p>
                We reserve the right to request that you remove all links, or any particular link, to
                our website. You agree to remove all links to our website immediately on request. We
                also reserve the right to amend these terms and conditions and this linking policy at
                any time. By continuing to link to our website, you agree to be bound by, and to
                follow, these linking terms and conditions.
            </p>

            <h2 class="h4 mt-4">Removal of links from our website</h2>
            <p>
                If you find any link on our website that is offensive for any reason, you are free to
                contact us at any time. We will consider requests to remove links, but we are not
                obliged to do so, or to respond to you directly.
            </p>
            <p>
                We do not ensure that the information on this website is correct, we do not warrant
                its completeness or accuracy, and we do not promise that the website remains available
                or that the material on it is kept up to date.
            </p>

            <h2 class="h4 mt-4">Disclaimer</h2>
            <p>
                To the maximum extent permitted by applicable law, we exclude all representations,
                warranties and conditions relating to our website and its use. Nothing in this
                disclaimer will:
            </p>
            <ul>
                <li>Limit or exclude our or your liability for death or personal injury</li>
                <li>Limit or exclude our or your liability for fraud or fraudulent misrepresentation</li>
                <li>Limit any of our or your liabilities in any way that is not permitted under applicable law</li>
                <li>Exclude any of our or your liabilities that may not be excluded under applicable law</li>
            </ul>
            <p>
                The limitations and prohibitions of liability set in this section and elsewhere in
                this disclaimer are subject to the preceding paragraph, and govern all liabilities
                arising under the disclaimer, including liabilities arising in contract, in tort and
                for breach of statutory duty.
            </p>
            <p>
                As long as the website and the information and services on it are provided free of
                charge, we will not be liable for any loss or damage of any nature.
            </p>
        </div>
    </div>
</section>

<?php require __DIR__ . '/app/parts/footer.php'; ?>
