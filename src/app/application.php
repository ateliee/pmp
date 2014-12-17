<?php
/**
 * application setting
 */

define("PMP_TEXTDOMAIN","application");
define("PMP_TEXTDOMAIN_LANGUAGEDIR",dirname(__FILE__)."/../errors/languages");
// set locale
Localize::setLocale("ja","JP","UTF-8");
Localize::setTimeZone('Asia/Tokyo');
// language file
Localize::textDomain(PMP_TEXTDOMAIN);
Localize::bindTextDomain(PMP_TEXTDOMAIN,PMP_TEXTDOMAIN_LANGUAGEDIR);

//PHPLangError::init();
