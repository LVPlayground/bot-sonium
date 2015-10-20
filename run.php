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

require 'Sources/Singleton.php';
require 'Sources/ModuleBase.php';
require 'Sources/Configuration.php';
require 'Sources/BotManager.php';
require 'Sources/Exception.php';
require 'Sources/BotGroup.php';
require 'Sources/Database.php';
require 'Sources/Network.php';
require 'Sources/Modules.php';
require 'Sources/Memory.php';
require 'Sources/Socket.php';
require 'Sources/Timer.php';
require 'Sources/Bot.php';
require 'config.php';

define ("NL",	"\n");
define ("RNL",	"\r\n");

set_time_limit (0);
chdir (__DIR__);

if ($_SERVER ['argc'] >= 2 && $_SERVER ['argv'][1] == 'restart')
	sleep (1); // Give the other bot the time to disconnect

Configuration	:: getInstance () -> register   ($aConfiguration);
NetworkManager	:: getInstance () -> Initialise ($aConfiguration ['Networks']);
BotManager	:: getInstance () -> Initialise ($aConfiguration ['Bots']);
Memory		:: Initialise  ();
//ErrorExceptionHandler :: getInstance () -> Initialise ($aConfiguration ['ErrorHandling']);

$g_bRun = true ;
while ($g_bRun)
{
	try
	{
		BotManager	:: getInstance () -> process ();
		ModuleManager	:: getInstance () -> onTick ();
		Timer 	   	:: process ();
		Memory	   	:: process ();
		
		if (count (BotManager :: getInstance ()) == 0)
		{
			$g_bRun = false;
		}
		
		usleep ($aConfiguration ['SleepTimer']);
	}
	catch (Exception $pException)
	{
		ErrorExceptionHandler :: getInstance () -> processException ($pException);
		@ ob_end_flush ();
	}
}
