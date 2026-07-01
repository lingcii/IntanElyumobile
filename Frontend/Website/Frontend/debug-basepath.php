<?php
$frontendRootPath = strtolower(str_replace('\\', '/', dirname(__DIR__)));
$entryFileDir = strtolower(str_replace('\\', '/', dirname($_SERVER['SCRIPT_FILENAME'])));

$basePath = '';
if (str_starts_with($entryFileDir, $frontendRootPath)) {
    $relativePath = substr($entryFileDir, strlen($frontendRootPath));
    $depth = substr_count(trim($relativePath, '/'), '/');
    for ($i = 0; $i < $depth; $i++) {
        $basePath .= '../';
    }
}

echo "<h1>Debug Base Path</h1>";
echo "<p>Entry File Dir: " . htmlspecialchars($entryFileDir) . "</p>";
echo "<p>Frontend Root Path: " . htmlspecialchars($frontendRootPath) . "</p>";
echo "<p>Base Path: " . htmlspecialchars($basePath) . "</p>";
?>