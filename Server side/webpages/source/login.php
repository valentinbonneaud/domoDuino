<?php
session_start();
session_destroy();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Login</title>
        <link rel="stylesheet" type="text/css" href="../bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="css/login.css">
        <link href="../bootstrap/css/bootstrap-responsive.css" rel="stylesheet">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
        <script src="login.js"></script>
        <script src="loginAjax.js"></script>
    </head>

  <body>
    <div class="container">
          <form class="form-signin">
            <form class="form-horizontal">
                <div>
                    <legend>Login</legend>
                </div>
                <div class="control-group" id = "usernameGroup">
                    <label class="control-label" for="inputUsername" style = "font-size:16px">Username</label>
                    <div class="controls">
                        <input type="text" id="inputUsername" placeholder="Username" class = "input-block-level">
                    </div>
                </div>
                <div class="control-group" id = "passwordGroup">
                    <label class="control-label" for="inputPassword" style = "font-size:16px">Password</label>
                    <div class="controls">
                        <input type="password" id="inputPassword" placeholder="Password" class = "input-block-level">
                    </div>
                </div>
                <div class = "control-group">
                    <ul class = "inline">
                        <li>
                            <div class="controls">
                                <button class="btn btn-large btn-inverse" id = "loginButton">Login</button>
                            </div>
                        </li>
                        <li>   
                            <p class="text-error hidden" id = "errorMessage">Invalid account.</p>
                        </li>
                    </ul>   
                </div>
            </form>
      </form>  
    </div> 
  </body>
</html>
