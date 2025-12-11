<?php
// TEMPORARY FIX - Hapus setelah masalah solved
// Akses file ini untuk clear semua cache

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear'])) {
    try {
        // Clear route cache
        $routeCacheFile = __DIR__ . '/../bootstrap/cache/routes-v7.php';
        if (file_exists($routeCacheFile)) {
            unlink($routeCacheFile);
            echo "✓ Route cache cleared<br>";
        }

        // Clear config cache
        $configCacheFile = __DIR__ . '/../bootstrap/cache/config.php';
        if (file_exists($configCacheFile)) {
            unlink($configCacheFile);
            echo "✓ Config cache cleared<br>";
        }

        // Clear compiled views
        $viewsPath = __DIR__ . '/../storage/framework/views';
        if (is_dir($viewsPath)) {
            $files = glob($viewsPath . '/*.php');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            echo "✓ View cache cleared (" . count($files) . " files)<br>";
        }

        // Clear application cache
        $cachePath = __DIR__ . '/../storage/framework/cache/data';
        if (is_dir($cachePath)) {
            $dirs = glob($cachePath . '/*');
            $fileCount = 0;
            foreach ($dirs as $dir) {
                if (is_dir($dir)) {
                    $files = glob($dir . '/*');
                    foreach ($files as $file) {
                        if (is_file($file)) {
                            unlink($file);
                            $fileCount++;
                        }
                    }
                }
            }
            echo "✓ Application cache cleared (" . $fileCount . " files)<br>";
        }

        echo "<br><strong>All caches cleared! Test pembelian sekarang.</strong>";

    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage();
    }
    exit;
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Clear All Cache</title>
    <style>
        body {
            font-family: Arial;
            padding: 50px;
        }

        button {
            padding: 15px 30px;
            font-size: 16px;
            background: #dc3545;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        button:hover {
            background: #c82333;
        }
    </style>
</head>

<body>
    <h2>⚠️ Clear All Laravel Cache</h2>
    <p>Klik tombol di bawah untuk clear semua cache Laravel.</p>
    <form method="POST">
        <button type="submit" name="clear" value="1">Clear All Cache</button>
    </form>
    <p><small>HAPUS FILE INI SETELAH SELESAI!</small></p>
</body>

</html>