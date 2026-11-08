<?php
declare(strict_types=1);

require __DIR__ . '/../app/init.php';

$tool = tool('gitignore-generator');

$templates = [
    'node' => ['Node.js', "# Node.js\nnode_modules/\nnpm-debug.log*\nyarn-debug.log*\nyarn-error.log*\n.pnpm-store/\ndist/\nbuild/\n.env\n.env.*\n!.env.example"],
    'php' => ['PHP / Composer', "# PHP / Composer\n/vendor/\ncomposer.phar\n.env\n.env.*\n!.env.example\n.phpunit.result.cache"],
    'python' => ['Python', "# Python\n__pycache__/\n*.py[cod]\n*.egg-info/\n.eggs/\n.venv/\nvenv/\nENV/\n.pytest_cache/\n.mypy_cache/\ndist/\nbuild/"],
    'java' => ['Java / Maven / Gradle', "# Java\n*.class\n*.jar\n*.war\ntarget/\nbuild/\n.gradle/\n.gradle-cache/\n.idea/\n*.iml"],
    'go' => ['Go', "# Go\n/bin/\n/pkg/\n*.exe\n*.test\n*.out\nvendor/"],
    'rust' => ['Rust', "# Rust\n/target/\n**/*.rs.bk\nCargo.lock"],
    'ruby' => ['Ruby', "# Ruby\n/.bundle/\n/vendor/bundle/\n*.gem\n.ruby-version\n.byebug_history"],
    'dotnet' => ['.NET', "# .NET\nbin/\nobj/\n*.user\n*.suo\n.vs/\npackages/\n*.nupkg"],
    'macos' => ['macOS', "# macOS\n.DS_Store\n.AppleDouble\n.LSOverride\n._*"],
    'windows' => ['Windows', "# Windows\nThumbs.db\nehthumbs.db\nDesktop.ini\n$RECYCLE.BIN/"],
    'linux' => ['Linux', "# Linux\n*~\n.fuse_hidden*\n.directory\n.Trash-*"],
    'vscode' => ['VS Code', "# VS Code\n.vscode/*\n!.vscode/settings.json\n!.vscode/tasks.json\n!.vscode/launch.json\n!.vscode/extensions.json"],
    'jetbrains' => ['JetBrains IDEs', "# JetBrains\n.idea/\n*.iml\nout/\n*.iws"],
    'vim' => ['Vim', "# Vim\n[._]*.s[a-v][a-z]\n[._]*.sw[a-p]\n[._]s[a-rt-v][a-z]\n[._]ss[a-gi-z]\n*~\nSession.vim"],
];

$picked = isset($_POST['tpl']) && is_array($_POST['tpl']) ? array_values(array_intersect(array_keys($templates), array_map('strval', $_POST['tpl']))) : [];
$extra = posted('extra');
$output = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $parts = ['# Generated .gitignore'];
    foreach ($picked as $key) {
        $parts[] = '';
        $parts[] = $templates[$key][1];
    }
    $extra = trim(str_replace(["\r\n", "\r"], "\n", $extra));
    if ($extra !== '') {
        $parts[] = '';
        $parts[] = "# Custom";
        $parts[] = $extra;
    }
    if (count($parts) === 1) {
        $parts[] = '';
        $parts[] = '# Select templates above, or add custom rules below.';
    }
    $output = implode("\n", $parts) . "\n";
}

require __DIR__ . '/../app/parts/tool.php';
?>

<form method="post" class="card bg-body rounded-4 border shadow-sm p-4">
    <span class="form-label fw-semibold d-block">Templates</span>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-1 mb-3">
        <?php foreach ($templates as $key => [$label, $body]): ?>
            <div class="col">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="tpl[]" value="<?= e($key) ?>" id="tpl-<?= e($key) ?>"<?= in_array($key, $picked, true) ? ' checked' : '' ?>>
                    <label class="form-check-label" for="tpl-<?= e($key) ?>"><?= e($label) ?></label>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <label class="form-label fw-semibold" for="extra">Custom rules (optional)</label>
    <textarea class="form-control font-monospace" id="extra" name="extra" rows="5" placeholder="secrets/*.key"><?= e($extra) ?></textarea>
    <button class="btn btn-primary mt-3" type="submit">Generate .gitignore</button>
</form>

<?php if ($output !== ''): ?>
    <?= output($output, '.gitignore', true) ?>
<?php endif; ?>

<?php require __DIR__ . '/../app/parts/explain.php'; ?>
