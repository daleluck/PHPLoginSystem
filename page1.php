<?php

	// include the database information
	include "databaseInfo.php";
	
	$username = $_COOKIE["username"];
	$token = $_COOKIE["token"];
	$sessionID = $_COOKIE["sessionID"];
	
	// connect to the database and find the correct token entry
	$connection = mysqli_connect("localhost", $db_username, $db_password, $db_name);
	$result = mysqli_query($connection, "SELECT * FROM session_info WHERE username='" . $username . "'");
	$row = mysqli_fetch_array($result);
	
	// if there's no token present
	if ( (strlen($row["username"]) == 0 || $row["username"] == "") )
	{	
		// clear the user's cookies
		unset($_COOKIE["username"]); unset($_COOKIE["token"]); unset($_COOKIE["sessionID"]);
		setcookie("username", "", time());
		setcookie("token", "", time());
		setcookie("sessionID", "", time());
		
		// send them back to the login page
		print "You are not currently logged in.";
		// ...
		mysqli_close($connection);
		exit();	
	}
	// if the token information is all correct
	else if ( $token == $row["token"] && $sessionID == $row["series"] )
	{
		// update the token information (cookies)
		$newtoken = substr(md5(uniqid(rand(), true)), 0, 22);
		setcookie("username", $username, time() + 36000);
		setcookie("token", $newtoken, time() + 36000);
		setcookie("sessionID", $sessionID, time() + 36000);
		
		// update the database information
		$result = mysqli_query($connection, "UPDATE session_info SET token='".$newtoken."' WHERE username='".$username."'");
		
		// signal that they're logged in
		print "You're currently logged in.";
	}
	// if only the session ID is correct
	else if ( $token != $row["token"] && $sessionID == $row["series"] )
	{
		print "Someone has stolen your token information.";
		mysqli_close($connection);
		exit();	
	}
	// if none of it was correct
	else
	{
		// clear the user's cookies
		// send them back to the homepage
	}
	
	print "<br><br>" . $username . " // " . $row["username"] . "<br>";
	print $token . " // " . $row["token"] . "<br>";
	print $sessionID . " // " . $row["series"] . "<br>";
	
	// close the database connection
	mysqli_close($connection);
	exit();	
?>