<?php
declare(strict_types=1);

require __DIR__ . '/app/init.php';

$title = 'Contact Us';

/* The form posts to itself. Nothing is written to the database — a message is
   handed to the mail server on this machine and nowhere else — so the only
   state to keep is what the visitor typed, so a mistake does not wipe it. */
$sent   = false;
$errors = [];
$form   = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    foreach ($form as $field => $_) {
        $form[$field] = trim((string) ($_POST[$field] ?? ''));
    }

    if (!token_ok($_POST['token'] ?? null)) {
        $errors[] = 'That form had expired. Please send it again.';
    }

    if ($form['name'] === '') {
        $errors[] = 'Enter your name.';
    }

    if ($form['subject'] === '') {
        $errors[] = 'Enter the subject of the message.';
    }

    if ($form['message'] === '') {
        $errors[] = 'Enter the message you want to send.';
    }

    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'That email address does not look right. Check it and try again.';
    }

    if ($errors === []) {
        /* Every header is built from a value we produced or validated, never
           from a raw field, so a newline in the email address cannot smuggle
           in an extra header. Reply-To is the only value that comes from the
           visitor, and it has just passed FILTER_VALIDATE_EMAIL. */
        $headers = implode("\r\n", [
            'From: ' . MAIL_FROM,
            'Reply-To: ' . $form['email'],
            'Content-Type: text/plain; charset=UTF-8',
        ]);

        $body = wordwrap($form['message'] . "\n\n— " . $form['name'], 78);

        $sent = @mail(MAIL_ADMIN, MAIL_SUBJECT_PREFIX . $form['subject'], $body, $headers);
    }
}

require __DIR__ . '/app/parts/header.php';
?>

<section class="container py-5">
    <div class="row g-5">
        <div class="col-lg-7">
            <h1 class="mb-2">Contact Us</h1>
            <p class="text-body-secondary mb-4">
                Fill in the form and I will reply as soon as I can.
            </p>

            <?php if ($sent): ?>
                <div class="alert alert-success" role="alert">
                    <?= icon('fa-solid fa-circle-check me-1') ?>
                    Thank you — your message is on its way. I will get back to you soon.
                </div>
            <?php elseif ($errors !== []): ?>
                <div class="alert alert-danger" role="alert">
                    <?= icon('fa-solid fa-triangle-exclamation me-1') ?>
                    <strong>Your message was not sent:</strong>
                    <ul class="mb-0 mt-2">
                        <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!$sent): ?>
            <form method="post" novalidate>
                <?= token_field() ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="name">Name</label>
                        <input class="form-control" id="name" name="name" placeholder="John Doe…"
                               value="<?= e($form['name']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="email">Email</label>
                        <input class="form-control" id="email" name="email" type="email"
                               placeholder="example@example.com" value="<?= e($form['email']) ?>" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="subject">Subject</label>
                        <input class="form-control" id="subject" name="subject" placeholder="Subject…"
                               value="<?= e($form['subject']) ?>" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="message">Message</label>
                        <textarea class="form-control" id="message" name="message" rows="6"
                                  placeholder="Type your message here…" required><?= e($form['message']) ?></textarea>
                    </div>
                </div>

                <button class="btn btn-primary mt-4" type="submit">
                    <?= icon('fa-solid fa-paper-plane me-1') ?> Send message
                </button>
            </form>
            <?php endif; ?>
        </div>

        <div class="col-lg-5">
            <h2 class="h5 mb-3">Contacts</h2>
            <ul class="list-group shadow-sm">
                <li class="list-group-item d-flex align-items-center gap-3">
                    <?= icon('fa-solid fa-phone text-primary') ?>
                    <a class="link-body-emphasis text-decoration-none" href="tel:<?= e(str_replace(' ', '', SITE_PHONE)) ?>">
                        <?= e(SITE_PHONE) ?>
                    </a>
                </li>
                <li class="list-group-item d-flex align-items-center gap-3">
                    <?= icon('fa-brands fa-whatsapp text-success') ?>
                    <a class="link-body-emphasis text-decoration-none" href="https://wa.me/<?= e(SITE_WHATSAPP) ?>" rel="noopener">
                        <?= e(SITE_PHONE) ?>
                    </a>
                </li>
                <li class="list-group-item d-flex align-items-center gap-3">
                    <?= icon('fa-solid fa-envelope text-primary') ?>
                    <a class="link-body-emphasis text-decoration-none" href="mailto:<?= e(SITE_EMAIL) ?>">
                        <?= e(SITE_EMAIL) ?>
                    </a>
                </li>
                <li class="list-group-item d-flex align-items-center gap-3">
                    <?= icon('fa-solid fa-location-dot text-primary') ?>
                    <span><?= e(SITE_LOCATION) ?></span>
                </li>
            </ul>
        </div>
    </div>
</section>

<?php require __DIR__ . '/app/parts/footer.php'; ?>
