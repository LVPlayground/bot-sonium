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

use Nuwani \ Bot;

class Connection extends Nuwani \ ModuleBase
{
	/**
	 * Function: __construct
	 *
	 * The constructor will initialise a list of the people who are allowed
	 * to evaluate direct PHP code, using the configuration manager.
	 */
	
	public function __construct ()
	{
		echo 'Loaded the Connection module..' . NL;
	}
	
	/**
	 * Function: onConnect
	 * Argument: pBot (Bot) - The bot who is connecting with a server.
	 *
	 * This function will be called as soon as a bot has connected to a
	 * network. We'll be executing automated commands- and joins here.
	 */
	
	public function onConnect (Bot $pBot)
	{
		if (isset ($pBot ['OnConnect']))
		{
			$aChannels    = array ();
			$aConnectInfo = $pBot ['OnConnect'];
			
			if (isset ($aConnectInfo ['Channels']))
			{
				$aChannels = $aConnectInfo ['Channels'];
				unset ($aConnectInfo ['Channels']);
			}
			
			foreach ($aConnectInfo as $sCommand)
				$pBot -> send ($sCommand);
			
			foreach ($aChannels as $sChannel)
				$pBot -> send ('JOIN ' . $sChannel);
		}
	}
	
	/**
	 * Function: onUnhandledCommand
	 * Argument: pBot (Bot) - Bot which received this command.
	 *
	 * Something unknown, weird, unexplored or otherwise not included has been
	 * received by this bot, and this is a function in which we'll handle it. This
	 * function will act as a connection-helper, if anything fails, we'll make
	 * sure the bot can connect either way.
	 */
	
	public function onUnhandledCommand ($pBot)
	{
		switch ($pBot -> In -> Chunks [1])
		{
			case 433:	// Sonium :Nickname is already in use.
			{
				$sNickname = $pBot ['Nickname'] . '_' .  rand (1000,9999);
				$pBot -> send ('NICK ' . $sNickname);
				
				break;
			}
			
			case 461:	// USER :Not enough parameters
			{
				$sUsername = !empty ($pBot ['Username']) ? $pBot ['Username'] : 'Nuwani';
				$sRealname = !empty ($pBot ['Realname']) ? $pBot ['Realname'] : VERSION_STR;
				$pBot -> send ('USER ' . $sUsername . ' ' . $sUsername . ' - :' . $sRealname);
				
				break;
			}
			
			case 'KILL':	// Killed by the server for whatever reason;
			{
				new Nuwani \ Timer (function () use ($pBot) 
					{
						$pBot ['Socket'] -> restart ();
						$pBot -> connect ();
						
					}, 1000, false);
				
				break;
			}
		}
	}
	
	/**
	 * Function: onShutdown
	 * Argument: pBot (Bot) - The bot who will be disconnecting in a sec.
	 *
	 * This function gets invoked when the bot is about to shut down, so right
	 * before unregistering itself and sending the IRC quit messages.
	 */
	
	public function onShutdown (Bot $pBot)
	{
		if (isset ($pBot ['QuitMessage']))
		{
			$pBot -> send ('QUIT :' . $pBot ['QuitMessage']);
			return Nuwani \ ModuleManager :: FINISHED;
		}
	}
	
};

?>