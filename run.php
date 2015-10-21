<?php
/**
 * Nuwani-v2 Bot Framework
 *
 * This file is part of the Nuwani v2 Bot Framework, a simple set of PHP classes
 * which allow you to set-up and run your own bot. It features advanced,
 * PHP 5.3 based syntax for optimal performance and security.
 *
 * @author Peter Beverloo <peter@lvp-media.com>
 */

define ('NUWANI_STARTTIME', microtime (true));
define ('NUWANI_NAME', 'Nuwani');
define ('NUWANI_VERSION', 'v2.2-beta');
define ('NUWANI_VERSION_STR', NUWANI_NAME . ' ' . NUWANI_VERSION);

error_reporting (E_ALL);

if (version_compare (PHP_VERSION, '5.3.0b1-dev') < 0)
	die ("You need PHP 5.3 or higher to run Nuwani.\n");

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

define ("NL",	"\n");
define ("RNL",	"\r\n");

set_time_limit (0);
chdir (__DIR__);

if ($_SERVER ['argc'] >= 2 && $_SERVER ['argv'][1] == 'restart')
	sleep (1); // Give the other bot the time to disconnect

Nuwani \ Configuration	:: getInstance () -> register   ($aConfiguration);
Nuwani \ NetworkManager	:: getInstance () -> Initialise ($aConfiguration ['Networks']);
Nuwani \ BotManager	:: getInstance () -> Initialise ($aConfiguration ['Bots']);
Nuwani \ Memory		:: Initialise  ();

$g_bRun = true ;
while ($g_bRun)
{
	try
	{
		Nuwani \ BotManager	:: getInstance () -> process ();
		Nuwani \ ModuleManager	:: getInstance () -> onTick ();
		Nuwani \ Timer 	   	:: process ();
		Nuwani \ Memory	   	:: process ();
		
		if (count (BotManager :: getInstance ()) == 0)
		{
			$g_bRun = false;
		}
		
		usleep ($aConfiguration ['SleepTimer']);
	}
	catch (Exception $pException)
	{
		Nuwani \ ErrorExceptionHandler :: getInstance () -> processException ($pException);
		@ ob_end_flush ();
	}
}
