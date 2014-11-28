<?php
	// include the database information
	include "databaseInfo.php";	

	// connect to the database
	$connection = mysqli_connect("localhost", $db_username, $db_password, $db_name);

	// store the username and password in local variables
	$username = $_POST['username'];
	$password = $_POST['password'];
	
	// sanitize them to avoid SQL injections
	$username = mysqli_real_escape_string($connection, $username);
	$password = mysqli_real_escape_string($connection, $password);
	
	// find the correct row in the database
	$result = mysqli_query($connection, "SELECT * FROM accounts WHERE username='" . $username . "'");
	$row = mysqli_fetch_array($result);
	
	// if it returned nothing, tell them it's incorrect
	if ( strlen($row["username"]) == 0 || $row["username"] == "" )
	{	
		$message = "Username not found.";
	}
	// otherwise, process it
	else
	{
		// check the password is correct
		if ( $row["password"] == crypt($password, $row["password"]) )
		{			
			// remove session tokens currently stored for this username
			$result = mysqli_query($connection, "DELETE FROM session_info WHERE username='".$username."'");

			// create the session token and store in the database
			$token = substr(md5(uniqid(rand(), true)), 0, 25);
			$session = substr(md5(uniqid(rand(), true)), 0, 25);
			$result = mysqli_query($connection, "INSERT INTO session_info VALUES ( '".$username."', '".$token."', '".$session."' )");

			// store that token in the browser cookies too
			$expireWhen = time() + 36000;
			setcookie("username", $username, $expireWhen);
			setcookie("token", $token, $expireWhen);
			setcookie("sessionID", $session, $expireWhen);

			$message = "Successfully logged in.<br>Stored new session token in database and cookies.";
		}
		// if not, tell them it's incorrect
		else
		{
			$message = "Password incorrect.";
		}
	}

	// close the connection to the database
	print $message;
	mysqli_close($connection);
	exit();

?>