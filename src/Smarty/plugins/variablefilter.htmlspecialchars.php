<?php
use FileExport\Smarty\Smarty;
/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage PluginsFilter
 */

/**
 * Smarty htmlspecialchars variablefilter plugin
 *
 * @param string $source input string
 *
 * @return string filtered output
 */
function smarty_variablefilter_htmlspecialchars($source)
{
    return htmlspecialchars($source, ENT_QUOTES, FileExport\Smarty\Smarty::$_CHARSET);
}
