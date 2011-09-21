<?php
// HAROLD file upload script
// Copies file to songs dir and enters files into db as available song for specified user.

$WEBAUTH_USER = getenv('WEBAUTH_USER');

// Make filename SQL-safe
mysql_real_escape_string($_FILES["file"]["name"]);

if ($_FILES["file"]["error"] > 0)
{
	echo "Return Code: " . $_FILES["file"]["error"] . "<br />";
}
else
{
	// If user does not have a songs directory, create one
	if(!file_exists("/var/www/songs/$WEBAUTH_USER"))
	{
		mkdir("/var/www/songs/$WEBAUTH_USER/");
	}

	if (file_exists("/var/www/songs/$WEBAUTH_USER/" . $_FILES["file"]["name"]))
	{
		echo $_FILES["file"]["name"] . " already exists. Press back to return to the preferences console.";
	}
	else
	{
		move_uploaded_file($_FILES["file"]["tmp_name"],
		  "/var/www/songs/$WEBAUTH_USER/" . $_FILES["file"]["name"]);
		$con = mysql_connect("localhost", "harold", "goshsurehopeidontfailthismajorproject");
		if (!$con)
		{
  			die('Could not connect: ' . mysql_error());
		}
		mysql_select_db("harold", $con);
		$filename = $_FILES["file"]["name"];
		$result = mysql_query("insert into songs (username, filename) VALUES('$WEBAUTH_USER','$filename');");
		mysql_close($con);	
		echo "Successfully uploaded " . $_FILES["file"]["name"] . ", press back to return to the preferences console.";
	}
}
?>
