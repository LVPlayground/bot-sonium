<?php
// Copyright 2015 Las Venturas Playground. All rights reserved.
// Use of this source code is governed by the MIT license, a copy of which can
// be found in the LICENSE file.

use Nuwani \ Bot;

// The BotVersion module keeps track of the current version of the bot (using the VERSION file in
// its directory), and sends an announcement to a configured channel when an update has been found.
//
// Additionally, in order to avoid having the poll GitHub for updates, or rely on an HTTP server for
// providing webhooks that support the bot, this module features a very limited HTTP daemon that
// will listen to and process "push" webhook requests coming from GitHub.
class BotVersion extends Nuwani\ModuleBase {
    // Number of milliseconds that should be between update checks for the bot.
    const UPDATE_FREQUENCY_MS = 60 * 1000;

    private $m_announcementChannel;

    private $m_initialVersion;
    private $m_availableVersion;

    private $m_daemonSecret;
    private $m_daemonSocket;

    private $m_updateTimer;

    public function __construct() {
        $this->m_initialVersion = $this->determineCurrentVersion();
        $this->m_availableVersion = $this->m_initialVersion;

        // Load and apply the configuration for this module from the main config file.
        $configuration = Nuwani\Configuration::getInstance()->get('BotVersion');
        if (array_key_exists('channel', $configuration))
            $this->m_announcementChannel = $configuration['channel'];

        $this->m_daemonRequests = [];

        // Starts the GitHub daemon on the configured port with the configured secret.
        if (array_key_exists('daemon_port', $configuration) && array_key_exists('daemon_secret', $configuration))
            $this->startDaemon($configuration['daemon_port'], $configuration['daemon_secret']);

        // Create a timer that checks for updates to the version file at a given frequency. This
        // timer will be destroyed again when this instance goes away.
        $this->m_updateTimer =
            Nuwani\Timer::Create([$this, 'onUpdateCheck'], self::UPDATE_FREQUENCY_MS, true /* repeating */);
    }

    // Called when there are no further references to this class. Stops the update check timer.
    public function __destruct() {
        Nuwani\Timer::Stop($this->m_updateTimer);
    }

    // Starts the GitHub daemon by listening to incoming requests on port |$port| for all IP
    // addresses. The |$secret| will be used to authenticate incoming requests.
    private function startDaemon($port, $secret) {
        $this->m_daemonSecret = $secret;
        
        $this->m_daemonSocket = stream_socket_server('tcp://0.0.0.0:' . $port, $errno, $error);
        if (!$this->m_daemonSocket)
            throw new Exception('Unable to start the daemon: ' . $error);

        stream_set_blocking($this->m_daemonSocket, 0);
    }

    // Called at the interval defined by |UPDATE_FREQUENCY_MS| to determine if updates are available
    // for the bot. When there are, an announcement will be send to a given channel.
    public function onUpdateCheck() {
        $availableVersion = $this->determineCurrentVersion();
        if ($availableVersion == $this->m_availableVersion)
            return;

        $message  = Nuwani\ModuleBase::COLOUR_BROWN . 'Update available!';
        $message .= Nuwani\ModuleBase::COLOUR . ' Currently running ';
        $message .= Nuwani\ModuleBase::COLOUR_PURPLE . substr($this->m_initialVersion, 0, 7);
        $message .= Nuwani\ModuleBase::COLOUR . ', source updated to ';
        $message .= Nuwani\ModuleBase::COLOUR_PURPLE . substr($availableVersion, 0, 7);
        $message .= Nuwani\ModuleBase::COLOUR . '. Use !restart to update.';

        $bot = Nuwani\BotManager::getInstance()->offsetGet('channel:' . $this->m_announcementChannel);
        if ($bot)
            $bot->send('PRIVMSG ' . $this->m_announcementChannel . ' :' . $message);

        $this->m_availableVersion = $availableVersion;
    }

    // Called when a message has been send to a channel. We implement the !botversion command here,
    // that will reply with the current version -and available version if different- of this bot.
    public function onChannelPrivmsg(Bot $bot, $channel, $nickname, $message) {
        if ($message != '!botversion')
            return;

        $reply  = 'Running version ';
        $reply .= Nuwani\ModuleBase::COLOUR_PURPLE . substr($this->m_initialVersion, 0, 7);
        $reply .= Nuwani\ModuleBase::COLOUR;

        if ($this->m_availableVersion != $this->m_initialVersion) {
            $reply .= ', however, version ';
            $reply .= Nuwani\ModuleBase::COLOUR_PURPLE . substr($this->m_availableVersion, 0, 7);
            $reply .= Nuwani\ModuleBase::COLOUR . ' is available';
        }

        $reply .= '.';

        $bot->send('PRIVMSG ' . $channel . ' :' . $reply);
    }

    // Reads and returns the latest sha hash from the VERSION file in the bot's root directory. If
    // the file does not exist, the string "UNKNOWN" will be returned instead.
    private function determineCurrentVersion() {
        if (!file_exists('VERSION'))
            return 'UNKNOWN';

        return trim(file_get_contents('VERSION'));
    }
};
