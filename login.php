<?php
	// check both username and password are present
	if ( !isset($_POST['username']) || strlen($_POST['username']) == 0 || $_POST['username'] == "" ) { print "Login unsuccessful."; exit(); }
	if ( !isset($_POST['password']) || strlen($_POST['password']) == 0 || $_POST['password'] == "" ) { print "Login unsuccessful."; exit(); }
	
	// connect to the database
	include "databaseInfo.php";
	$connection = mysqli_connect("localhost", $db_username, $db_password, $db_name);
	
	// store them in easier to access variables, and sanitize them
	$username = mysqli_real_escape_string($connection, $_POST['username']);
	$password = mysqli_real_escape_string($connection, $_POST['password']);
	
	// check the username is present in the database
	$result = mysqli_query($connection, "SELECT COUNT(*) AS count FROM accounts WHERE username='" . $username . "'");
	$row = mysqli_fetch_array($result);
	if ( $row['count'] == 0 )
	{
		// exit if not present
		print "Login unsuccessful.";
		mysqli_close($connection);
		exit();
	}
	
	// check the password matches the hash
	$result = mysqli_query($connection, "SELECT * FROM accounts WHERE username='" . $username . "'");
	$row = mysqli_fetch_array($result);
	$hash = $row['password'];
	if ( $hash != crypt($password, $hash) )
	{
		// exit if it doesn't match
		print "Login unsuccessful.";
		mysqli_close($connection);
		exit();
	}
	
	// generate a new token and series identifier, and how long they'll last in the user's cookies (12 hours)
	$timeLimit = time() + 43200;
	$token = substr(md5(uniqid(rand(), true)), 0, 25);
	$sessionID = substr(md5(uniqid(rand(), true)), 0, 25);
	
	// delete any old tokens from the database
	mysqli_query($connection, "DELETE FROM session_info WHERE username='" . $username . "'");
	
	// store the new token in the database and in the user's cookies
	mysqli_query($connection, "INSERT INTO session_info VALUES ( '" . $username . "', '" . $token . "', '" . $sessionID . "' )");
	setcookie("username", $username, $timeLimit);
	setcookie("token", $token, $timeLimit);
	setcookie("sessionID", $sessionID, $timeLimit);
	
	// close the connection
	mysqli_close($connection);

	// redirect to the main admin page
	//header("Location: ...");
?>