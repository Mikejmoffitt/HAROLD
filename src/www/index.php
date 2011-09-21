<HTML>
<head>
<title>HAROLD Preferences Console</title>
</head>
<body>
<h2>HAROLD Preferences Management Console</h2>
<?php
	$WEBAUTH_USER = getenv('WEBAUTH_USER'); 
	print "Welcome, $WEBAUTH_USER!<br />";
?>
Current song:
<?php
	$con = mysql_connect("localhost", "harold", "goshsurehopeidontfailthismajorproject");
	if (!$con)
	{
		die('Could not connect: ' . mysql_error());
	}
	mysql_select_db("harold", $con);
	$result = mysql_query("select * from selections where username='$WEBAUTH_USER'");
	$row = mysql_fetch_array($result);
	$id = $row["selection"];
	$result = mysql_query("select * from songs where id=$id");
	$row = mysql_fetch_array($result);
	print $row['filename'];
	mysql_close($con);
?>
<br /><br />
Select from uploaded files:<br />
<form action="select_or_delete_song.php" method="post">
<?php
	$con = mysql_connect("localhost", "harold", "goshsurehopeidontfailthismajorproject");
        if (!$con)
        {
                die('Could not connect: ' . mysql_error());
        }
        mysql_select_db("harold", $con);
        $result = mysql_query("select * from songs where username='$WEBAUTH_USER'");
        
	while($row = mysql_fetch_array($result))
	{
		print '<input name="song_id" type="radio" value="' . $row['id'] . '" />' . $row['filename'] . "<br />\n";
	}

	mysql_close($con);
?>
<input type="submit" name="Select" value="Select" />
<input type="submit" name="Delete" value="Delete" />
</form>
Upload file:
<form action="upload_file.php" method="post" enctype="multipart/form-data">
<input type="file" name="file" id="file" />
<br />
<input type="submit" name="submit" value="Submit" />
</form>
<?php
	if($handle = opendir('/var/www/harold-icons')) {
		$files = array();
		$index = 0;

		while (false !== ($file = readdir($handle))) {
        		if($file != "." && $file != "..") {
				$files[$index] = $file;
    				$index++;
			}
		}

		$filename = $files[array_rand($files)];
		print "<img src=\"harold-icons/$filename\" />";
	}
?>
</body>
</HTML>
