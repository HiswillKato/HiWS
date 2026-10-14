<?php
declare(strict_types=1);

require __DIR__ . '/app/init.php';

$title = 'Disclaimer';

require __DIR__ . '/app/parts/header.php';
?>

<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <h1 class="mb-4">Disclaimer</h1>

            <p>
                If you need more information, or have any questions about this site's disclaimer,
                please contact us by email at
                <a href="mailto:<?= e(SITE_EMAIL) ?>"><?= e(SITE_EMAIL) ?></a>.
            </p>

            <h2 class="h4 mt-4">Disclaimers for <?= e(SITE_NAME) ?></h2>

            <p>
                All the information on this website — <a href="<?= url() ?>"><?= e(SITE_DOMAIN) ?></a> —
                is published in good faith and for general information purposes only.
                <?= e(SITE_NAME) ?> does not make any warranties about the completeness, reliability
                or accuracy of this information. Any action you take upon the information you find on
                this website is strictly at your own risk. <?= e(SITE_NAME) ?> will not be liable for
                any losses or damages in connection with the use of our website.
            </p>

            <p>
                From our website you can visit other websites by following links to such external
                sites. While we try to link only to useful and ethical websites, we have no control
                over their content and nature. Those links do not imply a recommendation for all the
                content found on those sites. Site owners and content may change without notice, and
                a link may go bad before we have a chance to remove it.
            </p>

            <p>
                Please be aware that when you leave our website, other sites may have different
                privacy policies and terms that are beyond our control. Check the privacy policy and
                the terms of service of those sites before doing any business or uploading any
                information.
            </p>

            <h2 class="h4 mt-4">Consent</h2>

            <p>By using our website, you consent to this disclaimer and agree to its terms.</p>

            <h2 class="h4 mt-4">Update</h2>

            <p>
                Should we update, amend or make any changes to this document, they will be posted
                prominently here.
            </p>
        </div>
    </div>
</section>

<?php require __DIR__ . '/app/parts/footer.php'; ?>
