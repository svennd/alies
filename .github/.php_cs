<?php
# force tab instead of spaces
# exclude view
$finder = PhpCsFixer\Finder::create()
    ->exclude('application/views')
    ->exclude('system')
	->notPath('index.php')
;

$config = new PhpCsFixer\Config();
return $config->setRules([
        '@PSR2' => true,
		'indentation_type' => true,
    ])
	->setIndent("\t")
	->setLineEnding("\n")
    ->setFinder($finder)
;