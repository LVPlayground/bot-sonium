<?php
/**
 * Nuwani-v2 Bot Framework
 *
 * This file is part of the Nuwani v2 Bot Framework, a simple set of PHP classes which allow
 * you to set-up and run your own bot. It features advanced, PHP-5.3 based syntax for
 * optimal performance and security.
 *
 * Author: Peter Beverloo
 *         peter@lvp-media.com
 */

require_once 'lvp.echo.php';

use Nuwani \ Bot;

class Stats extends Nuwani\ModuleBase
{
	const	STATS_MANAGER_LVPECHO		= 0;
	
	/**
	 * Property: m_aStatistics
	 * 
	 * An array with the registered statistic handlers for this module, which will mostly
	 * contain instances of statistic-helpers, to keep different things devided.
	 */
	
	private $m_aStatistics;
	
	/**
	 * Function: __construct
	 * 
	 * The constructor will initialise all our stats listeners, who each reply to a
	 * different kind of stat or command. To keep everything devided mainly.
	 */
	
	public function __construct ()
	{
		$this -> m_aStatistics = array
		(
			/* STATS_MANAGER_LVPECHO */	new LvpEchoStatistics ()
		);
	}

	/**
	 * Function: onChannelPrivmsg
	 * Argument: pBot (Bot) - The bot who received the public channel message
	 * Argument: sChannel (string) - Channel in which we received the message
	 * Argument: sNickname (string) - The nickname associated with this message
	 * Argument: sMessage (string) - And of course the actual message we received
	 *
	 * PHP Code evaluations may occur in public channels, such as #Sonium which
	 * is frequently used for testing. This function will check whether we're
	 * allowed to evaluate anything here.
	 */
	
	public function onChannelPrivmsg (Bot $pBot, $sChannel, $sNickname, $sMessage)
	{
		if (strtolower ($sChannel) == '#lvp.echo')
		{
			$this -> m_aStatistics [self :: STATS_MANAGER_LVPECHO] -> onPrivmsg ($pBot, $sChannel, $sNickname, $sMessage);
		}
	}
	
};

?>
