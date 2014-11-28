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
	
	// create new token information
	$token = substr(md5(uniqid(rand(), true)), 0, 25);
	
	// store that new information
	mysqli_query($connection, "UPDATE session_info SET token='" . $token . "' WHERE username='" . $username . "'");
	setcookie("username", $username, time() + 43200);
	setcookie("token", $token, time() + 43200);
	setcookie("sessionID", $sessionID, time() + 43200);
	
	// logout button
?>
	<form action="logout.php" method="post">
		<input type="submit" value="Logout" />
	</form>
<?
	// close connection
	mysqli_close($connection);
?>