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
            if (isset($_POST['action'])){ 
                $xref_id = mysqli_real_escape_string($link, htmlspecialchars($_POST['xref_id']));
                $user_is = mysqli_real_escape_string($link, htmlspecialchars($_POST['user_is']));
                $task_id = mysqli_real_escape_string($link, htmlspecialchars($_POST['task_id']));
                $parent_id = mysqli_real_escape_string($link, htmlspecialchars($_POST['parent_id']));
                $note = mysqli_real_escape_string($link, htmlspecialchars($_POST['the_note']));
                $note_display = str_replace('\r\n', "<br />", $note);
                $action = mysqli_real_escape_string($link, htmlspecialchars($_POST['action']));
                if ($user_data_time > time()) {
                    include 'config.php';
                    $link = mysqli_connect($host, $dbuser, $dbpass, $dbname);
                    $user_id = $user_data->data->user_id;
                    $user_type = $user_data->data->user_type;
                } else {
                    header("Location: login.php");
                }
                if ($action === "add_note"){
                    if ($user_is == "sub") {
                        $sec_query = "select * from user_xref join tasks on user_xref.xref_id = tasks.xref_id where user_xref.xref_id = $xref_id and user_xref.subordinate_id = $user_id and tasks.task_id = $task_id";
                    }
                    if ($user_is == "sup"){
                        $sec_query = "select * from user_xref join tasks on user_xref.xref_id = tasks.xref_id where user_xref.xref_id = $xref_id and user_xref.superior_id = $user_id and tasks.task_id = $task_id";
                    }
                    $sec_result = mysqli_query($link, $sec_query);
                    $sec_num_rows = mysqli_num_rows($sec_result);
                    if ($sec_num_rows == 1) {
                        $task_row = mysqli_fetch_assoc($sec_result);
                        $task_name = $task_row['task_name'];
                        $date = date("Y-m-d");
                        $time = date("H:i", $_SERVER['REQUEST_TIME']);
                        $ins_query = "insert into task_notes (task_id, note, note_date, note_time, parent_id) VALUES ($task_id, '$note', '$date', '$time', $parent_id)";
                        $ins_result = mysqli_query($link, $ins_query);
                        $upd_query = "update tasks set last_action = '$date' where task_id = $task_id";
                        $upd_result = mysqli_query($link, $upd_query);
                        if ($user_is == "sub") {
                            $conection_id = $task_row['superior_id'];
                            $em_query = "select email from users where user_id = $conection_id";
                            $subject = 'A note was left on your task';
                            $message = 'A note was left on a task that you gave on Master Tasker.  Check it out by going to:' . "\r\n" . "https://dewdevelopment.com/MasterTasker/" . "\r\n\r\n" . "If you are already logged in simply refresh the home page to see the note.";    
                        }
                        if ($user_is == "sup") {
                            $conection_id = $task_row['subordinate_id'];
                            $em_query = "select email from users where user_id = $conection_id";
                            $subject = 'You have reply to your task note';
                            $message = 'You have a reply to a note you left to a task on Master Tasker.  Check it out by going to:' . "\r\n" . "https://dewdevelopment.com/MasterTasker/" . "\r\n\r\n" . "If you are already logged in simply refresh the home page to see the reply.";    
                        }
                        $em_result = mysqli_query($link, $em_query);
                        $em_row = mysqli_fetch_assoc($em_result);
                        $em = $em_row['email'];
                        $headers = 'From: Master Tasker Support <support@dewdevelopment.com>' . "\r\n" . 'Reply-To: support@dewdevelopment.com' . "\r\n" . 'X-Mailer: PHP/' . phpversion();
                        mail($em, $subject, $message, $headers);

                    }
                }
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
            <?php
                if ($action === "add_note"){
                    echo ("<h1>Note Added</h1>");
                    echo ("<a href='index.php' class='ui-btn ui-shadow ui-corner-all ui-icon-home ui-btn-icon-notext'>Home</a>");
                }
            ?>
            </div>
            <div data-role="main" class="ui-content"><div class="ui-body ui-body-a ui-corner-all">
            <?php
                if ($action === "add_note"){
                    echo ("<p><strong>The following note was added to task: $task_name </strong></p>");
                    echo ("<p>$note_display</p>");
                    echo ("<hr />");
                    echo ("<a href='index.php?ref=note_added'>Return to front page</a>");
                    if ($user_is == "sub") {
                        echo ("or return to <a href='disp_connection.php?xref_id=$xref_id&user_is=$user_is'>connection feed</a>");
                    }
                    if ($user_is == "sup") {
                        echo (" or return to <a href='disp_connection.php?xref_id=$xref_id&user_is=$user_is'>connection feed</a>");
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