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
            $user_data = json_decode($_COOKIE["mt_user_data"]);
            $user_data_time = $user_data->expiry;
            if ($user_data_time > time()) {
                include 'config.php';
                $link = mysqli_connect($host, $dbuser, $dbpass, $dbname);
                $user_id = $user_data->data->user_id;
                $action = mysqli_real_escape_string($link, htmlspecialchars($_GET['action']));
                if ($action === "confirm") {
                    $xref_id = mysqli_real_escape_string($link, htmlspecialchars($_GET['xref_id']));
                    $user_is = mysqli_real_escape_string($link, htmlspecialchars($_GET['user_is']));
                    if ($user_is === "sub") {
                        $upd_query = "update user_xref set connection_confirmed = 1 where xref_id = $xref_id and subordinate_id = $user_id";
                        $upd_result = mysqli_query($link, $upd_query);
                        $get_query = "select * from user_xref join users on user_xref.superior_id = users.user_id WHERE user_xref.xref_id=$xref_id and user_xref.subordinate_id=$user_id";
                        $get_result = mysqli_query($link, $get_query);
                        $num_rows = mysqli_num_rows($get_result);
                        if ($num_rows == 1) {
                            $row = mysqli_fetch_assoc($get_result);
                            $connection_title = $row['superior_title'];
                            $connection_name = $row['name'];
                            $status = "confirmed";
                        }
                    }
                    if ($user_is === "sup") {
                        $upd_query = "update user_xref set connection_confirmed = 1 where xref_id = $xref_id and superior_id = $user_id";
                        $upd_result = mysqli_query($link, $upd_query);
                        $get_query = "select * from user_xref join users on user_xref.subordinate_id = users.user_id WHERE user_xref.xref_id=$xref_id and user_xref.superior_id=$user_id";
                        $get_result = mysqli_query($link, $get_query);
                        $num_rows = mysqli_num_rows($get_result);
                        if ($num_rows == 1) {
                            $row = mysqli_fetch_assoc($get_result);
                            $connection_title = $row['subordinate_title'];
                            $connection_name = $row['name'];
                            $title = $row['superior_title'];
                            $status = "confirmed";
                        }
                    }
                }
                if ($action === "noconfirm") {
                    $xref_id = mysqli_real_escape_string($link, htmlspecialchars($_GET['xref_id']));
                    $user_is = mysqli_real_escape_string($link, htmlspecialchars($_GET['user_is']));
                }
                if ($action === "delete") {
                    $xref_id = mysqli_real_escape_string($link, htmlspecialchars($_GET['xref_id']));
                    $user_is = mysqli_real_escape_string($link, htmlspecialchars($_GET['user_is']));
                    if ($user_is === "sub") {
                        $del_query = "delete from user_xref where xref_id = $xref_id and subordinate_id = $user_id";
                        $del_result = mysqli_query ($link, $del_query);
                    }
                    if ($user_is === "sup") {
                        $del_query = "delete from user_xref where xref_id = $xref_id and superior_id = $user_id";
                        $del_result = mysqli_query ($link, $del_query);
                    }
                }
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
                <h1>Connection Status</h1>
                <a href="index.php" class="ui-btn ui-shadow ui-corner-all ui-icon-home ui-btn-icon-notext">Home</a>
            </div>
            <div data-role="main" class="ui-content"><div class="ui-body ui-body-a ui-corner-all">
                <?php
                    if ($status === "confirmed") {
                        echo ("<p>Connection to $connection_title $connection_name is confirmed you will now see this person in your home screen feed.</p>");
                        if ($user_is == "sup"){
                            echo ("<p>As the $title in this connection you will have the ability to modify the title for both you and your $connection_title</p>");
                        }
                        echo ("<p>You can <a href='index.php?ref=yes'>return to the main screen</a> or you can also <a href='connect.php'>connect to someone else</a></P>");
                        // commented out exists for debug only: echo ("<p>$upd_query<br />$get_query</p>");
                    } 
                    if ($action === "noconfirm") {
                        echo ("<p>You are about to remove the connection made, are you sure?</p>");
                        echo ("<a href='connect_confirm.php?action=delete&user_is=$user_is&xref_id=$xref_id' class='ui-btn ui-btn-icon-left ui-icon-forbidden'>Yes, Delete This Connection</a>");
                        echo ("<a href='index.php?ref=yes'>Actually, send me back to the home screen and do not do anything yet</a>");
                    }
                    if ($action === "delete") {
                        echo ("<p>Connection removed.  You can <a href='index.php?ref=yes'>return to the home screen</a> and this user will not show up in your feed.</p>");
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