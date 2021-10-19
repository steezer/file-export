<?php
use FileExport\Smarty\Smarty;
/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage PluginsModifierCompiler
 */

/**
 * Smarty count_characters modifier plugin
 * Type:     modifier<br>
 * Name:     count_characteres<br>
 * Purpose:  count the number of characters in a text
 *
 * @link   http://www.smarty.net/manual/en/language.modifier.count.characters.php count_characters (Smarty online manual)
 * @author Uwe Tews
 *
 * @param array $params parameters
 *
 * @return string with compiled code
 */
function smarty_modifiercompiler_count_characters($params)
{
    if (!isset($params[1]) || $params[1] != 'true') {
        return 'preg_match_all(\'/[^\s]/' . FileExport\Smarty\Smarty::$_UTF8_MODIFIER . '\',' . $params[0] . ', $tmp)';
    }
    if (FileExport\Smarty\Smarty::$_MBSTRING) {
        return 'mb_strlen(' . $params[0] . ', \'' . addslashes(FileExport\Smarty\Smarty::$_CHARSET) . '\')';
    }
    // no MBString fallback
    return 'strlen(' . $params[0] . ')';
}
