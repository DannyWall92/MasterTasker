<?php
    ob_start();
        //If the HTTPS is not found to be "on"
        if(!isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] != "on") {
            //Tell the browser to redirect to the HTTPS URL.
            header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"], true, 301);
            //Prevent the rest of the script from executing.
            exit;
        }
        $expiry = time() -7000000 ;
        $data = (object) array( "user_id" => $user_id, "value2" => "i'll save whatever I want here" );
        $cookieData = (object) array( "data" => $data, "expiry" => $expiry );
        setcookie("mt_user_data", json_encode( $cookieData ), $expiry);
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
                <h1>You have Been Logged Out</h1>
            </div>
        
            <div data-role="main" class="ui-content"><div class="ui-body ui-body-a ui-corner-all">
                <h2>You should restart your browser to ensure you are fully logged out</h2>
            </div></div><!-- /content -->
        
            <div data-role="footer">
            <?php
                include 'footer.php';
            ?>
            </div>
        </div>
    </body>
</html>