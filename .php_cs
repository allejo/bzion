<?php
/**
* This file contains the configuration for php-cs-fixer
*
* @package    BZiON
* @license    https://github.com/allejo/bzion/blob/master/LICENSE.md GNU General Public License Version 3
* @link  http://cs.sensiolabs.org/
*/

$finder = Symfony\CS\Finder\DefaultFinder::create()
	->in(array(
		__DIR__ . '/app',
		__DIR__ . '/controllers',
		__DIR__ . '/migrations',
		__DIR__ . '/models',
		__DIR__ . '/src',
		__DIR__ . '/tests'
	))
	->exclude('cache')
;

return Symfony\CS\Config\Config::create()
	->level(Symfony\CS\FixerInterface::SYMFONY_LEVEL)
	->fixers(array(
		'-empty_return',
		'-multiline_array_trailing_comma',
		'-phpdoc_params',
		'-return',
		'align_double_arrow',
		'concat_with_spaces',
		'multiline_spaces_before_semicolon',
		'ordered_use'
	))
	->finder($finder)
;
