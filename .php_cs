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
		'-no_empty_lines_after_phpdocs', // Workaround for removed spaces before use declarations
		'-phpdoc_no_empty_return',
		'-phpdoc_no_package',
		'-phpdoc_params',
		'-phpdoc_separation',
		'-phpdoc_short_description',
		'-single_quote',
		'-return',
		'-unalign_equals',
		'align_double_arrow',
		'concat_with_spaces',
		'multiline_spaces_before_semicolon',
		'newline_after_open_tag',
		'ordered_use',
		'phpdoc_order'
	))
	->finder($finder)
;
