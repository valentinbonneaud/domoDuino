<?php

require_once('menu.php');

session_start();
if(!isset($_SESSION['username']))
{
  echo '<meta http-equiv="refresh" content="0; url=login.php" />';
} 
else 
{

  $username = $_SESSION['username'];

  // Print the menu
  getMenu('sensors');

?>

    <!-- we load the ajax script -->
    <script src='domoticAjax.js'></script>
    <script src='domotic.js'></script>

    <!-- Content -->
    <div class="container">

    </div>

</body>
</html>

<?php } ?>
