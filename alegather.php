<html>
<head><title>ALE parsing page 1</title>
<!-- <link rel="stylesheet" type="text/css" href="_css/stil.css"> -->
<script src="https://code.jquery.com/jquery-2.0.2.min.js"></script>
<script src="form.js"></script>
<script>
var counter = 2;									// start at 2 because 1 is handled by submit.php
function startFunction() {							// clean up screen and display first table
	document.getElementById('welcome').style.display='none';
	$('#jquery-form').fadeIn();

	// fire off the request to /submit.php
	request = $.ajax({
		url: "submit.php",
		type: "post",
		data: null
	});

	// callback handler that will be called on success
	request.done(function (response, textStatus, jqXHR){
		// log a message to the console
		console.log("Hooray, it worked!");
		$('#results').html(response);
	});

	// callback handler that will be called on failure
	request.fail(function (jqXHR, textStatus, errorThrown){
		// log the error to the console
		console.error(
			"The following error occured: " +
			textStatus, errorThrown);
	});

}
</script>
<style type="text/css">
table.gridtable {
	font.family: verdana, arial, sans-serif;
	font.size: 11px;
	color: #333333;
	border-width: 1px;
	border-color: #666666;
	border-collapse: collapse;
}
table.gridtable th {
	padding: 4px;
	border-style: solid;
	border-color: #666666;
	background-color: $dedede;
}
table.gridtable td {
	border-width: 1px;
	border-color: #666666;
	border-style: solid;
	padding: 4px;
	background-color: #ffffff;
}
</style>
</head>

<body>
<?php
	// Create MySQL connection
	$mysqli = @mysqli_connect("localhost","elliott_copra","copra","elliott_copraALE");
	// Check connection
	if (mysqli_connect_errno($con)) {
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
		exit();
	}
	$aledir 		= "/Users/elliott/Sites/copra/ALE";
	$mediadir 		= "/Users/elliott/Sites/copra/H264";
	$projectname	= "The PET Squad";
	$filelist;

	echo "<p>ALE directory is set to: " . $aledir . "<br>";
	echo "Media directory is set to: " . $mediadir;
?>
<p>

<form id="jquery-form" style="display:none">
	<label>Production:</label><input type="text" name="production" placeholder="production name" id="productioninput">
	<label>Director:</label><input type="text" name="director" placeholder="director's name">
	<label>Producer:</label> <input type="text" name="producer" placeholder="producer's name">
	<br>
	<label>Project Name:</label><select name="projectname">
	<option value="test">Test</option>
	<option value="test2">Test2</option>
	</select>
	<label>Task:</label><select name="task">
	<option selected>Dailies</option>
	<option>Editing</option>
	<option>Qtake</option>
	<option>VFX</option>
	<option>Pictures</option>
	<option>Documents</option>
	</select>
	<br>
	<input type="submit">
</form>

<div id="results"></div>

<?php
	global $aledir, $mediadir;

	$glob = glob($aledir . '/*.[aA][lL][eE]');			// find all ALE files
	// TO DO: handle spaces in ALE filenames 
	$sql = "DROP TABLE ale_batch;";
	mysqli_query($mysqli, $sql);						// reset batch table
	$sql = "CREATE TABLE ale_batch (
		id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		name TEXT );";
	mysqli_query($mysqli, $sql);						// create new index table each batch

	foreach($glob as $index => $file) {					// loop through the ALE files
		parseALE($file);
		//	moveFile($file);
		echo "<p>";
	}
	
	$sql = "SELECT COUNT(*) FROM ale_batch;";
	$result = mysqli_query($mysqli, $sql);
	$result = mysqli_fetch_array($result);
	$numFiles = $result['COUNT(*)'];

	function parseALE($filename) {						// store relevant data in MySQL
		global $mysqli;
		global $info;
		global $mediadir;
		global $projectname;
		global $mysqli;
		global $filelist;
		$reel		= substr($filename, -8, 4);
		$aleFile    = file_get_contents($filename);
		$rows        = explode("\n", $aleFile);			// separate rows
		$rows		 = array_slice($rows, 10, -1);		// delete header and footer
		$filelist .= substr($filename, -8);
		$filelist .= "<br>";

		$sql = "CREATE TABLE $reel (
			ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			filename TEXT,
			date DATE,
			scene TEXT,
			take TEXT,
			path TEXT,
			md5 TEXT );";
		mysqli_query($mysqli, $sql);							// create MySQL table for each ALE
	
		$sql = "INSERT INTO ale_batch SET
			name = \"$reel\"";
		mysqli_query($mysqli, $sql);


		foreach($rows as $row => $data)	{				// create SQL table for each ALE
			$columns = explode("\t", $data);
// TO DO: Error handling when table already exists
			$md5sum = md5_file($mediadir . "/" . pathinfo($columns[0], PATHINFO_FILENAME) . ".mov");
// TO DO: Error handling when preview file doesn't exist
			$sql = "INSERT INTO $reel SET
				filename = \"$columns[0]\",
				date = \"$columns[10]\",
				scene = \"$columns[9]\",
				take = \"$columns[4]\",
				path = \"" . $mediadir . "/" . pathinfo($columns[0], PATHINFO_FILENAME) . ".mov\",
				md5 = \"" . $md5sum . "\";";
			mysqli_query($mysqli, $sql);
		}
	}
	//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	function moveFile($x) {
		$path_parts = pathinfo($x);
		$old_path = $path_parts['dirname'];
		$new_path = $old_path . "/processed/";
		if (!file_exists($new_path)) {
			mkdir($new_path);
		}
	//	echo $path_parts['dirname'] . "/processed/" . $path_parts['basename'];
		rename($x, $new_path . $path_parts['basename']);
	}
	//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	function printVar($var) {
	echo '<pre>';
	var_dump($var);
	echo '</pre>';
}
?>

<div id="welcome">							<!-- this shows only at the beginning -->
The following files were found.  Press OK to go through them one at a time.<br>
<?php echo $filelist; ?>
<button id="start" onclick="startFunction()">OK</button>
</div>

</tbody>

</body>
<script>
var numFiles = <?php echo $numFiles; ?>;
</script>
</html>