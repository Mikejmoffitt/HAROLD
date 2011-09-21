<?php
// HAROLD file upload script
// Copies file to songs dir and enters files into db as available song for specified user.

$WEBAUTH_USER = getenv('WEBAUTH_USER');
$id = $_POST["song_id"];

if($_POST["Select"]) {
	$con = mysql_connect("localhost", "harold", "goshsurehopeidontfailthismajorproject");
	if (!$con)
	{
		die('Could not connect: ' . mysql_error());
	}

	mysql_real_escape_string($id);
	mysql_select_db("harold", $con);
	$result = mysql_query("select * from selections where username='$WEBAUTH_USER';");	

	if(!mysql_fetch_array($result))
	{
		mysql_query("insert into selections (username, selection) VALUES('$WEBAUTH_USER', $id)");
		print "Tired of the trombones, hmm? Your selection updated, press back to return to the preferences console.";
	}
	elseif(mysql_query("update selections set selection=$id where username='$WEBAUTH_USER';"))
	{
		print "Selection updated, press back to return to the preferences console.";
	}
	else 
	{
		print "Error updating selection, press back to return to the preferences console..";
	}
	mysql_close($con);
}
elseif($_POST["Delete"])
{
	$con = mysql_connect("localhost", "harold", "goshsurehopeidontfailthismajorproject");
	mysql_real_escape_string($id);	

	if (!$con)
	{
		die('Could not connect: ' . mysql_error());
	}
	mysql_select_db("harold", $con);
	$result = mysql_query("select * from songs where username='$WEBAUTH_USER' and id=$id");
	$row = mysql_fetch_array($result);
	$filename = $row['filename'];
	mysql_close($con);

	if(unlink("/var/www/songs/$WEBAUTH_USER/$filename"))
	{
		// remove from songs table
		$con = mysql_connect("localhost", "harold", "goshsurehopeidontfailthismajorproject");
        	
		if (!$con)
        	{
                	die('Could not connect: ' . mysql_error());
        	}
        	mysql_select_db("harold", $con);
        	$result = mysql_query("delete from songs where id=$id");
        	mysql_close($con);

		// TODO: handle currently-selected-song case
		echo "Successfully deleted $filename, press back to return to the preferences console.";
	}
	else
	{
		echo "Error: delete failed, press back to return to the preferences console.";
	}
}
?>
