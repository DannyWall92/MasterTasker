<?php
    ob_start();
        //If the HTTPS is not found to be "on"
        if(!isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] != "on") {
            //Tell the browser to redirect to the HTTPS URL.
            header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"], true, 301);
            //Prevent the rest of the script from executing.
            exit;
        }
    
        include 'config.php';
        $link = mysqli_connect($host, $dbuser, $dbpass, $dbname);
        $email = $_POST['email'];
        $email = mysqli_real_escape_string($link, htmlspecialchars($email));
        $password = $_POST['password'];
        $query = "select user_id, user_type, password from users where email like '$email'";
        $result = mysqli_query($link, $query);
        $row = mysqli_fetch_assoc($result);
        $user_id = $row['user_id'];
        $hash = $row['password'];
        $user_type = $row['user_type'];
        if (password_verify($password, $hash)) {
            $expiry = time() + (86400*30);
            $data = (object) array( "user_id" => $user_id, "user_type" => $user_type );
            $cookieData = (object) array( "data" => $data, "expiry" => $expiry );
            setcookie( "mt_user_data", json_encode( $cookieData ), $expiry );
            // setcookie("user_id", $user_id, time() + (86400 * 30), "/");
            $success = "yes";    
        } else {
            $success = "no";
        }
    ob_end_flush();
?>
<html>
    <head>
        <title>Master Tasker</title>
        <meta name="viewport" content="width=device-width, initial-scale=1"> 
        <link rel="stylesheet" href="https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.css" />
        <script src="https://code.jquery.com/jquery-1.11.1.min.js"></script>
        <script src="https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js"></script>
        <link rel="stylesheet" href="themes/MT3.min.css" />
        <link rel="stylesheet" href="themes/jquery.mobile.icons.min.css" />
    </head>
    <body>
        <div data-role="page">
            <div data-role="header">
                <h1>Login Page</h1>
                <a href="index.php" class="ui-btn ui-shadow ui-corner-all ui-icon-home ui-btn-icon-notext">Home</a>
            </div>
            <div data-role="main" class="ui-content"><div class="ui-body ui-body-a ui-corner-all">
            <?php
            /*
            echo ($user_id . "<BR /><br />" . $password . "<br />" . $hash . "<br /><br />");
            echo ($query . "<br /><br />");
            echo ($source . " " . $cio . " " . $howmany . " " . $title);
            echo ("<br /><br />");
            var_dump($_REQUEST);
            echo ("<br /><br />");
            var_dump($_POST);
            */
            if ($success == "yes") {
                echo ("<h1>Login Successful</h1>");
                echo ("<a href='index.php'>Proceed to home screen</a>");
            }
            if ($success == "no") {
                echo ("<h1>Login Unsuccessful</h1>");
                echo ("<a href='index.php'>Login again</a>");
            }
            ?>
            </div></div>
            <div data-role="footer">
            <?php
                include 'footer.php';
            ?>
            </div>
        </div>
    </body>
</html>