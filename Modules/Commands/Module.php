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

require_once 'Modules/Commands/Command.php';

class Commands extends Nuwani \ ModuleBase implements Nuwani \ ISecurityModule, ArrayAccess, Countable, IteratorAggregate
{
	/**
	 * Property: m_aCommands
	 * 
	 * This property contains an array with all commands which have been registered with
	 * this bot. Various information will be included as well.
	 */
	
	private $m_aCommands;
	
	/**
	 * Property: m_sPrefix
	 *
	 * A single character which defines the prefix of all commands that will be used. Do
	 * NOT use multiple characters in here, seeing everything will totally break.
	 */
	
	private $m_sPrefix;
	
	/**
	 * Function: m_aSecurityProviders
	 *
	 * Defines an array with security-level providers, seeing we don't have any level
	 * system ourselfes, other modules are free to register theirs.
	 */
	
	private $m_aSecurityProviders;
	
	/**
	 * Function: __construct
	 *
	 * In the constructor of this function we'll attempt to load the data file associated
	 * with this module, containing all its current commands.
	 */
	
	public function __construct ()
	{
		$this -> m_aSecurityProviders = array ();
		$this -> m_aCommands = array ();
		$this -> m_sPrefix = '!';
		
		if (file_exists ('Data/Internal.dat'))
			$this -> m_aCommands = unserialize (file_get_contents ('Data/Internal.dat'));
		
		if (file_exists ('Data/Commands.dat'))
		{
			$aInformation = unserialize (file_get_contents ('Data/Commands.dat'));
			foreach ($aInformation [0] as $sName => $pCommand)
			{
				if ($pCommand instanceof Command)
					$this -> m_aCommands [$sName] = $pCommand;
			}
			
			$this -> m_sPrefix = $aInformation [1];
		}
                
		echo 'Loaded the Commands module..' . NL;
	}
	
	/**
	 * Function: __destruct
	 * 
	 * The destructor saves all commands again, just to be sure we have all changes.
	 */
	
	public function __destruct ()
	{
		$this -> save ();
	}
	
	/**
	 * Function: save
	 *
	 * The save function will serialize all this bot's data into a file, so it can be
	 * retreived for later use without any inconvenience. No need to call this
	 * manually; it'll be done after each modification to the m_aCommands array.
	 */
	
	private function save ()
	{
		$aStorageList = array ();
		
		foreach ($this -> m_aCommands as $sName => $pCommandInfo)
		{
			if ($pCommandInfo ['Internal'] === true)
				continue ;
			
			$aStorageList [$sName] = $pCommandInfo ;
		}
		
		return (file_put_contents ('Data/Commands.dat', serialize (array (
			$aStorageList, $this -> m_sPrefix ))) !== false);
	}
	
	/**
	 * Function: setPrefix
	 * Argument: sPrefix (string) - Prefix to assign to the commands.
	 *
	 * To instantly shift all commands from "!time" to "?time", this is the function that
	 * has to be used. It'll automatically serialize the new prefix too.
	 */
	
	public function setPrefix ($sPrefix)
	{
		if (strlen ($sPrefix) != 1)
			return false ;
		
		$this -> m_sPrefix = $sPrefix;
		return $this -> save ();
	}
	
	/**
	 * Function: addCommand
	 * Argument: sCommand (string) - Name of the command that you wish to implement.
	 * Argument: sCode (string) - Code to be associated with this command.
	 *
	 * The function which allows you to create a new command with the handler,
	 * so new fancy features can be added to the bot system.
	 */
	
	public function addCommand ($sCommand, $sCode)
	{
		$this -> m_aCommands [$sCommand] = new Command ($sCommand, $sCode);
		return $this -> save ();
	}
	
	/**
	 * Function: renameCommand
	 * Argument: sOldCommand (string) - Current name of the command
	 * Argument: sNewCommand (string) - New name of the command
	 * 
	 * This function will rename a currently listed command into something new,
	 * so it can be used properly. 
	 */
	
	public function renameCommand ($sOldCommand, $sNewCommand)
	{
		if (!isset ($this -> m_aCommands [$sOldCommand]))
			return false ;
	
		$this -> m_aCommands [$sNewCommand] = $this -> m_aCommands [$sOldCommand];
		unset ($this -> m_aCommands [$sOldCommand]);
		
		return $this -> save ();
	}
	
	/**
	 * Function: deleteCommand
	 * Argument: sCommand (string) - Command that you want to completely remove.
	 *
	 * This function can be used to totally remove a command from our system.
	 * Mind that no backups are made and that this is being applied immediatly.
	 */
	
	public function deleteCommand ($sCommand)
	{
		if (!isset ($this -> m_aCommands [$sCommand]))
			return false ;
		
		unset ($this -> m_aCommands [$sCommand]);
		return $this -> save ();
	}
	
	/**
	 * Function: getCommand
	 * Argument: sCommand (string) - The command of which you want the object.
	 * 
	 * This function lets you retrieve the object of a command.
	 */
	public function getCommand ($sCommand)
	{
		if (isset ($this -> m_aCommands[$sCommand]))
			return $this -> m_aCommands[$sCommand];
			
		return false;
	}
	
	/**
	 * Function: setCommandLevel
	 * Argument: sCommand (string) - Command to update the level of
	 * Argument: nLevel (integer) - Required level for executing this command.
	 *
	 * This function can be used to update the required security level of this command,
	 * e.g. 
	 */
	
	public function setCommandLevel ($sCommand, $nLevel)
	{
		if (isset ($this -> m_aCommands [$sCommand]))
		{
			$this -> m_aCommands [$sCommand] -> setSecurityLevel ($nLevel);
			return $this -> save ();
		}
		
		return false ;
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
		if ($sMessage [0] == $this -> m_sPrefix)
		{
			return $this -> handleCommand ($pBot, $sChannel, $sNickname, $sMessage);
		}
		
		$sAlternativePrefix = $pBot ['Nickname'] . ': ' . $this -> m_sPrefix;
		if (substr ($sMessage, 0, strlen ($sAlternativePrefix)) == $sAlternativePrefix)
		{
			return $this -> handleCommand ($pBot, $sChannel, $sNickname, substr ($sMessage, strlen ($sAlternativePrefix) - 1));
		}
	}
	
	/**
	 * Function: onPrivmsg
	 * Argument: pBot (string) - Bot that received this privage message
	 * Argument: sNickname (string) - Source of the message
	 * Argument: sMessage (string) - The message that got PM'ed to us.
	 *
	 * People are free to send private messages to the bot, which gets
	 * handled right here. This function does the same as onChannelPrivmsg
	 */
	
	public function onPrivmsg (Bot $pBot, $sNickname, $sMessage)
	{
		if ($sMessage [0] == $this -> m_sPrefix)
		{
			return $this -> handleCommand ($pBot, false, $sNickname, $sMessage);
		}
	}
	
	/**
	 * Function: handleCommand
	 * Argument: pBot (Bot) - The bot who received the public channel message
	 * Argument: sChannel (string) - Channel in which we received the message
	 * Argument: sNickname (string) - The nickname associated with this message
	 * Argument: sMessage (string) - And of course the actual message we received
	 *
	 * This function determains whether we have to execute this command,
	 * or not. The only hardcoded command is !addcmd.
	 */
	
	private function handleCommand (Bot $pBot, $sChannel, $sNickname, $sMessage)
	{
		$aArguments = preg_split ('/(\s+)/', $sMessage);
		$sCommand   = substr (array_shift ($aArguments), 1);
		
		if (isset ($this -> m_aCommands [$sCommand]))
		{
			$sDestination = $sChannel === false ? $sNickname : $sChannel;
			$nSecurityLevel = $this -> m_aCommands [$sCommand] -> getSecurityLevel ();
			
			if ($nSecurityLevel != -1 &&
			    isset ($this -> m_aSecurityProviders [$nSecurityLevel]) &&
			    $this -> m_aSecurityProviders [$nSecurityLevel] -> checkSecurity ($pBot, $nSecurityLevel) === false)
			{
				return false ;
			}
			
			$this -> m_aCommands [$sCommand] ($pBot, $sDestination, $sChannel, $sNickname, $aArguments, trim (substr ($sMessage, strlen ($sCommand) + 2)));
		}
		
		return false ;
	}

	/**
	 * Function: registerSecurityProvider
	 * Argument: pProvider (class) - The security provider to register with this module.
	 * Argument: nLevel (integer) - Security level this provider will register.
	 *
	 * This function will register a new security provider with this very module,
	 * as soon as it gets available. Will be called automatically.
	 */
	
	public function registerSecurityProvider (ISecurityProvider $pProvider, $nLevel)
	{
		$this -> m_aSecurityProviders [$nLevel] = $pProvider;
	}
	
	/**
	 * Function: unregisterSecurityProvider
	 * Argument: pProvider (class) - Security Provider that is being unloaded.
	 *
	 * Will be called when a security provider unloads, so we will be aware that we
	 * won't be able to use it anymore. Could be for any reason.
	 */
	
	public function unregisterSecurityProvider (ISecurityProvider $pProvider)
	{
		foreach ($this -> m_aSecurityProviders as $nLevel => $pInstance)
		{
			if ($pInstance == $pProvider)
				unset ($this -> m_aSecurityProviders [$nLevel]);
		}
	}
	
	/**
	 * Function: count
	 * 
	 * This method returns the number of commands currently loaded.
	 */
	public function count ()
	{
		return count ($this -> m_aCommands);
	}
		
	/**
	 * Function: getIterator
	 * 
	 * Returns an ArrayIterator for m_aCommands, which can be used in a
	 * foreach statement.
	 */
	public function getIterator ()
	{
		return new ArrayIterator ($this -> m_aCommands);
	}
	
	// -------------------------------------------------------------------//
	// Region: ArrayAccess                                                //
	// -------------------------------------------------------------------//
	
	/**
	 * Function: offsetExists
	 * Argument: sOffset (string) - The command or setting to check
	 * 
	 * Check whether the offset exists within this module.
	 */
	public function offsetExists ($sOffset)
	{
		if ($sOffset == 'Prefix')
			return true;
			
		return isset ($this -> m_aCommands [$sOffset]);
	}
	
	/**
	 * Function: offsetGet
	 * Argument: sOffset (string) - The command or setting to get
	 * 
	 * Gets a specific setting of this module or the object of a command.
	 * If no bots are found, then this function tries to match patterns.
	 * Returns false if nothing has been found.
	 */
	public function offsetGet ($sOffset)
	{
		if ($sOffset == 'Prefix')
			return $this -> m_sPrefix;
			
		if (isset ($this -> m_aCommands [$sOffset]))
			return $this -> getCommand ($sOffset);
			
		if ($sOffset[0] == $this -> m_sPrefix && 
			isset ($this -> m_aCommands [substr ($sOffset, 1)]))
		{
			return $this -> getCommand (substr ($sOffset, 1));
		}
		
		$aChunks = explode(' ', strtolower ($sOffset));
		$aMatch  = $aReq = array ();
		
		/** Extract information from the pattern */
		foreach ($aChunks as $sChunk)
		{
			if (substr ($sChunk, 0, 5) == 'level') {
				$aReq ['level'] = array ($sChunk[5],
					(int) substr ($sChunk, 6));
			} else if (strpos ($sChunk, ':') !== false) {
				list ($sKey, $sValue) = explode (':', $sChunk);
				
				$aReq [$sKey] = $sValue;
			}
		}
		
		/** Let's see if there are commands which match */
		foreach ($this -> m_aCommands as $sName => $pCmd)
		{
			$bCont = false;
			
			if (isset ($aReq ['level']))
			{
				$nLevel = $aReq ['level'][1];
				switch ($aReq ['level'][0])
				{
					case '=':
						if ($pCmd ['Level'] != $nLevel) $bCont = true;
						break;
						
					case '<':
						if ($pCmd ['Level'] >= $nLevel) $bCont = true;
						break;
						
					case '>':
						if ($pCmd ['Level'] <= $nLevel) $bCont = true;
						break;
				}
				
				if ($bCont) continue;
			}
			
			if (isset ($aReq ['name']) && strpos ($pCmd, $aReq ['name']) === false)
				continue;
				
			if (isset ($aReq ['network']) && !$pCmd -> checkNetwork ($aReq ['network']))
				continue;
			
			$aMatch[] = $this -> m_aCommands [$sName];
		}
		
		return $aMatch;
	}
	
	/**
	 * Function: offsetSet
	 * Argument: sOffset (string) - The command to set
	 * Argument: mValue (mixed) - The value
	 * 
	 * This is a shortcut to addCommand().
	 */
	public function offsetSet ($sOffset, $mValue)
	{
		if ($sOffset == 'Prefix')
			return $this -> setPrefix ($mValue);
		
		$this -> addCommand ($sOffset, $mValue);
	}
	
	/**
	 * Function: offsetUnset
	 * Argument: sOffset (string) - The command to unset
	 * 
	 * This is a shortcut to deleteCommand().
	 */
	public function offsetUnset ($sOffset)
	{
		$this -> deleteCommand ($sOffset);
	}
	
};

?>