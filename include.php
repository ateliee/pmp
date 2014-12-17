<?php
/**
 * PMP Application
 */
include_once(dirname(__FILE__) . '/src/errors/errors.php');
include_once(dirname(__FILE__) . '/src/app/setup.php');
// config file put loading
autoload_class();
dir_include_all(dirname(__FILE__).'/src/core');
include_once(dirname(__FILE__) . '/src/app/application.php');
