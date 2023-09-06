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
                $offset_query = "select timezone_offset from users where user_id = $user_id";
                $offset_result = mysqli_query($link, $offset_query);
                $offset_row = mysqli_fetch_assoc($offset_result);
                $offset = $offset_row['timezone_offset'];
                $offset = $is_daylightsavings + $offset;
                $server_offset = -6;
                $server_offset = $server_offset + $is_daylightsavings;
                $offset = $server_offset - $offset;

                // $query = "select c.name, c.logo, c.hvacphone, c.elecphone, c.plumbphone from users u inner join contractors c ON u.contractorID = c.ID where u.email like '$email'";
                // $result = mysqli_query($link, $query);
                // $row = mysqli_fetch_assoc($result);
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
                <h1>Master Tasker (public beta)</h1>
                <a href='logout.php' class='ui-btn ui-btn-right ui-btn-icon-left ui-icon-delete'>Logout</a>
            </div>
            <div data-role="main" class="ui-content"><div class="ui-body ui-body-a ui-corner-all">
                <?php
                // paint connection stuff
                $con_query = "select * from user_xref where superior_id = $user_id or subordinate_id = $user_id";
                $con_result = mysqli_query($link, $con_query);
                $num_rows = mysqli_num_rows($con_result);
                echo ("<div data-role='navbar' data-grid='d'><ul>");
                    // the help and connect icons are always displayed
                    echo ("<li><a href='profile.php' class='ui-btn ui-btn-icon-top ui-icon-gear'>Profile</a>");
                    echo ("<li><a href='help.php' class='ui-btn ui-btn-icon-top ui-icon-info'>Help</a>");
                    echo ("<li><a href='connect.php' class='ui-btn ui-btn-icon-top ui-icon-action'>Connect</a>");
                    
                    // now show icon buttons for anyone this user is connected to
                    if ($num_rows > 0){
                        while ($row = mysqli_fetch_assoc($con_result)) {
                            $xref_id = $row['xref_id'];
                            $superior_id = $row['superior_id'];
                            $subordinate_id = $row['subordinate_id'];
                            $superior_title = $row['superior_title'];
                            $subordinate_title = $row['subordinate_title'];
                            $connection_confirmed = $row['connection_confirmed'];
                            if ($superior_id != $user_id && $connection_confirmed == 1) {
                                $sup_query = "select email, name from users where user_id = $superior_id";
                                $sup_result = mysqli_query($link, $sup_query);
                                $sup_row = mysqli_fetch_assoc($sup_result);
                                $sup_name = $sup_row['name'];
                                $sup_email = $sup_row['email'];
                                echo ("<li><a href='disp_connection.php?xref_id=$xref_id&user_is=sub' class='ui-btn ui-btn-icon-top ui-icon-user'>$superior_title $sup_name</a>");
                            }
                            if ($subordinate_id != $user_id && $connection_confirmed == 1) {
                                $sub_query = "select email, name from users where user_id = $subordinate_id";
                                $sub_result = mysqli_query($link, $sub_query);
                                $sub_row = mysqli_fetch_assoc($sub_result);
                                $sub_name = $sub_row['name'];
                                $sub_email = $sub_row['email'];
                                echo ("<li><a href='disp_connection.php?xref_id=$xref_id&user_is=sup' class='ui-btn ui-btn-icon-top ui-icon-user'>$subordinate_title $sub_name</a>");
                            }
                        }
                    }
                echo ("</ul></div>");

                // now determine if there is anyone wanting to connect before displaying the rest of the feed
                $conf_query = "select * from user_xref where superior_id = $user_id or subordinate_id = $user_id and submitted_by <> $user_id";
                $conf_result = mysqli_query($link, $conf_query);
                $num_rows = mysqli_num_rows($conf_result);
                // echo ("<p>$conf_query - $num_rows</p>");
                if ($num_rows > 0) {
                    while ($row = mysqli_fetch_assoc($conf_result)) {
                        $xref_id = $row['xref_id'];
                        $superior_id = $row['superior_id'];
                        $subordinate_id = $row['subordinate_id'];
                        $superior_title = $row['superior_title'];
                        $subordinate_title = $row['subordinate_title'];
                        $connection_confirmed = $row['connection_confirmed'];
                        $submitted_by = $row['submitted_by'];
                        if ($superior_id != $user_id && $connection_confirmed == 0 && $submitted_by != $user_id) {
                            $sup_query = "select email, name from users where user_id = $superior_id";
                            $sup_result = mysqli_query($link, $sup_query);
                            $sup_row = mysqli_fetch_assoc($sup_result);
                            $sup_name = $sup_row['name'];
                            $sup_email = $sup_row['email'];
                            echo ("$superior_title $sup_name wants to connect with you");
                            echo ("<div data-role='navbar'><ul>");
                                echo ("<li><a href='connect_confirm.php?action=confirm&user_is=sub&xref_id=$xref_id' class='ui-btn ui-corner-all ui-btn-icon-left ui-icon-check'>Confirm Connection</a>");
                                echo ("<li><a href='connect_confirm.php?action=noconfirm&user_is=sub&xref_id=$xref_id' class='ui-btn ui-corner-all ui-btn-icon-left ui-icon-forbidden'>Do not connect</a>");
                            echo ("</ul></div>");
                        }
                        if ($subordinate_id != $user_id && $connection_confirmed == 0 && $submitted_by != $user_id) {
                            $sub_query = "select email, name from users where user_id = $subordinate_id";
                            $sub_result = mysqli_query($link, $sub_query);
                            $sub_row = mysqli_fetch_assoc($sub_result);
                            $sub_name = $sub_row['name'];
                            $sub_email = $sub_row['email'];
                            echo ("$subordinate_title $sub_name wants to connect with you");
                            echo ("<div data-role='navbar'><ul>");
                                echo ("<li><a href='connect_confirm.php?action=confirm&user_is=sup&user_is=sup&xref_id=$xref_id' class='ui-btn ui-corner-all ui-btn-icon-left ui-icon-check'>Confirm Connection</a>");
                                echo ("<li><a href='connect_confirm.php?action=noconfirm&user_is=sup&xref_id=$xref_id' class='ui-btn ui-corner-all ui-btn-icon-left ui-icon-forbidden'>I do not know this person</a>");
                            echo ("</ul></div>");
                        }
                    }
                }

                // Now display the feed
                $tasks_query = "select * from tasks where user_id_given_by = $user_id or user_id_assigned_to = $user_id order by last_action desc";
                $tasks_result = mysqli_query($link, $tasks_query);
                $tasks_num_rows = mysqli_num_rows($tasks_result);
                if ($tasks_num_rows > 0) {
                    echo ("<h2>Task Feed</h2>");
                    while ($task_row = mysqli_fetch_assoc($tasks_result)){
                        $task_id = $task_row['task_id'];
                        $xref_id = $task_row['xref_id'];
                        $task_name = $task_row['task_name'];
                        $assigned_to = $task_row['user_id_assigned_to'];
                        echo ("<p><strong>$task_name:</strong> ");
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

                        $assigned_to = $task_row['user_id_assigned_to'];
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
                                if ($assigned_to == $user_id){
                                    echo ("<br /><a href='task.php?i=$task_id&action=complete&xr=$xref_id&user_is=sub' class='ui-btn ui-corner-all ui-mini ui-btn-inline ui-btn-icon-left ui-icon-check'>Mark Completed</a>");
                                    echo ("<a href='task.php?i=$task_id&action=notes&xr=$xref_id&user_is=sub' class='ui-btn ui-corner-all ui-mini ui-btn-inline ui-btn-icon-left ui-icon-plus'>Add Notes</a>");
                                    echo ("<a href='task.php?i=$task_id&action=fail&xr=$xref_id&user_is=sub' class='ui-btn ui-corner-all ui-mini ui-btn-inline ui-btn-icon-left ui-icon-delete'>Fail</a></br>");
                                }
                            }
                            if ($completion_flag == 2){
                                $completed_date = $task_row['completed_date'];
                                // $completed_date = date_create($completed_date);
                                // $completed_date = date_format($completed_date,"F d, Y");
                                $completed_time = $task_row['started_time'];
                                // $completed_time = date ('g:i a',strtotime($completed_time));
                                $completed = date("Y-m-d H:i:s", strtotime("-$offset hours", strtotime($completed_date . " " . $completed_time)));
                                        $completed_date_adj = date("Y-m-d", strtotime($completed));
                                        $completed_time_adj = date("H:i:s", strtotime($completed));
                                        $completed_date_adj = date_create($completed_date_adj);
                                        $completed_date_adj = date_format($completed_date_adj,"F d, Y");
                                        $completed_time_adj = date ('g:i a',strtotime($completed_time_adj));
                                echo ("was completed $completed_date_adj at $completed_time_adj");
                                if ($assigned_to == $user_id){
                                    echo ("<br /><a href='task.php?i=$task_id&action=notes&xr=$xref_id&user_is=sub' class='ui-btn ui-corner-all ui-mini ui-btn-inline ui-btn-icon-left ui-icon-plus'>Add Notes</a></p>");
                                }
                            }
                            if ($completion_flag == 3){
                                echo (" task was failed</p>");
                            }
                        } else {
                            echo (" this task has not been started yet");
                            if ($assigned_to == $user_id){
                                echo ("<br /><a href='task.php?i=$task_id&action=start&xr=$xref_id&user_is=sub' class='ui-btn ui-corner-all ui-mini ui-btn-inline ui-btn-icon-left ui-icon-tag'>Start</a>");
                                echo ("<a href='task.php?i=$task_id&action=notes&xr=$xref_id&user_is=sub' class='ui-btn ui-corner-all ui-mini ui-btn-inline ui-btn-icon-left ui-icon-plus'>Add Notes</a>");
                                echo ("<a href='task.php?i=$task_id&action=fail&xr=$xref_id&user_is=sub' class='ui-btn ui-corner-all ui-mini ui-btn-inline ui-btn-icon-left ui-icon-delete'>Fail</a></p>");
                            }
                        }
                        $notes_query = "select * from task_notes where task_id = $task_id and parent_id = 0";
                        $notes_result = mysqli_query($link, $notes_query);
                        $notes_num_rows = mysqli_num_rows($notes_result);
                        if ($notes_num_rows > 0) {
                            while ($note_row = mysqli_fetch_assoc($notes_result)) {
                                $note = $note_row['note'];
                                $note_display = nl2br($note);
                                $note_date = $note_row['note_date'];
                                // $note_date = date_create($note_date);
                                // $note_date = date_format($note_date,"F d, Y");
                                $note_time = $note_row['note_time'];
                                // $note_time = date ('g:i a',strtotime($note_time));
                                $note_adj = date("Y-m-d H:i:s", strtotime("-$offset hours", strtotime($note_date . " " . $note_time)));
                                        $note_date_adj = date("Y-m-d", strtotime($note_adj));
                                        $note_time_adj = date("H:i:s", strtotime($note_adj));
                                        $note_date_adj = date_create($note_date_adj);
                                        $note_date_adj = date_format($note_date_adj,"F d, Y");
                                        $note_time_adj = date ('g:i a',strtotime($note_time_adj));
                                $note_id = $note_row['note_id'];
                                echo ("<div style='border: 1px solid black; width: 90%; margin-left: 30px; padding-left: 5px;'>");
                                    echo ("<p style='text-decoration: underline;'>Note created on $note_date_adj at $note_time_adj</p>");
                                    $image = $note_row['image'];
                                            if ($image == "none") {
                                                echo ("<li />$note_display");
                                            } else {
                                                echo ("<li><img src='$image' />");
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
                                    if ($user_id != $assigned_to) {
                                        echo ("<a href='task.php?i=$task_id&action=reply&xr=$xref_id&user_is=sup&parent_id=$note_id' class='ui-btn ui-mini ui-btn-inline ui-btn-icon-left ui-icon-plus'>Reply</a>");
                                    }
                                echo ("</div>");
                            }
                        }
                        echo ("<hr />");
                    }
                } else {
                    echo ("<p>This app is designed that you either give someone tasks, or else someone gives them to you.  Use the 'Connect' button above as a way of connecting with your Dom or sub for that purpose.</p>");
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