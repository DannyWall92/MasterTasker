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

        $email = mysqli_real_escape_string($link, htmlspecialchars($_GET['email']));
        $query1 = "update users set em_confirmed = 1 where email like '$email'";
        $result = mysqli_query($link, $query1);

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
                    <h1>Email Confirmed</h1>
                </div>
                <div data-role="main" class="ui-content"><div class="ui-body ui-body-a ui-corner-all">
                    <h2>Your Email Address Been Confirmed</h2>
                    <p>You can <a href="login.php">click here now to login</a> and use your Master Tracker account normally.</p>
                </div></div>
                <div data-role="footer">
                <?php
                    include 'footer.php';
                ?>
                </div>
            </div>
    </body>