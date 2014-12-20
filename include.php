<?php
/**
 * PMP Application
 */
// config file put loading
dir_include_all(dirname(__FILE__).'/src/core');
autoload_class();
require_once(dirname(__FILE__).'/app/init.php');

// set locale
Localize::setLocale("ja","JP","UTF-8");
Localize::setTimeZone('Asia/Tokyo');
// language file
Localize::textDomain('application');
Localize::bindTextDomain('application',dirname(__FILE__).'/languages');

//PHPLangError::init();
