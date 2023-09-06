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
                <h1>Update Titles</h1>
                <a href="index.php" class="ui-btn ui-shadow ui-corner-all ui-icon-home ui-btn-icon-notext">Home</a>
            </div>
            <div data-role="main" class="ui-content"><div class="ui-body ui-body-a ui-corner-all">
            <?php
            if (!isset($_POST['submit'])){
                $xref_id = mysqli_real_escape_string($link, htmlspecialchars($_GET['xref_id']));
                $get_query = "select * from user_xref join users on user_xref.subordinate_id = users.user_id where user_xref.xref_id = $xref_id and user_xref.superior_id = $user_id";
                $get_result = mysqli_query($link, $get_query);
                $get_num_rows = mysqli_num_rows($get_result);
                if ($get_num_rows == 1) {
                    $row = mysqli_fetch_assoc($get_result);
                    $sup_title = $row['superior_title'];
                    $sub_title = $row['subordinate_title'];
                    $sub_name = $row['name'];
                    echo ("<form action='title_update.php' method='post'>");
                        echo ("<input type='hidden' name='xr' value='$xref_id'>");
                        echo ("<input type='hidden' name='action' value='update'>");
                        echo ("<table>");
                            echo ("<tr><td><label for='sup_title'>Your Title:</label></td><td><input type='text' name='sup_title' id='sup_title' value='$sup_title'></td></tr>");
                            echo ("<tr><td><label for='sub_title'>$sub_name&apos;s Title:</label></td><td><input type='text' name='sub_title' id='sub_title' value='$sub_title'></td></tr>");
                            echo ("<tr><td colspan='2'><input type='submit' name='submit' value='Submit'></td></tr>");
                        echo ("</table>");
                    echo ("</form>");
                    echo ("<a href='index.php?ref=nochange'>Tap Here</a> if you want to leave the titles as they are and return to the home screen.");
                } else {
                    echo ("Fatal Error");
                }
            } else {
                $xref_id = mysqli_real_escape_string($link, htmlspecialchars($_POST['xr']));
                $action = mysqli_real_escape_string($link, htmlspecialchars($_POST['action']));
                if ($action === "update") {
                    $sup_title = mysqli_real_escape_string($link, htmlspecialchars($_POST['sup_title']));
                    $sub_title = mysqli_real_escape_string($link, htmlspecialchars($_POST['sub_title']));
                    $upd_query = "update user_xref set superior_title = '$sup_title', subordinate_title = '$sub_title' where xref_id = $xref_id and superior_id = $user_id";
                    $upd_result = mysqli_query($link, $upd_query);
                    echo ("Titles updated.  <a href='index.php?ref=titleupdate'>Tap Here</a> to return to the home screen");
                }
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