<?php
/**
 * TestHelper.php for App in /tests/
 *
 * @category   App
 * @package    App_UnitTest
 * @copyright  Copyright (c) 2008
 * @author     Harold Thetiot (hthetiot)
 */

//---------------------------------------------------------------------------
// Start output buffering

ob_start();

//---------------------------------------------------------------------------
// Maximize memory limit

ini_set('memory_limit', -1);

//---------------------------------------------------------------------------
// Define application environment

define('APPLICATION_ENV', 'production');
define('APPLICATION_CONFIG', CONFIG_PATH . '/application.ini');

