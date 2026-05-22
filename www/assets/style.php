<?php

require __DIR__ . '/../vendor/autoload.php';

use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\OutputStyle;

$scssDir   = __DIR__ . '/scss';
$mainFile  = $scssDir . '/main.scss';
$cacheFile = sys_get_temp_dir() . '/smarty_style_' . md5($scssDir) . '.css';

// Находим самый свежий mtime среди всех .scss-файлов
$scssFiles   = glob($scssDir . '/*.scss') ?: [];
$latestMtime = 0;
foreach ($scssFiles as $f) {
    $latestMtime = max($latestMtime, filemtime($f));
}

$cacheValid = file_exists($cacheFile) && filemtime($cacheFile) >= $latestMtime;

if (!$cacheValid) {
    $compiler = new Compiler();
    $compiler->setImportPaths($scssDir);
    $compiler->setOutputStyle(OutputStyle::COMPRESSED);

    $css = $compiler->compileString(file_get_contents($mainFile))->getCss();
    file_put_contents($cacheFile, $css, LOCK_EX);
} else {
    $css = file_get_contents($cacheFile);
}

header('Content-Type: text/css; charset=utf-8');
header('Cache-Control: public, max-age=3600');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $latestMtime) . ' GMT');
echo $css;
