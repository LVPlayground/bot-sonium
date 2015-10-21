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

class Command implements Serializable, ArrayAccess
{
	/**
	 * Property: m_sCommand
	 *
	 * The actual command name, e.g. "test". The prefix is NOT included in this,
	 * seeing we want to have that private.
	 */
	
	private $m_sCommand;
	
	/**
	 * Property: m_sCode
	 *
	 * Contains the PHP code for the command. For performance reasons this will
	 * be cached in another property, as an anonymous function.
	 */
	
	private $m_sCode;
	
	/**
	 * Property: m_nLevel
	 *
	 * Sets the security level associated with this command. Level is related
	 * to the secutity providers which registered themselfes with the module.
	 */
	
	private $m_nLevel;
	
	/**
	 * Property: m_pCachedCommand
	 *
	 * The cached command is a anonymous function that'll execute the command's
	 * code. Reason for this is that it's faster when cached.
	 */
	
	private $m_pCachedCommand;
	
	/**
	 * Property: m_aNetworks
	 *
	 * The networks on which this command will be executed on. If it's empty,
	 * it will be executed on all networks. The networks should be supplied as
	 * the name given to them in config.php.
	 */
	
	private $m_aNetworks;
	
	/**
	 * Property: m_aChannels
	 * 
	 * The channels this command will be permitted to execute on. If it's empty,
	 * all channels are allowed.
	 */
	
	private $m_aChannels;
	
	/**
	 * Property: m_aStatistics
	 *
	 * The array holding all of the statistics of this command.
	 */
	
	private $m_aStatistics;

	/**
	 * Property: m_bInternal
	 *
	 * Indicates whether this command is an internal command or one added by the
	 * user. Internal commands should not be serialized to Commands.dat.
	 */
	
	private $m_bInternal;

	/**
	 * Function: __construct
	 * Argument: sCommand (string) - Command that should be executed
	 * Argument: sCode (string) - Code that should be executed for the command.
	 * 
	 * The constructor will initialise the basic variables in this class, and, incase
	 * required and possible, initialise the cached handler as well.
	 */
	
	public function __construct ($sCommand = '', $sCode = '')
	{
		$this -> m_sCode          = $sCode;
		$this -> m_sCommand       = $sCommand;
		$this -> m_bInternal	  = false;
		$this -> m_pCachedCommand = null;
		$this -> m_nLevel         = -1;
		
		$this -> m_aNetworks      = array ();
		$this -> m_aChannels      = array ();
		
		$this -> m_aStatistics    = array
		(
			'Executed' => 0,
			'TotalTime' => 0.0,
			'LastTime' => 0,
		);
		
		if ($this -> m_sCode != '')
			$this -> cache ();
	}
	
		
	/**
	 * Function: getCommand
	 *
	 * This function returns the name of the command which will be executed,
	 * pretty much returning the m_sCommand property.
	 */
	
	public function getCommand ()
	{
		return $this -> m_sCommand;
	}
	
	/**
	 * Function: getCode
	 *
	 * Returns the code which has been associated with this command. Making changes or
	 * whatsoever is not possible using this.
	 */
	
	public function getCode ()
	{
		return $this -> m_sCode;
	}
	
	/**
	 * Function: getSecurityLevel
	 *
	 * This function retuns the security level associated with this command,
	 * enabling you to check whether this use can execute this.
	 */
	
	public function getSecurityLevel ()
	{
		return $this -> m_nLevel;
	}
	
	/**
	 * Function: getNetworks
	 *
	 * This function returns the networks on which this command is allowed
	 * to execute.
	 */
	
	public function getNetworks ()
	{
		return $this -> m_aNetworks;
	}
	
	/**
	 * Function: getChannels
	 * 
	 * This function will return an array with the channels this command is
	 * allowed to execute in.
	 */
	
	public function getChannels ()
	{
		return $this -> m_aChannels;
	}
	
	/**
	 * Function: getStatistics
	 *
	 * Retrieves the array containing the statistics of this command.
	 */
	
	public function getStatistics ()
	{
		return $this -> m_aStatistics;
	}
	
	/**
	 * Function: setCommand
	 * Argument: sCommand (string) - New name of this command
	 *
	 * This function will set the actual command's name, to change it's name this function
	 * should be called. Purely for internal reference sake though.
	 */
	
	public function setCommand ($sCommand)
	{
		$this -> m_sCommand = $sCommand;
	}
	
	/**
	 * Function: setCode
	 * Argument: sCode (string) - Code this command should be executing.
	 *
	 * This function will update the code associated with this command. The caching will
	 * automatically be re-initialised for performance reasons.
	 */
	
	public function setCode ($sCode)
	{
		$this -> m_sCode = $sCode;
		$this -> cache ();
	}
	
	/**
	 * Function: setSecurityLevel
	 * Argument: nLevel (integer) - Security Level to apply to this command.
	 *
	 * A simple function which allows you to update the security level required
	 * to execute this command properly, will be checked externally.
	 */
	
	public function setSecurityLevel ($nLevel)
	{
		$this -> m_nLevel = $nLevel;
	}
	
	/**
	 * Function: setSecurityLevel
	 * Argument: aNetworks (array) - The networks to apply to this command.
	 *
	 * This function will let you add all the networks you want to give this
	 * command at once.
	 */
	
	public function setNetworks ($aNetworks)
	{
		if (count ($aNetworks) == 1 && $aNetworks [0] == '-')
			return $this -> m_aNetworks = array ();
		
		$this -> m_aNetworks = $aNetworks;
	}
	
	/**
	 * Function: addNetwork
	 * Argument: sNetwork (string) - The network to add to this command.
	 *
	 * Adds a single network entry to the networks array.
	 */
	
	public function addNetwork ($sNetwork)
	{
		if (!in_array ($sNetwork, $this -> m_aNetworks))
			$this -> m_aNetworks [] = $sNetwork;
	}
	
	/**
	 * Function: checkNetwork
	 * Argument: sNetwork (string) - The network to check for.
	 *
	 * This function checks if this command if allowed to execute on the given 
	 * network.
	 */
	
	public function checkNetwork ($sNetwork)
	{
		if (empty ($this -> m_aNetworks))
			return true;
		
		return in_array ($sNetwork, $this -> m_aNetworks);
	}
	
	/**
	 * Function: setChannels
	 * Argument: aChannels (array) - The channels to apply to this command.
	 * 
	 * This function allows you to set the array of channels in which this 
	 * command is allowed to execute in.
	 */
	
	public function setChannels ($aChannels)
	{
		if (count ($aChannels) == 1 && $aChannels [0] == '-')
			return $this -> m_aChannels = array ();
		
		$this -> m_aChannels = array_map ('strtolower', $aChannels);
	}
	
	/**
	 * Function: addChannel
	 * Argument: sChannel (string) - The channel to add.
	 * 
	 * This function lets you add a channel to the array of allowed channels.
	 */
	
	public function addChannel ($sChannel)
	{
		$sChannel = strtolower ($sChannel);
		
		if (!in_array ($sChannel, $this -> m_aChannels))
			$this -> m_aChannels [] = $sChannel;
	}
	
	/**
	 * Function: checkChannel
	 * Argument: sChannel (string) - The channel to check.
	 * 
	 * This function checks if this command is allowed to execute in the
	 * given channel.
	 */
	public function checkChannel ($sChannel)
	{
		if (empty ($this -> m_aChannels))
			return true;
		
		return in_array (strtolower ($sChannel), $this -> m_aChannels);
	}
	
	/**
	 * Function: cache
	 *
	 * The cache function will cache the actual command's code, to make sure the
	 * performance loss of eval() only occurs once, rather than every time.
	 */
	
	private function cache ()
	{
		$this -> m_pCachedCommand = create_function ('$pBot, $sDestination, $sChannel, $sNickname, $aParams, $sMessage', $this -> m_sCode);
                if ($this -> m_pCachedCommand === false)
                {
                        echo $this -> m_sCode;
                }
	}
	
	/**
	 * Function: serialize
	 *
	 * This function returns this command in a serialized form, so it can be stored
	 * in a file and re-created later on, using the unserialize function (suprise!)
	 */
	
	public function serialize ()
	{
		return serialize (array 
		(
			$this -> m_bInternal,
			$this -> m_sCommand,
			$this -> m_sCode,
			$this -> m_nLevel,
			$this -> m_aNetworks,
			$this -> m_aChannels,
			$this -> m_aStatistics
		));
	}
	
	/**
	 * Function: unserialize
	 *
	 * The unserialize method will, yes, unserialize a previously serialized command
	 * so we can use it again. Quite convenient for various reasons.
	 */
	
	public function unserialize ($sData)
	{
		$aInformation = unserialize ($sData);
		
		$this -> m_bInternal = $aInformation [0];
		$this -> m_sCommand  = $aInformation [1];
		$this -> m_sCode     = $aInformation [2];
		$this -> m_nLevel    = $aInformation [3];
		
		if (isset ($aInformation [4])) 
			$this -> m_aNetworks = $aInformation [4];
			
		if (isset ($aInformation [6])) {
			$this -> m_aChannels   = $aInformation [5];
			$this -> m_aStatistics = $aInformation [6];
		} else if (isset ($aInformation [5])) {
			$this -> m_aStatistics = $aInformation [5];
		}
		
		$this -> cache ();
	}

	/**
	 * Function: __invoke
	 * Argument: aArguments (array) - Arguments as passed on to this command.
	 *
	 * The invoking function which allows us to use fancy syntax for commands. It allows the user
	 * and bot-system to directly invoke the object variable.
	 */
	
	public function __invoke (Bot $pBot, $sDestination, $sChannel, $sNickname, $aParams, $sMessage)
	{
		if (is_callable ($this -> m_pCachedCommand) && 
		    $this -> checkNetwork($pBot ['Network']) &&
		    $this -> checkChannel($sChannel))
		{
			$sFunction = $this -> m_pCachedCommand;
			$this -> m_aStatistics ['Executed'] ++;
			$this -> m_aStatistics ['LastTime'] = time();
			
			ErrorExceptionHandler :: $Source = $sDestination ;
			
			ob_start ();
			
			$fStart = microtime (true);
			$sFunction ($pBot, $sDestination, $sChannel, $sNickname, $aParams, $sMessage);
			$this -> m_aStatistics ['TotalTime'] += microtime (true) - $fStart;
			
			$aOutput = explode (NL, trim (ob_get_clean ()));
			if (isset ($pBot) && $pBot instanceof Bot)
			{
				foreach ($aOutput as $sLine)
				{
					$pBot -> send ('PRIVMSG ' . $sDestination . ' :' . trim ($sLine), false);
					echo '[' . $sDestination . '] <' . $pBot ['Nickname'] . '> ' . trim ($sLine) . NL;
				}
			}
		}
		
		return false ;
	}
	
	/**
	 * Function: __toString
	 *
	 * This magic method enables this object to be echo'd, without calling
	 * methods. Useful for quick retrieval of which command this is.
	 */
	
	public function __toString()
	{
		return $this -> m_sCommand;
	}
	
	// -------------------------------------------------------------------//
	// Region: ArrayAccess                                                //
	// -------------------------------------------------------------------//
	
	/**
	 * Function: offsetExists
	 * Argument: sOffset (string) - The setting or statistic to check
	 * 
	 * Check whether the offset exists within this command.
	 */
	public function offsetExists ($sOffset)
	{
		return (in_array ($sOffset, array ('Command', 'Code', 'Level', 'Networks')) || 
			isset ($this -> m_aStatistics [$sOffset]));
	}
	
	/**
	 * Function: offsetGet
	 * Argument: sOffset (string) - The setting or statistic to get
	 * 
	 * Gets a specific setting or statistic of this command. 
	 * Returns false if no setting or statistic has been found.
	 */
	public function offsetGet ($sOffset)
	{
		switch ($sOffset)
		{
			case 'Command':  return $this -> m_sCommand;
			case 'Code':     return $this -> m_sCode;
			case 'Level':    return $this -> m_nLevel;
			case 'Networks': return $this -> m_aNetworks;
			case 'Internal': return $this -> m_bInternal;
		}
		
		if (isset ($this -> m_aStatistics [$sOffset]))
			return $this -> m_aStatistics [$sOffset];
		
		return false;
	}
	
	/**
	 * Function: offsetSet
	 * Argument: sOffset (string) - The setting to set
	 * Argument: mValue (mixed) - The value
	 * 
	 * Quickly set a certain setting for this command.
	 */
	public function offsetSet ($sOffset, $mValue)
	{
		switch ($sOffset)
		{
			case 'Command':  $this -> m_sCommand  = $mValue;
			case 'Code':     $this -> m_sCode     = $mValue;
			case 'Level':    $this -> m_nLevel    = $mValue;
			case 'Networks': $this -> m_aNetworks = $mValue;
		}
	}
	
	/**
	 * Function: offsetUnset
	 * Argument: sOffset (string) - The setting to unset
	 * 
	 * This is very much not allowed, since setting defaults could
	 * break the command or even more.
	 */
	public function offsetUnset ($sOffset)
	{
		return ;
	}
	
};

?>