<?php  


require_once('Config.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: index.php");

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) {
  die("ERROR: Unable to connect: " . $conn->connect_error);
} 


/*
if($_POST['MatriculaUF']=='1') {
	$query= mysql_query("UPDATE homevideos SET is_active = 1");
}
else {
	mysql_query("UPDATE homevideos SET is_active = '0'");
}
echo 'success';
*/

?>