<?php
$bRunning = strpos (`ps faux | grep sonium`, 'run.php') !== false;
if ($bRunning === false)
{
	shell_exec ('/usr/local/bin/php /home/lvp/sonium/run.php >/var/log/nuwani.log 2>&1 &');
}
