<?php

$finder = PhpCsFixer\Finder::create()
	->exclude('vendor')
	->in(__DIR__)
;

$config = new PhpCsFixer\Config();
return $config
	->setIndent("\t")
	->setRules([
		'braces'=>[
			'position_after_functions_and_oop_constructs'=>'same'
		],
		'elseif' => true
	])
	->setFinder($finder)
	;