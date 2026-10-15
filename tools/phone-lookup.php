<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberUtil;

$tool = tool('phone-lookup');

/* What each kind of number is called. The library answers with a number, and
   these are the names it gives them. */
const KINDS = [
    PhoneNumberType::FIXED_LINE           => 'Fixed line',
    PhoneNumberType::MOBILE               => 'Mobile',
    PhoneNumberType::FIXED_LINE_OR_MOBILE => 'Fixed line or mobile',
    PhoneNumberType::TOLL_FREE            => 'Toll free',
    PhoneNumberType::PREMIUM_RATE         => 'Premium rate',
    PhoneNumberType::SHARED_COST          => 'Shared cost',
    PhoneNumberType::VOIP                 => 'Internet calling',
    PhoneNumberType::PERSONAL_NUMBER      => 'Personal number',
    PhoneNumberType::PAGER                => 'Pager',
    PhoneNumberType::UAN                  => 'Company number',
    PhoneNumberType::VOICEMAIL            => 'Voicemail',
];

/* The name of a country from its two-letter code. PHP's intl knows them all;
   the code still names the country when it does not, or on a build without
   intl. */
function country(string $code): string
{
    $name = class_exists('Locale') ? (string) Locale::getDisplayRegion('-' . $code, 'en') : '';

    return $name === '' ? $code : $name;
}

$number = posted('number');
$home = strtoupper(posted('region'));

$util = PhoneNumberUtil::getInstance();

/* A flat list of the region codes the library carries a plan for, which is
   every country that numbers its phones in a way it can read. */
$regions = $util->getSupportedRegions();
sort($regions);

$details = [];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (trim($number) === '') {
        $error = 'Enter a phone number.';
    } else {
        try {
            /* The country is only needed for a number written the local way;
               one that starts with + carries its own. */
            $parsed = $util->parse($number, $home === '' ? null : $home);

            if (!$util->isValidNumber($parsed)) {
                $error = 'That is not a valid number'
                    . ($home === '' ? '. Add the country code, as in +256…' : ' for ' . country($home) . '.');
            } else {
                $region = (string) $util->getRegionCodeForNumber($parsed);
                $kind = $util->getNumberType($parsed);

                $details = [
                    'Country'        => country($region),
                    'Country code'   => '+' . $parsed->getCountryCode(),
                    'Number'         => (string) $parsed->getNationalNumber(),
                    'Kind'           => KINDS[$kind] ?? 'Unknown',
                    'E.164'          => $util->format($parsed, PhoneNumberFormat::E164),
                    'International'  => $util->format($parsed, PhoneNumberFormat::INTERNATIONAL),
                    'National'       => $util->format($parsed, PhoneNumberFormat::NATIONAL),
                    'Dial from a phone' => $util->format($parsed, PhoneNumberFormat::RFC3966),
                ];
            }
        } catch (NumberParseException $problem) {
            /* The library's own words for a number with no country code and no
               country chosen are about a *default region*, which is not what a
               visitor set out to do — so say what to do instead. */
            $error = $problem->getErrorType() === NumberParseException::INVALID_COUNTRY_CODE
                ? 'Add the country code at the front, as in +256…, or choose the country it was written in.'
                : $problem->getMessage();
        }
    }
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <div class="row g-3 align-items-end">
        <div class="col-md-6">
            <label class="form-label fw-semibold" for="number">Phone number</label>
            <input class="form-control" id="number" name="number" value="<?= e($number) ?>"
                   placeholder="+256 772 123456" required autofocus>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-semibold" for="region">Country it was written in</label>
            <select class="form-select" id="region" name="region">
                <option value="">It carries its own country code</option>
                <?php foreach ($regions as $code): ?>
                    <option value="<?= e($code) ?>" <?= $code === $home ? 'selected' : '' ?>>
                        <?= e($code . ' — ' . country($code)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <button class="btn btn-primary mt-4" type="submit">Look up</button>
</form>

<?php if ($error !== ''): ?>
    <?= notice($error) ?>
<?php elseif ($details !== []): ?>
    <div class="card bg-body rounded-4 border shadow-sm mt-4">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <tbody>
                    <?php foreach ($details as $label => $value): ?>
                        <tr>
                            <th scope="row" class="text-body-secondary fw-normal w-50"><?= e($label) ?></th>
                            <td><code><?= e((string) $value) ?></code></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <p class="text-body-secondary small mt-3 mb-0">
        Numbering plans come from Google's libphonenumber, the same data Android
        uses to dial. This tool reads a number; it cannot say who owns it.
    </p>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php';
