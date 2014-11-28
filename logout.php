<?php
	// include the general database information and connect to it
	include "databaseInfo.php";
	$connection = mysqli_connect("localhost", $db_username, $db_password, $db_name);
	
	// store the token information in local variables
	$username = $_COOKIE['username'];
	$token = $_COOKIE['token'];
	$sessionID = $_COOKIE['sessionID'];
	
	// check to see if the token information for this user is present
	$query = "SELECT COUNT(*) AS count FROM session_info WHERE username='" . $username . "'";
	$row = mysqli_fetch_array(mysqli_query($connection, $query));
	if ( $row["count"] == 0 )
	{
		// exit if not present
		mysqli_close($connection);
		print "Access Denied";
		exit();
	}
	
	// check to see if the session identifer matches
	$query = "SELECT * FROM session_info WHERE username='" . $username . "'";
	$row = mysqli_fetch_array(mysqli_query($connection, $query));
	if ( $sessionID != $row['sessionID'] )
	{
		// exit if not valid
		mysqli_close($connection);
		print "Access Denied";
		exit();
	}
	
	// check to see if the token matches (if it doesn't, this means the token was hijacked)
	if ( $token != $row['token'] )
	{
		// exit if not valid, but also delete the token from the database
		mysqli_query($connection, "DELETE FROM session_info WHERE username='" . $username . "'");
		mysqli_close($connection);
		print "Access Denied";
		exit();
	}
	
	// delete the token information from the database
	mysqli_query($connection, "DELETE FROM session_info WHERE username='" . $username . "'");
	
	// delete the token information from the user's cookies
	unset($_COOKIE["username"]);
	setcookie("username", "", time());
	unset($_COOKIE["token"]);
	setcookie("token", "", time());
	unset($_COOKIE["sessionID"]);
	setcookie("sessionID", "", time());
	
	// close the connection to the database
	mysqli_close($connection);

	// redirect to the login page
	//header("Location: ...");
?>