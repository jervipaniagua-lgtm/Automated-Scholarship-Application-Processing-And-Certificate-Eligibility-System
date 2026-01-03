<?php
/**
 * Setup Script - Run this once to create required directories
 * Access via: http://localhost/Scholarship/setup.php
 */

$directories = [
    'uploads',
    'uploads/applications',
    'uploads/certificates'
];

$results = [];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0777, true)) {
            $results[] = "✅ Created: $dir";
        } else {
            $results[] = "❌ Failed to create: $dir";
        }
    } else {
        $results[] = "ℹ️ Already exists: $dir";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - Scholarship System</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f6f8; padding: 40px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,.1); }
        h1 { color: #2c3e50; margin-bottom: 20px; }
        .result { padding: 12px; margin: 8px 0; border-radius: 8px; font-family: monospace; }
        .success { background: #e6fffb; color: #08979c; border: 1px solid #87e8de; }
        .error { background: #fff1f0; color: #cf1322; border: 1px solid #ffccc7; }
        .info { background: #e6f7ff; color: #0958d9; border: 1px solid #91d5ff; }
        .btn { display: inline-block; margin-top: 20px; padding: 12px 24px; background: #3498db; color: white; text-decoration: none; border-radius: 8px; font-weight: bold; }
        .btn:hover { background: #2980b9; }
        .note { margin-top: 20px; padding: 12px; background: #fff7e6; border: 1px solid #ffd591; border-radius: 8px; color: #d48806; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📁 Directory Setup</h1>
        <p>Creating required directories for file uploads...</p>
        
        <?php foreach ($results as $result): ?>
            <?php
                $class = 'info';
                if (strpos($result, '✅') !== false) $class = 'success';
                if (strpos($result, '❌') !== false) $class = 'error';
            ?>
            <div class="result <?= $class ?>"><?= $result ?></div>
        <?php endforeach; ?>
        
        <div class="note">
            <strong>⚠️ Next Steps:</strong>
            <ol style="margin: 10px 0 0 20px; line-height: 1.8;">
                <li>Run <code>database_updates.sql</code> in phpMyAdmin</li>
                <li>Verify folders have write permissions</li>
                <li>Delete this <code>setup.php</code> file for security</li>
            </ol>
        </div>
        
        <a href="index.php" class="btn">Go to Homepage</a>
        <a href="register.php" class="btn" style="background: #52c41a;">Register Student</a>
    </div>
</body>
</html>
