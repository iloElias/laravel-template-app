<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$rules = [
    '@PSR12' => true,
    'ordered_imports' => true,
    'no_unused_imports' => true,
    'no_mixed_echo_print' => ['use' => 'print'],
    'increment_style' => ['style' => 'post'],
    'yoda_style' => false,
    'concat_space' => ['spacing' => 'one'],
    'method_chaining_indentation' => true,
    'php_unit_test_class_requires_covers' => false,
    'array_indentation' => true,
];

$finder = Finder::create()
    ->in([
        __DIR__.'/app',
        __DIR__.'/config',
        __DIR__.'/database',
        __DIR__.'/resources',
        __DIR__.'/routes',
        __DIR__.'/tests',
    ])
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true)
;

return (new Config())
    ->setFinder($finder)
    ->setRules($rules)
    ->setRiskyAllowed(false)
    ->setUsingCache(true)
;
