<!DOCTYPE html>
<html>
<body>

    
<?php
function csv_to_array($filename='', $delimiter=',', $country=NULL) {
	if(!file_exists($filename) || !is_readable($filename)) return FALSE;
	$header = NULL;
	$data = array();
	if (($handle = fopen($filename, 'r')) !== FALSE) {
		while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
			if(!$header) { 
                            $header = $row; 
                        } else { 
                            if (isset($country)) {
                                if ($row[0]=$country) { $data[] = array_combine($header, $row); }
                            } else {
                                $data[] = array_combine($header, $row);    
                            }
                        }
		}
		fclose($handle);
	}
	return $data;
}

function display_contents($data) {
    $colNames = array_keys(reset($data));
    echo'<table border="1">';
    echo'<tr>';
     //print the header
     foreach($colNames as $colName)      {
        echo "<th>$colName</th>";
     }
    echo'</tr>';
    //print the rows
    foreach($data as $row)    {
        echo "<tr>";
        foreach($colNames as $colName)         {
           echo "<td>".$row[$colName]."</td>";
        }
        echo "</tr>";
     }
    echo'</table>';
}

$target_file = 'data.csv';
// Check if image file is a actual image or fake image
if(isset($_POST['submit'])) {
    if (move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $target_file)) {
        $fh = fopen($target_file, 'rb');
        $data = fread($fh, filesize($target_file));
        $data = str_replace(chr(13) , chr(10) , $data, $count);
        fclose($fh);        
        if ($count>0) {
            echo ("non-linux line breaks have been replaced: " . $count . "<br />");
            $fh = fopen($target_file, 'wb'); 
            fwrite($fh, $data);
            fclose($fh);        
        }
        echo 'The file '. basename( $_FILES['fileToUpload']['name']). ' has been uploaded.<br />Here is the data that I received:<br /><br />';
        display_contents(csv_to_array('data.csv'));
    } else {
        echo 'Sorry, there was an error uploading your file.';
    }
} else {
?> 
<form action='upload.php' method='post' enctype='multipart/form-data'>
    Select csv file to upload:
    <input type='file' name='fileToUpload' id='fileToUpload'>
    <input type='submit' value='Upload File' name='submit'>
</form>

</body>
</html> 
<?php } ?>