<?php
/**
 * PMP Application
 */
include_once(dirname(__FILE__) . '/errors/errors.php');
include_once(dirname(__FILE__) . '/app/setup.php');
// config file put loading
autoload_class();
dir_include_all(dirname(__FILE__).'/core');
include_once(dirname(__FILE__) . '/app/application.php');
