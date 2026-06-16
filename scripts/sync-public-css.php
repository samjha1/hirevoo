<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$files = [
    'hirevo-candidate.css',
];

$sourceDir = $root.'/resources/css';
$targetDir = $root.'/public/css';

if (! is_dir($targetDir) && ! mkdir($targetDir, 0755, true) && ! is_dir($targetDir)) {
    fwrite(STDERR, "Unable to create {$targetDir}\n");
    exit(1);
}

foreach ($files as $file) {
    $source = $sourceDir.'/'.$file;
    $target = $targetDir.'/'.$file;

    if (! is_file($source)) {
        continue;
    }

    if (! copy($source, $target)) {
        fwrite(STDERR, "Unable to copy {$file} to public/css\n");
        exit(1);
    }
}
