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
            $xref_id = mysqli_real_escape_string($link, htmlspecialchars($_POST['xref_id']));
            $user_is = "sup";
            $subordinate_id = mysqli_real_escape_string($link, htmlspecialchars($_POST['subordinate_id']));
            $task_name = mysqli_real_escape_string($link, htmlspecialchars($_POST['task_name']));
            $due_date_text = mysqli_real_escape_string($link, htmlspecialchars($_POST['due_date']));
            $due_date = date_create($due_date_text);
            $due_time = mysqli_real_escape_string($link, htmlspecialchars($_POST['due_time']));
            $reward_points = mysqli_real_escape_string($link, htmlspecialchars($_POST['reward_points']));
            $late_points = mysqli_real_escape_string($link, htmlspecialchars($_POST['late_points']));
            $fail_points = mysqli_real_escape_string($link, htmlspecialchars($_POST['fail_points']));
            $subordinate_id = mysqli_real_escape_string($link, htmlspecialchars($_POST['subordinate_id']));
            $cur_date = date("Y-m-d");
            $cur_time = date("H:i", $_SERVER['REQUEST_TIME']);
            if ($user_data_time > time()) {
                include 'config.php';
                $link = mysqli_connect($host, $dbuser, $dbpass, $dbname);
                $user_id = $user_data->data->user_id;
                $user_type = $user_data->data->user_type;
                $offset_query = "select timezone_offset from users where user_id = $user_id";
                $offset_result = mysqli_query($link, $offset_query);
                $offset_row = mysqli_fetch_assoc($offset_result);
                $offset = $offset_row['timezone_offset'];
                $offset = $is_daylightsavings + $offset;
                $server_offset = -6;
                $server_offset = $server_offset + $is_daylightsavings;
                $offset = $server_offset - $offset;
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
                <h1>Add <?php echo $task_name ?></h1>
                <a href="index.php" class="ui-btn ui-shadow ui-corner-all ui-icon-home ui-btn-icon-notext">Home</a>
            </div>
            <div data-role="main" class="ui-content"><div class="ui-body ui-body-a ui-corner-all">
            <?php
                if (!isset($_POST['action'])){
                    // lets ensure this user can add a task and hasn't spoofed anything
                    $xref_query = "select * from user_xref join users on users.user_id = user_xref.subordinate_id where user_xref.xref_id = $xref_id and user_xref.superior_id = $user_id and user_xref.subordinate_id = $subordinate_id";
                    $xref_result = mysqli_query($link, $xref_query);
                    $xref_num_rows = mysqli_num_rows($xref_result);
                    if ($xref_num_rows == 1) {
                        $xref_row = mysqli_fetch_assoc($xref_result);
                        $sub_name = $xref_row['name'];
                        $sub_title = $xref_row['subordinate_title'];
                        echo ("<h2>Are you sure you want to add the task $task_name to your $sub_title $sub_name?</h2>");
                        echo ("<p>With the following information: <br />");
                        echo ("Due date: " . date_format($due_date,"m/d/Y") . "<br />");
                        echo ("Due time: $due_time<br />");
                        echo ("Reward Points: $reward_points<br />");
                        echo ("Late Points: $late_points<br />");
                        echo ("Fail Points: $fail_points</p>");
                        echo ("<form action='add_task.php' method='post'>");
                            echo ("<input type='hidden' name='xref_id' value='$xref_id'>");
                            echo ("<input type='hidden' name='superior_id' value='$user_id'>");
                            echo ("<input type='hidden' name='subordinate_id' value='$subordinate_id'>");
                            echo ("<input type='hidden' name='task_name' value='$task_name'>");
                            echo ("<input type='hidden' name='due_date' value='$due_date_text'>");
                            echo ("<input type='hidden' name='due_time' value='$due_time'>");
                            echo ("<input type='hidden' name='reward_points' value='$reward_points'>");
                            echo ("<input type='hidden' name='late_points' value='$late_points'>");
                            echo ("<input type='hidden' name='fail_points' value='$fail_points'>");
                            echo ("<input type='hidden' name='action' value='addit'>");
                            echo ("<input type='submit' name='submit' value='Submit'>");
                        echo ("</form>");
                    } else {
                        echo ("<h2>Fatal Error</h2>");
                    }
                }
                if (isset($_POST['action'])){
                    // lets ensure this user can add a task and hasn't spoofed anything
                    if ($offset < 0) {
                        $ins_offset = 0 - $offset;
                    }
                    if ($offset > 0) {
                        $ins_offset = -1 * abs($offset);
                    }
                    // $created = date("Y-m-d H:i:s", strtotime("+$offset hours", $cur_date . " " . $cur_time));
                    // $created_date = date("Y-m-d", strtotime($created));
                    // $created_time = date("H:i:s", strtotime($created));
                    $due = date("Y-m-d H:i:s", strtotime("+$offset hours", strtotime($due_date_text . " " . $due_time)));
                    $due_date_adj = date("Y-m-d", strtotime($due));
                    $due_time_adj = date("H:i:s", strtotime($due));

                    $xref_query = "select * from user_xref join users on users.user_id = user_xref.subordinate_id where user_xref.xref_id = $xref_id and user_xref.superior_id = $user_id and user_xref.subordinate_id = $subordinate_id";
                    $xref_result = mysqli_query($link, $xref_query);
                    $xref_num_rows = mysqli_num_rows($xref_result);
                    if ($xref_num_rows == 1) {
                        $xref_row = mysqli_fetch_assoc($xref_result);
                        $sub_email = $xref_row['email'];
                        $subject = 'You have a new task on Master Tasker';
                        $message = 'You have been given a new task on Master Tasker.  Check it out by going to:' . "\r\n" . "https://dewdevelopment.com/MasterTasker/" . "\r\n\r\n" . "If you are already logged in simply refresh the home page to see the task.";
                        $headers = 'From: Master Tasker Support <support@dewdevelopment.com>' . "\r\n" . 'Reply-To: support@dewdevelopment.com' . "\r\n" . 'X-Mailer: PHP/' . phpversion();
                        mail($sub_email, $subject, $message, $headers);

                        $ins_query = "insert into tasks (xref_id, task_name, created_date, created_time, due_date, due_time, user_id_given_by, user_id_assigned_to, completion_points, late_minus, fail_minus, last_action) VALUES ($xref_id, '$task_name', '$cur_date', '$cur_time', '$due_date_adj', '$due_time_adj', $user_id, $subordinate_id, $reward_points, $late_points, $fail_points, '$cur_date')";
                        $ins_result = mysqli_query($link, $ins_query);
                        echo ("<h2>Task Given</h2>");
                        echo ("<p>An email has been sent to notify the person that you have added the task.</p>");
                        echo ("<a href='index.php'>Return to main screen</a> or you can <a href='disp_connection.php?xref_id=$xref_id&user_is=sup'>Return to connection feed</a>");
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