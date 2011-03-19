<?php

define(FULL_STRING, 0);
define(SENDER, 1);
define(CLIENT, 2);
define(HOST_INFO, 3);
define(WHERE, 4);
define(COMMAND, 5);
define(PARAMS, 6);

// ConnectIRC Class
class ConnectIrc {

	// Variables
	public $server;
	public $port;
	public $channel;
	public $password = "NOPASS";
	public $nick;
	public $socket;
	public $errno;
	public $errstr;
	public $timeout = 2;
	public $rbuffer;

	// Constructor
	function __construct($server, $port) {
		$this->server = $server;
		$this->port = $port;
	}	
	
	// Open socket
	function openSocket() {
		$this->socket = fsockopen($this->server, 
							  $this->port, 
							  $this->errno, 
							  $this->errstr,
							  $this->timeout);
		return $this->socket;
	}
	
	// Set channel
	function setChannel($channel) {
		$this->channel = $channel;
	}
	
	// Set nick
	function setNick($nick) {
		$this->nick = $nick;
		$this->sendCommand("NICK {$this->nick}\n\r");
	}
	
	function setUser($nick) {
		$this->sendCommand("USER {$this->nick} {$this->nick} {$this->nick} {$this->nick} :{$this->nick} \n\r");
	}
	
	// Set password
	function setPassword($password=false) {
		if ($password == true) {
			$this->password = $password;
		}
		$this->sendCommand("PASS {$this->password}\n\r");
	}
	
	// Talk in the chat
	function say($message, $who)
	{
		$this->sendCommand("PRIVMSG {$who} :{$message}\n\r");
	}
	
	// Return connected status
	function connected() {
		return !feof($this->socket);
	}
	
	// Send command
	function sendCommand($command) {
		fputs($this->socket, $command, strlen($command));
	}
	
	// Join channel
	function joinChannel($channel) {
		$this->sendCommand("JOIN {$channel}\n\r");
	}
	
	// Part channel
	function partChannel($channel) {
		$this->sendCommand("PART {$channel}\n\r");
	}
	
	// Handle the ping pong
	function handlePingPong() {
		if (substr($this->rbuffer, 0, 6) == "PING :") {
			$this->sendCommand("PONG: " . substr($this->rbuffer, 6) . "\n\r");
		}
	}
	
	// Return the read buffer
	function showReadBuffer() {
		$this->rbuffer = fgets($this->socket, 1024); 
		$line = "[RECIVE] '".$this->rbuffer."'<br />\n\r";
          return $line;
	}
	
	// Returns an array with the last message
	function returnLastSaid(&$message) 
	{
		$needle = "/^:([a-zA-Z0-9_\-]+)!([a-zA-Z0-9_\-~]+)[@]([a-zA-Z0-9_\-\.]+) PRIVMSG ([#a-zA-Z0-9\-_]+) :([a-zA-Z!]+)(.*)$/";
		preg_match($needle, $this->rbuffer, $message);	
	}
	
	// Flush connection
	function flushIrc() {
		flush();
	}
	
	// Close the connection
	function closeConnection() {
		if (fclose($this->socket)) { 
			return true; 
		} else { 
			return false; 
		}
	}
}

?>
