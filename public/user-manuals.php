<?php
/**
 * Cashew ERP - User Manual Entry Point
 *
 * Dynamically serves manual content from the /manuals/ directory.
 */

// Define allowed manual sections and topics to prevent directory traversal
$allowed_manuals = [
    'intro' => 'introduction/main.php',
    'transactions' => 'transactions/management.php',
    'budgets' => 'budgets/planning.php',
    'accounts' => 'accounts/wallets.php',
    'goals' => 'goals/tracking.php',
    'reports' => 'reports/analytics.php',
    'recurring' => 'recurring/payments.php',
    'sync' => 'sync-security/backup.php',
    'import' => 'import-export/data.php',
    'advanced' => 'advanced/automation.php',
];

$current_manual = isset($_GET['manual']) && array_key_exists($_GET['manual'], $allowed_manuals)
    ? $_GET['manual']
    : 'intro';

$content_file = __DIR__ . "/../manuals/" . $allowed_manuals[$current_manual];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashew User Manual - <?php echo ucfirst($current_manual); ?></title>
    <style>
        body { font-family: 'Inter', sans-serif; line-height: 1.6; color: #333; margin: 0; display: flex; }
        nav { width: 250px; background: #f4f4f4; height: 100vh; padding: 20px; border-right: 1px solid #ddd; position: sticky; top: 0; }
        nav ul { list-style: none; padding: 0; }
        nav li { margin-bottom: 10px; }
        nav a { text-decoration: none; color: #5f85c2; font-weight: bold; }
        nav a:hover { text-decoration: underline; }
        main { flex: 1; padding: 40px; max-width: 900px; margin: 0 auto; }
        .manual-container { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(10px); border-radius: 12px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h1, h2, h3 { color: #2c3e50; }
        .ui-callout { background: #eef2f7; border-left: 4px solid #5f85c2; padding: 10px; margin: 20px 0; font-style: italic; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <nav>
        <h3>Cashew Guides</h3>
        <ul>
            <?php foreach ($allowed_manuals as $key => $path): ?>
                <li><a href="?manual=<?php echo $key; ?>"><?php echo ucfirst($key); ?></a></li>
            <?php endforeach; ?>
        </ul>
    </nav>
    <main>
        <div class="manual-container">
            <?php
            if (file_exists($content_file)) {
                include $content_file;
            } else {
                echo "<h1>Coming Soon</h1><p>Documentation for this section is currently being updated.</p>";
            }
            ?>
        </div>
    </main>
</body>
</html>
