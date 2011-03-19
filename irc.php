<?php


// Set the timeout limit so the page wont timeout
set_time_limit(0);

$channel = "";

// IRC Class
include("irc_class.php");
// Config
include("config.php");

	// Create an instance of the IRC class
	$irc = new ConnectIrc(IRC_HOST, IRC_PORT);

	// Echo out the server and port
	echo "IRC Server: {$irc->server}<br />Port: {$irc->port}<br />";
	echo "<pre>";

	// Open socket
	if ( $irc->openSocket() ) {
	
		// Set password (leaving it blank defines no password)
		$irc->setPassword();
		// Set nick/user
		//$username = $_GET['nick'];
		if (isset($_GET['nick']))
			$username = $_GET['nick'];
		else
			$username = "LeonBot";
			
		$irc->setNick($username);
		$irc->setUser($username);
	
		// While you are connected to the server
		while ( $irc->connected() ) {
			
			// Print out the read buffer
			$buffer = $irc->showReadBuffer();
			//echo $buffer."\n\r";
			
			// Here is where you test for certain conditions
			$irc->returnLastSaid($message);
			$params = trim($message[PARAMS]);
			switch ($message[COMMAND])
			{
				// Shutting down
				case "!gtfo":
					echo "Shutting down\n\r";
					$irc->closeConnection(); exit;
					break;
					
				// Saying hello
				case "!hello":		
					echo "Saying hello to {$message[WHERE]}\n\r";
 					$irc->say("Hey, {$message[SENDER]}!", $message[WHERE]);
					break;
					
				// Handles joining rooms
				case "!join":
					echo "Joining {$params}\n\r";
					$channel = $params;
					$irc->joinChannel($channel);
					break;
					
				// handles parting rooms
				case "!part":
					echo "Leaving {$params}\n\r";
					$channel = $params;
					$irc->partChannel($channel);
					break;
					
				// changing nickname
				case "!nick":
					echo "Changing nick to {$params}\n\r";
					$irc->setNick($params);
					break;
					
				// grabbing someone's twitter status
				case "!twitter":
					echo "Grabbing status for {$params}\n\r";
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, "http://api.twitter.com/1/statuses/user_timeline/{$params}.json");
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					$json = curl_exec($ch);
					curl_close($ch);
					$jsonObject = json_decode($json);
					$status = $jsonObject[0]->text;
					echo "Got latest Tweet from {$params}\n\t-> {$status}";
					$irc->say("{$status}", $message[WHERE]);
					break;
				// for anything else ...
				default:
					if (strtolower($message[COMMAND]) == strtolower($irc->nick) || 
						strstr(strtolower($params), strtolower($irc->nick)))
						$irc->say("What the fuck do you want, {$message[SENDER]}?", $message[WHERE]);
					break;
			}
								
			// Handle the ping pong
			$irc->handlePingPong();			
		
			// Flush the buffer
			$irc->flushIrc();
		}
	
		// Close the connection
		if ( $irc->closeConnection() ) {
			echo "<br />Connection closed... ";
		} else {
			echo "<br />Connection had a problem closing... wait wtf?";
		}
	}

?>