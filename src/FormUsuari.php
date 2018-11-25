<?php

/** 
 * FormUsuari.php
 *
 * Formulari de l'usuari.
 */

require_once('Config.php');
require_once('lib/LibForms.php');

session_start();
if (!isset($_SESSION['usuari_id'])) 
	header("Location: index.html");

$conn = new mysqli($CFG->Host, $CFG->Usuari, $CFG->Password, $CFG->BaseDades);
if ($conn->connect_error) {
  die("ERROR: Unable to connect: " . $conn->connect_error);
} 

$frm = new FormRecerca($conn);
$frm->Titol = 'Usuaris';
$frm->SQL = 'SELECT * FROM USUARI';
$frm->Camps = 'nom, cognom1, cognom2, username';
$frm->Descripcions = 'Nom, 1r cognom, 2n cognom, Usuari';
$frm->GeneraHTML();

?>
