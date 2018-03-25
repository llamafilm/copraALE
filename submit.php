<?php
	if(!$_POST['counter']) {					// handle first file
		$_POST['counter'] = 1;
	}
	parse_str($_POST['main'], $fields);		// production, director, producer, projectname, task
	
	// Create MySQL connection
	$mysqli = @mysqli_connect("localhost","elliott_copra","copra","elliott_copraALE");
	// Check connection
	if (mysqli_connect_errno($con)) {
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
		exit();
	}
	$sql = "SELECT name FROM ale_batch WHERE id = " . $_POST['counter'] . ";";
	$current = mysqli_fetch_array(mysqli_query($mysqli, $sql));
//	echo $current['name'];
	$sql = "SELECT filename, date, scene, take, path, md5 FROM " . $current['name'] . ";";
	$result = mysqli_query($mysqli, $sql);

?>
<h2>Clips:</h2>
<table class="gridtable" id="cliptable">
<thead>
<tr>
<th>Filename</th>
<th>Creation Date</th>
<th>Scene</th>
<th>Take</th>
<th>preview</th>
<th>md5</th>
</tr>
</thead>
<tbody>
<?php
	while($row = mysqli_fetch_array($result)) {
		echo "<tr>";
		echo '<td>' . $row['filename'] . "</td>";
		echo '<td>' . $row['date'] . "</td>";
		echo '<td>' . $row['scene'] . "</td>";
		echo '<td>' . $row['take'] . "</td>";
		echo '<td>' . $row['path'] . "</td>";
		echo '<td>' . $row['md5'] . "</td>";
		echo '</tr>';
	}
	mysqli_data_seek($result, 0);	// reset for the next while loop
	
	if($_POST['main']) {		// do not write XML at first run
		writeXML();
	}
	function writeXML() {
		global $info;
		global $fields;			// projectname, production, producer, director, task
		global $current;
		global $result;
		
		$dom				= new DOMDocument("1.0", "UTF-8");
		$dom_root			= $dom->createElement("copraimport");
		$dom->appendChild($dom_root);
		$dom_xmlversion		= $dom->createAttribute("xmlversion");
		$dom_root->appendChild($dom_xmlversion);
		$dom_item			= $dom->createTextNode("1.0");
		$dom_xmlversion->appendChild($dom_item);
		$dom_project		= $dom->createElement("project");
		$dom_root->appendChild($dom_project);
		$dom_pname			= $dom->createAttribute("name");
		$dom_project->appendChild($dom_pname);
		$dom_item			= $dom->createTextNode($fields['projectname']);
		$dom_pname->appendChild($dom_item);
		$dom_item		= $dom->createElement("production", $fields['production']);
		$dom_project->appendChild($dom_item);
		$dom_item			= $dom->createElement("producer", $fields['producer']);
		$dom_project->appendChild($dom_item);
		$dom_item			= $dom->createElement("director", $fields['director']);
		$dom_project->appendChild($dom_item);
		$dom_tasks			= $dom->createElement("tasks");
		$dom_project->appendChild($dom_tasks);
		$dom_task			= $dom->createElement("task");
		$dom_tasks->appendChild($dom_task);
		$dom_tname			= $dom->createAttribute("name");
		$dom_task->appendChild($dom_tname);
		$dom_item			= $dom->createTextNode($fields['task']);
		$dom_tname->appendChild($dom_item);
		$dom_ttype			= $dom->createAttribute("type");
		$dom_task->appendChild($dom_ttype);
		$dom_item			= $dom->createTextNode("1");
		$dom_ttype->appendChild($dom_item);
		$dom_reels			= $dom->createElement("reels");
		$dom_task->appendChild($dom_reels);
		$dom_reel			= $dom->createElement("reel");
		$dom_reels->appendChild($dom_reel);
		$dom_rname			= $dom->createAttribute("name");
		$dom_reel->appendChild($dom_rname);
		$dom_item			= $dom->createTextNode($current['name']);
		$dom_rname->appendChild($dom_item);
		$dom_takes			= $dom->createElement("takes");
		$dom_reel->appendChild($dom_takes);

		while($row = mysqli_fetch_array($result)) {
			$dom_take			= $dom->createElement("take");
			$dom_takes->appendChild($dom_take);
			$dom_tname			= $dom->createAttribute("name");
			$dom_take->appendChild($dom_tname);
			$dom_item			= $dom->createTextNode($row['take']);
			$dom_tname->appendChild($dom_item);
			$dom_fname			= $dom->createElement("filename");
			$dom_take->appendChild($dom_fname);
			$dom_item			= $dom->createTextNode($row['filename']);
			$dom_fname->appendChild($dom_item);
			$dom_camroll		= $dom->createElement("camroll");
			$dom_take->appendChild($dom_camroll);
			$dom_item			= $dom->CreateTextNode($current['name']);
			$dom_camroll->appendChild($dom_item);
			$dom_shot			= $dom->CreateElement("shot");
			$dom_take->appendChild($dom_shot);
			$dom_item			= $dom->CreateTextNode($row['scene']);
			$dom_shot->appendChild($dom_item);
			$dom_date			= $dom->CreateElement("date");
			$dom_take->appendChild($dom_date);
			$dom_item			= $dom->CreateTextNode($row['date']);
			$dom_date->appendChild($dom_item);
			$dom_file			= $dom->CreateElement("preview_file");
			$dom_take->appendChild($dom_file);
			$dom_item			= $dom->CreateTextNode($row['path']);
			$dom_file->appendChild($dom_item);
			$dom_md5			= $dom->CreateElement("preview_md5hash");
			$dom_take->appendChild($dom_md5);
			$dom_item			= $dom->CreateTextNode($row['md5']);
			$dom_md5->appendChild($dom_item);
		}

		$dom->formatOutput = TRUE; // this adds spaces, new lines and makes the XML more readable format.

		// save and display tree
//		$dom->save("/tmp/" . $current['name'] . ".xml");
		$dom->save("/tmp/test.xml");
	}
	function printVar($var) {
		echo '<pre>';
		var_dump($var);
		echo '</pre>';
	}
?>
