<?php

// Check that a PHP session exist for this user, if not redirect to the login page

session_start();
if(!isset($_SESSION['username']))
{
  echo '<meta http-equiv="refresh" content="0; url=login.php" />';
  exit;
} 
else 
{
  $username = $_SESSION['username'];
  $ip = $_SESSION['ip'];
  $idUser = $_SESSION['idUser'];
}
?>
