<?php
    ob_start();
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        //If the HTTPS is not found to be "on"
        if(!isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] != "on") {
            //Tell the browser to redirect to the HTTPS URL.
            header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"], true, 301);
            //Prevent the rest of the script from executing.
            exit;
        }
    
        if(!isset($_COOKIE["mt_user_data"])) {
            header("Location: login.php");
        } else {
            include 'config.php';
            $link = mysqli_connect($host, $dbuser, $dbpass, $dbname);
            $user_data = json_decode($_COOKIE["mt_user_data"]);
            $user_data_time = $user_data->expiry;
            $xref_id = mysqli_real_escape_string($link, htmlspecialchars($_GET['xref_id']));
            if ($user_data_time > time()) {
                include 'config.php';
                $link = mysqli_connect($host, $dbuser, $dbpass, $dbname);
                $user_id = $user_data->data->user_id;
                $user_type = $user_data->data->user_type;
            } else {
                header("Location: login.php");
            }

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
                <h1>Change Points</h1>
                <a href="index.php" class="ui-btn ui-shadow ui-corner-all ui-icon-home ui-btn-icon-notext">Home</a>
            </div>
            <div data-role="main" class="ui-content"><div class="ui-body ui-body-a ui-corner-all">
                <?php
                    $get_query = "select * from user_xref join users on user_xref.subordinate_id = users.user_id where user_xref.superior_id = $user_id and xref_id = $xref_id";
                    $get_result = mysqli_query($link, $get_query);
                    $num_rows = mysqli_num_rows($get_result);
                    if ($num_rows == 1) {
                        $action = mysqli_real_escape_string($link, htmlspecialchars($_GET['action']));
                        $row = mysqli_fetch_assoc($get_result);
                        $sub_name = $row['name'];
                        $sub_title = $row['subordinate_title'];
                        if ($action === "edit") {
                            $points = $row['subordinate_points'];
                            echo ("<form action='points.php' method='GET'>");
                                echo ("<input type='hidden' name='xref_id' value='$xref_id'>");
                                echo ("<input type='hidden' name='action' value='update'>");
                                echo ("<table>");
                                    echo ("<tr><td><label for='points'>Your $sub_title $sub_name has</label></td><td><input type='number' name='points' id='points' value='$points'></td></tr>");
                                    echo ("<tr><td colspan='2'><input type='submit' name='submit' value='Submit'></td></tr>");
                                echo ("</table>");
                            echo ("</form>");
                        }
                        if ($action === "update"){
                            $points = mysqli_real_escape_string($link, htmlspecialchars($_GET['points']));
                            $upd_query = "update user_xref set subordinate_points = $points where xref_id = $xref_id";
                            $upd_result = mysqli_query($link, $upd_query);
                            echo ("<h2>Points Updated</h2>");
                            echo ("<p>Your $sub_title $sub_name now has $points reward points</p>");
                            echo ("<p><a href=disp_connection.php?xref_id=$xref_id&user_is=sup>Return to $sub_name&apos;s connection page</a> or else you can <a href='index.php?ref=pointsupdated'>Return to the main screen</a></p>");
                        }
                    } else {
                        echo ("Fatal Error");
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