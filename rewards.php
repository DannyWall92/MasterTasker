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
                <h1>Rewards</h1>
                <a href="index.php" class="ui-btn ui-shadow ui-corner-all ui-icon-home ui-btn-icon-notext">Home</a>
            </div>
            <div data-role="main" class="ui-content"><div class="ui-body ui-body-a ui-corner-all">
            <div style='display: flex;'>
            <div style='width: 70%;'>
                <?php
                if (!isset($_GET['submit'])){
                    $xref_id = mysqli_real_escape_string($link, htmlspecialchars($_GET['xref_id']));
                    $sub_title = mysqli_real_escape_string($link, htmlspecialchars($_GET['sub']));
                    echo ("<h2>Add Reward</h2>");
                    echo ("<form action='rewards.php' method='get'>");
                        echo ("<input type='hidden' name='xr' value='$xref_id'>");
                        echo ("<input type='hidden' name='action' value='add'>");
                        echo ("<input type='hidden' name='sub_title' value='$sub_title'>");
                        echo ("<table>");
                            echo ("<tr><td><label for='reward_points'>Number of Points:</label></td><td><input type='number' name='reward_points' id='reward_points' value='0'></td></tr>");
                            echo ("<tr><td><label for='reward_desc'>What will the reward be:</label></td><td><input type='text' name='reward_desc' id='reward_desc' placeholder='What does $sub_title receive'></td></tr>");
                            echo ("<tr><td colspan='2'><input type='submit' name='submit' value='Submit'></td></tr>");
                        echo ("</table>");
                    echo ("</form>");
                } else {
                    $xref_id = mysqli_real_escape_string($link, htmlspecialchars($_GET['xr']));
                    $sub_title = mysqli_real_escape_string($link, htmlspecialchars($_GET['sub_title']));
                    $action = mysqli_real_escape_string($link, htmlspecialchars($_GET['action']));
                    if ($action === "add") {
                        $num_points = mysqli_real_escape_string($link, htmlspecialchars($_GET['reward_points']));
                        $reward_desc = mysqli_real_escape_string($link, htmlspecialchars($_GET['reward_desc']));
                        $check_query = "select * from rewards join user_xref on rewards.xref_id = user_xref.xref_id where user_xref.xref_id = $xref_id and user_xref.superior_id = $user_id and num_points = $num_points";
                        $check_result = mysqli_query($link, $check_query);
                        $num_rows = mysqli_num_rows($check_result);
                        if ($num_rows == 0) {
                            $ins_query = "insert into rewards (xref_id, num_points, reward_desc) VALUES ($xref_id, $num_points, '$reward_desc')";
                            $ins_result = mysqli_query($link, $ins_query);
                        } else {
                            echo ("<p style='color: red'>Reward not added, identical number of points</p>");
                        }
                        echo ("<form action='rewards.php' method='get'>");
                            echo ("<input type='hidden' name='xr' value='$xref_id'>");
                            echo ("<input type='hidden' name='action' value='add'>");
                            echo ("<input type='hidden' name='sub_title' value='$sub_title'>");
                            echo ("<table>");
                                echo ("<tr><td><label for='reward_points'>Number of Points:</label></td><td><input type='number' name='reward_points' id='reward_points' value='0'></td></tr>");
                                echo ("<tr><td><label for='reward_desc'>What will the reward be:</label></td><td><input type='text' name='reward_desc' id='reward_desc' placeholder='What does $sub_title receive'></td></tr>");
                                echo ("<tr><td colspan='2'><input type='submit' name='submit' value='Submit'></td></tr>");
                            echo ("</table>");
                        echo ("</form>");
                    }
                    if ($action === "delete") {
                        $reward_id = mysqli_real_escape_string($link, htmlspecialchars($_GET['r']));
                        $check_query = "select * from rewards join user_xref on rewards.xref_id = user_xref.xref_id where user_xref.xref_id = $xref_id and user_xref.superior_id = $user_id and reward_id = $reward_id";
                        $check_result = mysqli_query($link, $check_query);
                        $num_rows = mysqli_num_rows($check_result);
                        if ($num_rows == 1) {
                            $row = mysqli_fetch_assoc($check_result);
                            $reward_desc = $row['reward_desc'];
                            echo ("<h2>Really Delete This Reward?</h2>");
                            echo ("<p>$reward_desc");
                            echo ("<br /><a href='rewards.php?r=$reward_id&action=gone&xr=$xref_id&sub_title=$sub_title&submit=yes' class='ui-btn ui-mini ui-btn-inline ui-btn-icon-left ui-icon-delete'>Yes Delete It</a>");
                            echo ("<a href='rewards.php?r=$reward_id&action=delete&xr=$xref_id&sub_title=$sub_title&submit=yes' class='ui-btn ui-mini ui-btn-inline ui-btn-icon-left ui-icon-heart'>No Keep It</a></p>");
                        } else {
                            echo ("Fatal Error");
                        }
                    }
                    if ($action === "gone") {
                        $reward_id = mysqli_real_escape_string($link, htmlspecialchars($_GET['r']));
                        $check_query = "select * from rewards join user_xref on rewards.xref_id = user_xref.xref_id where user_xref.xref_id = $xref_id and user_xref.superior_id = $user_id and reward_id = $reward_id";
                        $check_result = mysqli_query($link, $check_query);
                        $num_rows = mysqli_num_rows($check_result);
                        if ($num_rows == 1) {
                            $del_query = "delete from rewards where reward_id = $reward_id";
                            $del_result = mysqli_query($link, $del_query);
                            echo ("<p style='color: red'>Reward Deleted</p>");
                            echo ("<form action='rewards.php' method='get'>");
                                echo ("<input type='hidden' name='xr' value='$xref_id'>");
                                echo ("<input type='hidden' name='action' value='add'>");
                                echo ("<input type='hidden' name='sub_title' value='$sub_title'>");
                                echo ("<table>");
                                    echo ("<tr><td><label for='reward_points'>Number of Points:</label></td><td><input type='number' name='reward_points' id='reward_points' value='0'></td></tr>");
                                    echo ("<tr><td><label for='reward_desc'>What will the reward be:</label></td><td><input type='text' name='reward_desc' id='reward_desc' placeholder='What does $sub_title receive'></td></tr>");
                                    echo ("<tr><td colspan='2'><input type='submit' name='submit' value='Submit'></td></tr>");
                                echo ("</table>");
                            echo ("</form>");
                        } else {
                            echo ("Fatal Error");
                        }
                    }
                    if ($action === "edit"){
                        $reward_id = mysqli_real_escape_string($link, htmlspecialchars($_GET['r']));
                        $check_query = "select * from rewards join user_xref on rewards.xref_id = user_xref.xref_id where user_xref.xref_id = $xref_id and user_xref.superior_id = $user_id and reward_id = $reward_id";
                        $check_result = mysqli_query($link, $check_query);
                        $num_rows = mysqli_num_rows($check_result);
                        if ($num_rows == 1) {
                            $row = mysqli_fetch_assoc($check_result);
                            $num_points = $row['num_points'];
                            $reward_desc = $row['reward_desc'];
                            echo ("<h2>Edit Reward</h2>");
                            echo ("<form action='rewards.php' method='get'>");
                                echo ("<input type='hidden' name='xr' value='$xref_id'>");
                                echo ("<input type='hidden' name='action' value='update'>");
                                echo ("<input type='hidden' name='sub_title' value='$sub_title'>");
                                echo ("<input type='hidden' name='r' value='$reward_id'>");
                                echo ("<table>");
                                    echo ("<tr><td><label for='reward_points'>Number of Points:</label></td><td><input type='number' name='reward_points' id='reward_points' value='$num_points'></td></tr>");
                                    echo ("<tr><td><label for='reward_desc'>What will the reward be:</label></td><td><input type='text' name='reward_desc' id='reward_desc' value='$reward_desc'></td></tr>");
                                    echo ("<tr><td colspan='2'><input type='submit' name='submit' value='Submit'></td></tr>");
                                echo ("</table>");
                            echo ("</form>");
                        } else {
                            echo ("Fatal Error");
                        }
                    }
                    if ($action === "update") {
                        $reward_id = mysqli_real_escape_string($link, htmlspecialchars($_GET['r']));
                        $num_points = mysqli_real_escape_string($link, htmlspecialchars($_GET['reward_points']));
                        $reward_desc = mysqli_real_escape_string($link, htmlspecialchars($_GET['reward_desc']));
                        $check_query = "select * from rewards join user_xref on rewards.xref_id = user_xref.xref_id where user_xref.xref_id = $xref_id and user_xref.superior_id = $user_id and reward_id = $reward_id";
                        $check_result = mysqli_query($link, $check_query);
                        $num_rows = mysqli_num_rows($check_result);
                        if ($num_rows == 1) {
                            $row = mysqli_fetch_assoc($check_result);
                            $upd_query = "update rewards set num_points = $num_points, reward_desc = '$reward_desc' where reward_id = $reward_id";
                            $upd_result = mysqli_query($link, $upd_query);
                            echo ("<h2>Create Reward</h2>");
                            echo ("<form action='rewards.php' method='get'>");
                                echo ("<input type='hidden' name='xr' value='$xref_id'>");
                                echo ("<input type='hidden' name='action' value='add'>");
                                echo ("<input type='hidden' name='sub_title' value='$sub_title'>");
                                echo ("<table>");
                                    echo ("<tr><td><label for='reward_points'>Number of Points:</label></td><td><input type='number' name='reward_points' id='reward_points' value='0'></td></tr>");
                                    echo ("<tr><td><label for='reward_desc'>What will the reward be:</label></td><td><input type='text' name='reward_desc' id='reward_desc' placeholder='What does $sub_title receive'></td></tr>");
                                    echo ("<tr><td colspan='2'><input type='submit' name='submit' value='Submit'></td></tr>");
                                echo ("</table>");
                            echo ("</form>");
                        } else {
                            echo ("Fatal Error");
                        }
                    }
                }
                echo ("</div>");
                // Display rewards
                $get_query = "select * from rewards join user_xref on rewards.xref_id = user_xref.xref_id where user_xref.xref_id = $xref_id and user_xref.superior_id = $user_id order by num_points";
                $get_result = mysqli_query($link, $get_query);
                $num_rows = mysqli_num_rows($get_result);
                if ($num_rows > 0){
                    echo ("<div style='width:30%'>");
                    while ($row = mysqli_fetch_assoc($get_result)){
                        $reward_points = $row['num_points'];
                        $reward_desc = $row['reward_desc'];
                        $reward_id = $row['reward_id'];
                        echo ("<p style='border: 1px solid black; padding:5px;'>When your $sub_title reaches $reward_points points you will $reward_desc");
                        echo ("<br /><a href='rewards.php?r=$reward_id&action=edit&xr=$xref_id&sub_title=$sub_title&submit=yes' class='ui-btn ui-mini ui-btn-inline ui-btn-icon-left ui-icon-edit'>Edit Reward</a>");
                        echo ("<a href='rewards.php?r=$reward_id&action=delete&xr=$xref_id&sub_title=$sub_title&submit=yes' class='ui-btn ui-mini ui-btn-inline ui-btn-icon-left ui-icon-delete'>Delete Reward</a></p>");
                    }
                    echo ("</div>");
                }
                ?>
            </div></div></div>
            <div data-role="footer">
            <?php
                include 'footer.php';
            ?>
            </div>
        </div>
    </body>
</html>