"""
AUTO-PORTED FROM: api/contrib/firmarXML/xmlseclibs/src/Utils/XPath.php
Mode: mechanical baseline (no manual refactor).
"""

from __future__ import annotations

# No function blocks were detected automatically.
# Original PHP source is kept below for manual completion.
ORIGINAL_PHP_SOURCE = r'''
<?php

namespace RobRichards\XMLSecLibs\Utils;

class XPath
{
    const ALPHANUMERIC = '\w\d';
    const NUMERIC = '\d';
    const LETTERS = '\w';
    const EXTENDED_ALPHANUMERIC = '\w\d\s\-_:\.';

    const SINGLE_QUOTE = '\'';
    const DOUBLE_QUOTE = '"';
    const ALL_QUOTES = '[\'"]';


    /**
     * Filter an attribute value for save inclusion in an XPath query.
     *
     * @param string $value The value to filter.
     * @param string $quotes The quotes used to delimit the value in the XPath query.
     *
     * @return string The filtered attribute value.
     */
    public static function filterAttrValue($value, $quotes = self::ALL_QUOTES)
    {
        return preg_replace('#'.$quotes.'#', '', $value);
    }


    /**
     * Filter an attribute name for save inclusion in an XPath query.
     *
     * @param string $name The attribute name to filter.
     * @param mixed $allow The set of characters to allow. Can be one of the constants provided by this class, or a
     * custom regex excluding the '#' character (used as delimiter).
     *
     * @return string The filtered attribute name.
     */
    public static function filterAttrName($name, $allow = self::EXTENDED_ALPHANUMERIC)
    {
        return preg_replace('#[^'.$allow.']#', '', $name);
    }
}
'''
