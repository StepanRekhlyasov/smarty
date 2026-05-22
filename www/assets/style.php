<?php

require __DIR__ . '/../vendor/autoload.php';

use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\OutputStyle;

$scssDir  = __DIR__ . '/scss';
$mainFile = $scssDir . '/main.scss';

$compiler = new Compiler();
$compiler->setImportPaths($scssDir);
$compiler->setOutputStyle(OutputStyle::COMPRESSED);

$css = $compiler->compileString(file_get_contents($mainFile))->getCss();

header('Content-Type: text/css; charset=utf-8');
header('Cache-Control: no-cache');
echo $css;
