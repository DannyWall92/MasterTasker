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
            $user_is = mysqli_real_escape_string($link, htmlspecialchars($_GET['user_is']));
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
        <?php
            if ($user_is == "sup") {
                $con_query = "select * from user_xref join users on user_xref.subordinate_id = users.user_id WHERE user_xref.xref_id=$xref_id and user_xref.superior_id=$user_id";
            }
            if ($user_is == "sub") {
                $con_query = "select * from user_xref join users on user_xref.superior_id = users.user_id WHERE user_xref.xref_id=$xref_id and user_xref.subordinate_id=$user_id";
            }
            $con_result = mysqli_query($link, $con_query);
            $num_rows = mysqli_num_rows($con_result);
            if ($num_rows == 1){
                $con_row = mysqli_fetch_assoc($con_result);
                $connection_name = $con_row['name'];
                $subordinate_id = $con_row['subordinate_id'];
                $subordinate_title = $con_row['subordinate_title'];
                $superior_id = $con_row['superior_id'];
                $superior_title = $con_row['superior_title'];
                $xref_id = $con_row['xref_id'];
                $points = $con_row['subordinate_points'];
            }
        ?>
        <div data-role="page">
            <div data-role="header">
                <?php
                    if ($user_is == "sup") {
                        echo ("<h1>$subordinate_title $connection_name has earned $points reward points</h1>");
                    }
                    if ($user_is == "sub") {
                        echo ("<h1>You have a total $points reward points earned from $superior_title $connection_name</h1>");
                    }
                ?>
                <a href="index.php?ref=fromdisp" class="ui-btn ui-shadow ui-corner-all ui-icon-home ui-btn-icon-notext">Home</a>
            </div>
            <div data-role="main" class="ui-content">
            <?php
                if ($user_is == "sup"){
                    echo ("<div class='ui-body ui-body-a ui-corner-all'><div data-role='navbar'><ul>");
                        echo ("<li><a href='title_update.php?xref_id=$xref_id' class='ui-btn ui-btn-icon-top ui-icon-edit'>Update Titles</a>");
                        echo ("<li><a href='rewards.php?xref_id=$xref_id&sub=$subordinate_title' class='ui-btn ui-btn-icon-top ui-icon-heart'>Setup Rewards</a>");
                        echo ("<li><a href='points.php?xref_id=$xref_id&action=edit' class='ui-btn ui-btn-icon-top ui-icon-plus'>Change Points</a>");
                    echo ("</ul></div></div>");
                }
            ?>

            <div style='display: flex;'>
            <div style='width: 60%;'>
                <?php
                    if ($user_is == "sup") {            
                        echo ("<div class='ui-body ui-body-a ui-corner-all'><h2>Add Task</h2>");
                        $date = date('Y-m-d');
                ?>
                        <p><strong>NOTE:</strong> You add a task with the due date/time based on your local time.  If the person you are giving the task to is in a different time zone the system will translate to their time when they see the task.</p>
                        <form action="add_task.php" method="post">
                            <input type="hidden" name="subordinate_id" value="<?php echo $subordinate_id ?>">
                            <input type="hidden" name="xref_id" value="<?php echo $xref_id ?>">
                            <table style="width: 100%;">
                                <tr><td><label for="task_name">Task Name:</td><td><input type="text" name="task_name" id="task_name"></td></tr>
                                <tr><td><label for="due_date">Due Date:</td><td><input type="date" name="due_date" id="due_date" value="<?php echo $date ?>"></td></tr>
                                <tr><td><label for="due_time">Due Time:</td><td><input type="time" name="due_time" id="due_time"></td></tr>
                                <tr><td><label for="reward_points">Reward Points For Completing This Task:</td><td><input type="number" name="reward_points" id="reward_points" value='0'></td></tr>
                                <tr><td><label for="late_points">Points to take away for getting done late:</td><td><input type="number" name="late_points" id="late_points" value='0'></td></tr>
                                <tr><td><label for="fail_points">Points to take away for failure to accomplish:</td><td><input type="number" name="fail_points" id="fail_points" value='0'></td></tr>
                                <tr><td colspan='2'><input type="submit" name="submit" value="Submit"></td></tr>
                            </table>
                        </form></div><br />
                <?php
                        echo ("<div class='ui-body ui-body-a ui-corner-all'><h2>Previous Tasks You Assigned</h2>");
                        $tasks_query = "select * from tasks where user_id_given_by = $user_id and user_id_assigned_to = $subordinate_id";
                        $tasks_result = mysqli_query($link, $tasks_query);
                        $tasks_num_rows = mysqli_num_rows($tasks_result);
                        if ($tasks_num_rows > 0) {
                            while ($task_row = mysqli_fetch_assoc($tasks_result)){
                                $task_id = $task_row['task_id'];
                                $task_name = $task_row['task_name'];
                                echo ("<strong>$task_name:</strong> ");
                                $completion_flag = $task_row['completion_flag'];
                                $due_date = $task_row['due_date'];
                                // $due_date = date_create($due_date);
                                // $due_date = date_format($due_date,"F d, Y");
                                $due_time = $task_row['due_time'];
                                // $due_time = date ('g:i a',strtotime($due_time));
                                
                                $due = date("Y-m-d H:i:s", strtotime("-$offset hours", strtotime($due_date . " " . $due_time)));
                                $due_date_adj = date("Y-m-d", strtotime($due));
                                $due_time_adj = date("H:i:s", strtotime($due));
                                $due_date_adj = date_create($due_date_adj);
                                $due_date_adj = date_format($due_date_adj,"F d, Y");
                                $due_time_adj = date ('g:i a',strtotime($due_time_adj));
                                echo ("is due on $due_date_adj at $due_time_adj ");
                                if ($completion_flag > 0) {
                                    if ($completion_flag == 1) {
                                        $started_date = $task_row['started_date'];
                                        // $started_date = date_create($started_date);
                                        // $started_date = date_format($started_date,"F d, Y");        
                                        $started_time = $task_row['started_time'];
                                        // $started_time = date ('g:i a',strtotime($started_time));
                                        $started = date("Y-m-d H:i:s", strtotime("-$offset hours", strtotime($started_date . " " . $started_time)));
                                        $started_date_adj = date("Y-m-d", strtotime($started));
                                        $started_time_adj = date("H:i:s", strtotime($started));
                                        $started_date_adj = date_create($started_date_adj);
                                        $started_date_adj = date_format($started_date_adj,"F d, Y");
                                        $started_time_adj = date ('g:i a',strtotime($started_time_adj));
                                        echo ("was started $started_date_adj at $started_time_adj");
                                    }
                                    if ($completion_flag == 2){
                                        $completed_date = $task_row['completed_date'];
                                        // $completed_date = date_create($completed_date);
                                        // $completed_date = date_format($completed_date,"F d, Y");        
                                        $completed_time = $task_row['completed_time'];
                                        // $completed_time = date ('g:i a',strtotime($completed_time));
                                        $completed = date("Y-m-d H:i:s", strtotime("-$offset hours", strtotime($completed_date . " " . $completed_time)));
                                        $completed_date_adj = date("Y-m-d", strtotime($completed));
                                        $completed_time_adj = date("H:i:s", strtotime($completed));
                                        $completed_date_adj = date_create($completed_date_adj);
                                        $completed_date_adj = date_format($completed_date_adj,"F d, Y");
                                        $completed_time_adj = date ('g:i a',strtotime($completed_time_adj));
                                        echo ("was completed $completed_date_adj at $completed_time_adj");
                                    }
                                    if ($completion_flag == 3){
                                        echo ("your $connection_title failed to complete this task");
                                    }
                                } else {
                                    echo ("Your $subordinate_title has not started this task yet");
                                }
                                $notes_query = "select * from task_notes where task_id = $task_id and parent_id = 0";
                                $notes_result = mysqli_query($link, $notes_query);
                                $notes_num_rows = mysqli_num_rows($notes_result);
                                if ($notes_num_rows > 0) {
                                    echo ("<h3>Notes Added To This Task</h3>");
                                    while ($note_row = mysqli_fetch_assoc($notes_result)) {
                                        $note = $note_row['note'];
                                        $image = $note_row['image'];
                                        $note_display = nl2br($note);
                                        $note_id = $note_row['note_id'];
                                        echo ("<div style='border: 1px solid black; width: 90%; margin-left: 30px; padding-left: 5px;'>");
                                            if ($image == "none") {
                                                echo ("<p>$note_display<p>");
                                            } else {
                                                echo ("<P><img src='$image' /></p>");
                                            }
                                            $reply_query = "select * from task_notes where parent_id = $note_id";
                                            $reply_result = mysqli_query($link, $reply_query);
                                            $reply_num_rows = mysqli_num_rows($reply_result);
                                            if ($reply_num_rows > 0) {
                                                while ($reply_row = mysqli_fetch_assoc($reply_result)) {
                                                    $reply_note = $reply_row['note'];
                                                    $reply_display = nl2br($reply_note);
                                                    echo ("<div style='border: 1px solid black; width: 80%; margin-left: 10px; padding-left: 5px;'>");
                                                    echo ("<p><strong>Reply:</strong><br />$reply_display</p>");
                                                    echo ("</div><br />");
                                                }
                                            }
                                            echo ("<a href='task.php?i=$task_id&action=reply&xr=$xref_id&user_is=sup&parent_id=$note_id' class='ui-btn ui-mini ui-btn-inline ui-btn-icon-left ui-icon-plus'>Reply</a>");
                                        echo ("</div>");
                                    }
                                }
                                echo ("<hr />");
                            }
                        }
                        echo ("</div>");
                    } 
                    if ($user_is == "sub"){
                        echo ("<div class='ui-body ui-body-a ui-corner-all'>");
                        echo("<h2>Tasks You Have Been Assigned by $superior_title $connection_name</h2>");
                        echo ("<hr />");
                        $tasks_query = "select * from tasks where user_id_assigned_to = $user_id and user_id_given_by = $superior_id and xref_id = $xref_id order by completion_flag";
                        $tasks_result = mysqli_query($link, $tasks_query);
                        $task_num_rows = mysqli_num_rows($tasks_result);
                        if ($task_num_rows > 0) {
                            while ($task_row = mysqli_fetch_assoc($tasks_result)){
                                $task_id = $task_row['task_id'];
                                $task_name = $task_row['task_name'];
                                echo ("<strong>$task_name:</strong> ");
                                $completion_flag = $task_row['completion_flag'];
                                $due_date = $task_row['due_date'];
                                // $due_date = date_create($due_date);
                                // $due_date = date_format($due_date,"F d, Y");
                                $due_time = $task_row['due_time'];
                                // $due_time = date ('g:i a',strtotime($due_time));
                                $due = date("Y-m-d H:i:s", strtotime("-$offset hours", strtotime($due_date . " " . $due_time)));
                                $due_date_adj = date("Y-m-d", strtotime($due));
                                $due_time_adj = date("H:i:s", strtotime($due));
                                $due_date_adj = date_create($due_date_adj);
                                $due_date_adj = date_format($due_date_adj,"F d, Y");
                                $due_time_adj = date ('g:i a',strtotime($due_time_adj));
                                echo ("is due by $due_date_adj at $due_time_adj");
                                if ($completion_flag == 0) {
                                    echo ("<br /><a href='task.php?i=$task_id&action=start&xr=$xref_id&user_is=sub' class='ui-btn ui-mini ui-btn-inline ui-btn-icon-left ui-icon-tag'>Start</a>");
                                    echo ("<a href='task.php?i=$task_id&action=notes&xr=$xref_id&user_is=sub' class='ui-btn ui-mini ui-btn-inline ui-btn-icon-left ui-icon-plus'>Add Notes</a>");
                                    echo ("<a href='task.php?i=$task_id&action=upd&xr=$xref_id&user_is=sub' class='ui-btn ui-mini ui-btn-inline ui-btn-icon-left ui-icon-camera'>Add Image</a>");
                                    echo ("<a href='task.php?i=$task_id&action=fail&xr=$xref_id&user_is=sub' class='ui-btn ui-mini ui-btn-inline ui-btn-icon-left ui-icon-delete'>Fail</a>");
                                    $notes_query = "select * from task_notes where task_id = $task_id and parent_id = 0";
                                    $notes_result = mysqli_query($link, $notes_query);
                                    $notes_num_rows = mysqli_num_rows($notes_result);
                                    if ($notes_num_rows > 0) {
                                        echo ("<p><strong>Task notes</strong></p>");
                                        echo ("<ul>");
                                        while ($note_row = mysqli_fetch_assoc($notes_result)) {
                                            $note = $note_row['note'];
                                            $note_display = nl2br($note);
                                            $image = $note_row['image'];
                                            // $note_display = str_replace('\r\n', "<br />", $note);
                                            // echo ("<li> $note_display");
                                            if ($image == "none") {
                                                echo ("<li />$note");
                                            } else {
                                                echo ("<li><img src='$image' />");
                                            }
                                        }
                                        echo ("</ul>");
                                    }
                                    echo ("<hr />");
                                }
                                if ($completion_flag == 1) {
                                    $started_date = $task_row['started_date'];
                                    // $started_date = date_create($started_date);
                                    // $started_date = date_format($started_date,"F d, Y");    
                                    $started_time = $task_row['started_time'];
                                    // $started_time = date ('g:i a',strtotime($started_time));
                                    $started = date("Y-m-d H:i:s", strtotime("-$offset hours", strtotime($started_date . " " . $started_time)));
                                        $started_date_adj = date("Y-m-d", strtotime($started));
                                        $started_time_adj = date("H:i:s", strtotime($started));
                                        $started_date_adj = date_create($started_date_adj);
                                        $started_date_adj = date_format($due_date_adj,"F d, Y");
                                        $started_time_adj = date ('g:i a',strtotime($started_time_adj));

                                    echo ("<br />You started this task on $started_date_adj at $started_time_adj");
                                    echo ("<br /><a href='task.php?i=$task_id&action=complete&xr=$xref_id&user_is=sub' class='ui-btn ui-mini ui-btn-inline ui-btn-icon-left ui-icon-check'>Mark Completed</a>");
                                    echo ("<a href='task.php?i=$task_id&action=notes&xr=$xref_id&user_is=sub' class='ui-btn ui-mini ui-btn-inline ui-btn-icon-left ui-icon-plus'>Add Notes</a>");
                                    echo ("<a href='task.php?i=$task_id&action=upd&xr=$xref_id&user_is=sub' class='ui-btn ui-mini ui-btn-inline ui-btn-icon-left ui-icon-camera'>Add Image</a>");
                                    echo ("<a href='task.php?i=$task_id&action=fail&xr=$xref_id&user_is=sub' class='ui-btn ui-mini ui-btn-inline ui-btn-icon-left ui-icon-delete'>Fail</a>");
                                    $notes_query = "select * from task_notes where task_id = $task_id and parent_id = 0";
                                    $notes_result = mysqli_query($link, $notes_query);
                                    $notes_num_rows = mysqli_num_rows($notes_result);
                                    if ($notes_num_rows > 0) {
                                        echo ("<p><strong>Task notes</strong></p>");
                                        echo ("<ul>");
                                        while ($note_row = mysqli_fetch_assoc($notes_result)) {
                                            $note = $note_row['note'];
                                            $image = $note_row['image'];
                                            $note_display = str_replace('\r\n', "<br />", $note);
                                            if ($image == "none") {
                                                echo ("<li />$note");
                                            } else {
                                                echo ("<li><img src='$image' />");
                                            }
                                            
                                        }
                                        echo ("</ul>");
                                    }
                                    echo ("<hr />");
                                }
                                if ($completion_flag == 2) {
                                    $completed_date = $task_row['completed_date'];
                                    // $completed_date = date_create($completed_date);
                                    // $completed_date = date_format($completed_date,"F d, Y");
                                    $completed_time = $task_row['completed_time'];
                                    // $completed_time = date ('g:i a',strtotime($completed_time));
                                    $completed = date("Y-m-d H:i:s", strtotime("-$offset hours", strtotime($completed_date . " " . $completed_time)));
                                        $completed_date_adj = date("Y-m-d", strtotime($completed));
                                        $completed_time_adj = date("H:i:s", strtotime($completed));
                                        $completed_date_adj = date_create($completed_date_adj);
                                        $completed_date_adj = date_format($completed_date_adj,"F d, Y");
                                        $completed_time_adj = date ('g:i a',strtotime($completed_time_adj));
                                    echo ("<br />You completed this task on $completed_date_adj at $completed_time_adj");
                                    echo ("<br /><a href='task.php?i=$task_id&action=notes&xr=$xref_id&user_is=sub' class='ui-btn ui-mini ui-btn-inline ui-btn-icon-left ui-icon-plus'>Add Notes</a>");
                                    echo ("<a href='task.php?i=$task_id&action=upd&xr=$xref_id&user_is=sub' class='ui-btn ui-mini ui-btn-inline ui-btn-icon-left ui-icon-camera'>Add Image</a>");
                                    $notes_query = "select * from task_notes where task_id = $task_id and parent_id = 0";
                                    $notes_result = mysqli_query($link, $notes_query);
                                    $notes_num_rows = mysqli_num_rows($notes_result);
                                    if ($notes_num_rows > 0) {
                                        echo ("<p><strong>Task notes</strong></p>");
                                        echo ("<ul>");
                                        while ($note_row = mysqli_fetch_assoc($notes_result)) {
                                            $note = $note_row['note'];
                                            $note_display = str_replace('\r\n', "<br />", $note);
                                            $image = $note_row['image'];
                                            if ($image == "none") {
                                                echo ("<li />$note");
                                            } else {
                                                echo ("<li><img src='$image' />");
                                            }
                                        }
                                        echo ("</ul>");
                                    }
                                    echo ("<hr />");
                                }
                                if ($completion_flag == 3) {
                                    $completed_date = $task_row['completed_date'];
                                    // $completed_date = date_create($completed_date);
                                    // $completed_date = date_format($completed_date,"F d, Y");
                                    $completed_time = $task_row['completed_time'];
                                    // $completed_time = date ('g:i a',strtotime($completed_time));
                                    $completed = date("Y-m-d H:i:s", strtotime("-$offset hours", strtotime($completed_date . " " . $completed_time)));
                                        $completed_date_adj = date("Y-m-d", strtotime($completed));
                                        $completed_time_adj = date("H:i:s", strtotime($completed));
                                        $completed_date_adj = date_create($completed_date_adj);
                                        $completed_date_adj = date_format($completed_date_adj,"F d, Y");
                                        $completed_time_adj = date ('g:i a',strtotime($completed_time_adj));
                                    echo ("<br />You failed to complete this task");
                                    echo ("<a href='task.php?i=$task_id&action=notes&xr=$xref_id&user_is=sub' class='ui-btn ui-mini ui-btn-inline ui-btn-icon-left ui-icon-plus'>Add Notes</a>");
                                    $notes_query = "select * from task_notes where task_id = $task_id and parent_id = 0";
                                    $notes_result = mysqli_query($link, $notes_query);
                                    $notes_num_rows = mysqli_num_rows($notes_result);
                                    if ($notes_num_rows > 0) {
                                        echo ("<p><strong>Task notes</strong></p>");
                                        echo ("<ul>");
                                        while ($note_row = mysqli_fetch_assoc($notes_result)) {
                                            $note = $note_row['note'];
                                            $note_display = str_replace('\r\n', "<br />", $note);
                                            echo ("<li> $note_display");
                                        }
                                        echo ("</ul>");
                                    }
                                    echo ("<hr />");
                                }
                            }
                        }
                        echo ("</div>");
                    }
                    echo ("</div>");
                    // Display rewards
                    $get_query = "select * from rewards join user_xref on rewards.xref_id = user_xref.xref_id where user_xref.xref_id = $xref_id and user_xref.superior_id = $user_id order by num_points";
                    $get_result = mysqli_query($link, $get_query);
                    $num_rows = mysqli_num_rows($get_result);
                    if ($num_rows > 0){
                        echo ("<div style='width: 1%'></div><div style='width:39%'><div class='ui-body ui-body-a ui-corner-all'>");
                        echo ("<h2>Rewards</h2>");
                        while ($row = mysqli_fetch_assoc($get_result)){
                            $reward_points = $row['num_points'];
                            $reward_desc = $row['reward_desc'];
                            echo ("<p>When $reward_points reached: $reward_desc</p>");
                        }
                        echo ("<p><strong>Tap Setup Rewards above to add/update/delete rewards</strong></p>");
                        echo ("</div></div>");
                    }
                    $get_query = "select * from rewards join user_xref on rewards.xref_id = user_xref.xref_id where user_xref.xref_id = $xref_id and user_xref.subordinate_id = $user_id order by num_points";
                    $get_result = mysqli_query($link, $get_query);
                    $num_rows = mysqli_num_rows($get_result);
                    if ($num_rows > 0){
                        echo ("<div style='width: 1%'></div><div style='width:39%'><div class='ui-body ui-body-a ui-corner-all'>");
                        echo ("<h2>Rewards</h2>");
                        while ($row = mysqli_fetch_assoc($get_result)){
                            $reward_points = $row['num_points'];
                            $reward_desc = $row['reward_desc'];
                            echo ("<p>When $reward_points reached: $reward_desc</p>");
                        }
                        echo ("</div></div>");
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