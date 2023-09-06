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
            $xref_id = mysqli_real_escape_string($link, htmlspecialchars($_REQUEST['xr']));
            $user_is = mysqli_real_escape_string($link, htmlspecialchars($_REQUEST['user_is']));
            $action = mysqli_real_escape_string($link, htmlspecialchars($_REQUEST['action']));
            $task_id = mysqli_real_escape_string($link, htmlspecialchars($_REQUEST['i']));
            $cur_date = date("Y-m-d");
            $cur_time = date("H:i", $_SERVER['REQUEST_TIME']);
            $is_daylightsavings = date('I');
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
            if ($user_is === "sup"){
                $task_query = "select * from user_xref inner join tasks on user_xref.xref_id = tasks.xref_id where user_xref.xref_id = $xref_id and user_xref.superior_id = $user_id and tasks.task_id = $task_id";
                $task_result = mysqli_query($link, $task_query);
                $task_num_rows = mysqli_num_rows($task_result);
                if ($task_num_rows == 1){
                    $task_row = mysqli_fetch_assoc($task_result);
                    $task_name = $task_row['task_name'];
                    $task_id = $task_row['task_id'];
                    $allgood = "yes";
                }
                echo ("<div data-role='page'>");
                    echo ("<div data-role='header'>");
                        if ($action === "reply") {
                            echo ("<h1>Note Reply</h1>");
                        }
                        echo ("<a href='index.php?ref=fromtask' class='ui-btn ui-shadow ui-corner-all ui-icon-home ui-btn-icon-notext'>Home</a>");
                    echo ("</div>");
                    echo ("<div data-role='main' class='ui-content'><div class='ui-body ui-body-a ui-corner-all'>");
                    if ($action === "reply" && $allgood === "yes"){
                        $parent_id = mysqli_real_escape_string($link, htmlspecialchars($_REQUEST['parent_id']));
                        $orig_query = "select * from task_notes where note_id = $parent_id and task_id = $task_id";
                        $orig_result = mysqli_query($link, $orig_query);
                        $orig_row = mysqli_fetch_assoc($orig_result);
                        $orig_note = $orig_row['note'];
                        echo ("<h2>Leaving a reply to task: $task_name</h2><p><strong>On Note:</strong> $orig_note</p>");
                        echo ("<form action='add_note.php' method='post'>");
                            echo ("<input type='hidden' name='xref_id' value='$xref_id'>");
                            echo ("<input type='hidden' name='task_id' value='$task_id'>");
                            echo ("<input type='hidden' name='user_is' value='$user_is'>");
                            echo ("<input type='hidden' name='parent_id' value='$parent_id'>");
                            echo ("<input type='hidden' name='action' value='add_note'>");
                            echo ("<label for='the_note'>What reply do you want to leave?</label><textarea name='the_note' id='the_note'></textarea>");
                            echo ("<input type='submit' name='submit' value='Submit'>");
                        echo ("</form>");
                    } else {
                        echo ("Fatal Error");
                    }
            }
            if ($user_is === "sub"){
                $task_query = "select * from user_xref inner join tasks on user_xref.xref_id = tasks.xref_id where user_xref.xref_id = $xref_id and subordinate_id = $user_id and tasks.task_id = $task_id";
                $task_result = mysqli_query($link, $task_query);
                $task_num_rows = mysqli_num_rows($task_result);
                if ($task_num_rows == 1){
                    $task_row = mysqli_fetch_assoc($task_result);
                    $task_name = $task_row['task_name'];
                    $allgood = "yes";
                }
            }
            echo ("<div data-role='page'>");
                echo ("<div data-role='header'>");
                    if ($action === "notes") {
                        echo ("<h1>Leave A Note For: $task_name</h1>");
                    }
                    if ($action === "start"){
                        echo ("<h1>Start Task $task_name</h1>");
                    }
                    if ($action === "complete"){
                        echo ("<h1>Completed Task $task_name</h1>");
                    }
                    if ($action === "upload" || $action === "upd"){
                        echo ("<h1>Image Upload for $task_name</h1>");
                    }
                    echo ("<a href='index.php?ref=fromtask' class='ui-btn ui-shadow ui-corner-all ui-icon-home ui-btn-icon-notext'>Home</a>");
                echo ("</div>");
                echo ("<div data-role='main' class='ui-content'><div class='ui-body ui-body-a ui-corner-all'>");
                    if ($action === "notes" && $user_is === "sub" && $allgood === "yes"){
                        echo ("<h2>Leaving a note to task: $task_name</h2>");
                        echo ("<form action='add_note.php' method='post'>");
                            echo ("<input type='hidden' name='xref_id' value='$xref_id'>");
                            echo ("<input type='hidden' name='task_id' value='$task_id'>");
                            echo ("<input type='hidden' name='user_is' value='$user_is'>");
                            echo ("<input type='hidden' name='parent_id' value='0'>");
                            echo ("<input type='hidden' name='action' value='add_note'>");
                            echo ("<label for='the_note'>What note do you want to leave for this task?</label><textarea name='the_note' id='the_note'></textarea>");
                            echo ("<input type='submit' name='submit' value='Submit'>");
                        echo ("</form>");
                    }
                    if ($action === "start" && $user_is === "sub" && $allgood === "yes"){
                        $upd_query = "update tasks set completion_flag = 1, started_date = '$cur_date', started_time = '$cur_time', last_action = '$cur_date' where task_id = $task_id";
                        $upd_result = mysqli_query($link, $upd_query);
                        $cur = date("Y-m-d H:i:s", strtotime("-$offset hours", strtotime($cur_date . " " . $cur_time)));
                                        $cur_date_adj = date("Y-m-d", strtotime($cur));
                                        $cur_time_adj = date("H:i:s", strtotime($cur));
                                        $cur_date_adj = date_create($cur_date_adj);
                                        $cur_date_adj = date_format($cur_date_adj,"F d, Y");
                                        $cur_time_adj = date ('g:i a',strtotime($cur_time_adj));
                        echo ("<h2>$task_name started on $cur_date_adj at $cur_time_adj</h2>");
                        echo ("<p><a href='index.php?ref=start'>Return to main feed</a></p>");
                    }
                    if ($action === "complete" && $user_is === "sub" && $allgood === "yes"){
                        $check_query = "select * from tasks join user_xref on tasks.xref_id = user_xref.xref_id where task_id = $task_id and subordinate_id = $user_id";
                        $check_result = mysqli_query($link, $check_query);
                        $num_rows = mysqli_num_rows($check_result);
                        if ($num_rows == 1) {
                            $row = mysqli_fetch_assoc($check_result);
                            $completion_points = $row['completion_points'];
                            $late_mins = $row['late_minus'];
                            // First lets see if reward points are owed
                            if ($completion_points > 0) {
                                $due_date = $row['due_date'];
                                if ($cur_date < $due_date) {
                                    $points = $row['subordinate_points'] + $completion_points;
                                    $award_query = "update user_xref set subordinate_points = $points where xref_id = $xref_id";
                                    $award_result = mysqli_query($link, $award_query);
                                    $ontime = "yes";
                                } else {
                                    $due_time = $row['due_time'];
                                    if ($cur_time <= $due_time) {
                                        $points = $row['subordinate_points'] + $completion_points;
                                        $award_query = "update user_xref set subordinate_points = $points where xref_id = $xref_id";
                                        $award_result = mysqli_query($link, $award_query);
                                        $ontime = "yes";
                                    } else {
                                        $points = $row['subordinate_points'] - $late_minus;
                                        $award_query = "update user_xref set subordinate_points = $points where xref_id = $xref_id";
                                        $award_result = mysqli_query($link, $award_query);
                                        $ontime = "no";
                                    }
                                }
                            } else {
                                $ontime="nopoints";
                            }
                            
                            $upd_query = "update tasks set completion_flag = 2, completed_date = '$cur_date', completed_time = '$cur_time', last_action = '$cur_date' where task_id = $task_id";
                            $upd_result = mysqli_query($link, $upd_query);
                            $cur = date("Y-m-d H:i:s", strtotime("-$offset hours", strtotime($cur_date . " " . $cur_time)));
                                        $cur_date_adj = date("Y-m-d", strtotime($cur));
                                        $cur_time_adj = date("H:i:s", strtotime($cur));
                                        $cur_date_adj = date_create($cur_date_adj);
                                        $cur_date_adj = date_format($cur_date_adj,"F d, Y");
                                        $cur_time_adj = date ('g:i a',strtotime($cur_time_adj));
                            if ($ontime === "yes"){
                                echo ("<h2>$task_name completed on $cur_date_adj at $cur_time_adj</h2>");
                                echo ("<P>You earned $completion_points from this task.  You now have $points in total you have earned.</p>");
                            }
                            if ($ontime === "no"){
                                echo ("<h2>$task_name completed on $cur_date_adj at $cur_time_adj</h2>");
                                echo ("<P>You were late in completing this taks and lost $late_minus points.  You now have $points in total.</p>");
                            }
                            if ($ontime === "nopoints"){
                                echo ("<h2>$task_name completed on $cur_date_adj at $cur_time_adj</h2>");
                            }
                            echo ("<p><a href='index.php?ref=task'>Return to main feed</a></p>");
                        }
                    }
                    if ($action === "fail" && $user_is === "sub" && $allgood === "yes"){
                        $fail_minus = $task_row['fail_minus'];
                        if ($fail_minus > 0) {
                            $points = $task_row['subordinate_points'] - $fail_minus;
                            $fail_query = "update user_xref set subordinate_points = $points where xref_id = $xref_id";
                            $fail_result = mysqli_query($link, $fail_query);
                            $withminus = "yes";
                        } else {
                            $withminus = "no";
                        }
                        $upd_query = "update tasks set completion_flag = 3, last_action = '$cur_date' where task_id = $task_id";
                        $upd_result = mysqli_query($link, $upd_query);
                        echo ("<h2>$task_name started on $cur_date at $cur_time</h2>");
                        if ($withminus == "yes") {
                            echo ("<p>Your failure to complete the task results in losing $fail_minus points leaving you with $points in total</p>");
                        }
                        echo ("<p><a href='index.php?ref=start'>Return to main feed</a></p>");
                    }
                    if ($action === "upd" && $allgood === "yes"){
                        echo ("<form action='task.php' method='post' enctype=\"multipart/form-data\" data-ajax='false'>");
                            echo ("<input type='hidden' name='xr' value='$xref_id'>");
                            echo ("<input type='hidden' name='i' value='$task_id'>");
                            echo ("<input type='hidden' name='user_is' value='$user_is'>");
                            echo ("<input type='hidden' name='parent_id' value='0'>");
                            echo ("<input type='hidden' name='action' value='upload'>");
                            echo ("<label for='fileToUpload'>Select image to upload:</label> <input type='file' name='fileToUpload' id='fileToUpload'>");
                            echo ("<input type='submit' value='Upload Image' name='submit'>");
                        echo ("</form>");
                    }
                    if ($action === "upload" && $allgood === "yes") {
                        $target_dir = "uploads/" . $user_id . "/";
                        $dir_good = file_exists($target_dir);
                        if (!$dir_good) {
                            $dir_good = mkdir("./" . $target_dir, 0777, true);
                            if (!$dir_good) {
                                echo ("<h2>user folder not created - upload failed</h2>");
                            } 
                        } 
                        $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
                        $uploadOk = 1;
                        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

                        // Check if image file is a actual image or fake image
                        if(isset($_POST["submit"]) && isset($_FILES['file'])) {
                            $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
                            if($check !== false) {
                                echo "File is an image - " . $check["mime"] . ".";
                                $uploadOk = 1;
                            } else {
                                echo "File is not an image.";
                                $uploadOk = 0;
                            }
                        }
                        
                        // Check if file already exists
                        if (file_exists($target_file) && isset($_FILES['file'])) {
                            echo "Sorry, file already exists.";
                            $uploadOk = 0;
                        }

                        // Check file size
                        if ($_FILES["fileToUpload"]["size"] > 500000 && isset($_FILES['file'])) {
                            echo "Sorry, your file is too large.";
                            $uploadOk = 0;
                        }

                        // Allow certain file formats
                        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" && isset($_FILES['file'])) {
                            echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
                            $uploadOk = 0;
                        }

                        // Check if $uploadOk is set to 0 by an error
                        if ($uploadOk == 0) {
                            echo "Sorry, your file was not uploaded.";
                            // if everything is ok, try to upload file
                        } else {
                            if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
                                echo "<h2>The file " . basename( $_FILES["fileToUpload"]["name"]) . " has been uploaded.</h2>";
                                $date = date("Y-m-d");
                                $time = date("H:i", $_SERVER['REQUEST_TIME']);
                                $note = "<img src='$target_file' />";
                                $ins_query = "insert into task_notes (task_id, note, image, note_date, note_time, parent_id) VALUES ($task_id, '', '$target_file', '$date', '$time', 0)";
                                $ins_result = mysqli_query($link, $ins_query);
                                echo ("$ins_query");
                            } else {
                                echo "Sorry, there was an error uploading your file.";
                            }
                        }
                    }
                ?>
            </div></div>
            <div data-role='footer'>
            <?php
                include 'footer.php';
            ?>
            </div>
        </div>
    </body>
</html>