<?php

/**
 * Initialize internationalization information
 * 
 * @param type $locale Locale to use
 * @param type $domain Essentially, the filename for the ".mo" files
 * 
 * <p>This function makes the following assumptions:
 * <ul>
 * <li>The codeset is UTF-8</li>
 * <li>The numeric format for the locale is overridden and set to "C" style (decimals)</li>
 * <li>All *.mo files are in a subfolder off the root called "locale", e.g. [root]/locale/en/LC_MESSAGES>/li>
 * </ul>
 * </p>
 * 
 */
function i18n_init($locale = 'en_EN', $domain = 'messages') {
    $codeset = 'UTF8';
    putenv("LANG=$locale");
    putenv("LANGUAGE=$locale");
    putenv("LC_ALL=$locale");
    putenv("LC_MESSAGES=$locale");
    setlocale(LC_ALL,
            $locale . ".utf8",
            $locale . ".UTF8",
            $locale . ".utf-8",
            $locale . ".UTF-8",
            $locale,
            "CC_LANG");
    // This ensures that decimal numbers use a decimal point rather than a comma
    setlocale(LC_NUMERIC, 'C');
    bindtextdomain($domain, dirname(__FILE__).'/locale'); 
    bind_textdomain_codeset($domain, $codeset);
    textdomain($domain);
}

?>
