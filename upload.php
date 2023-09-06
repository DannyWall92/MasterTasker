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
    </head>
    <body>
        <?php
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
            
                    if ($action === "upload" || $action === "upd"){
                        echo ("<h1>Basic Image Upload for $task_name</h1>");
                        echo ("<p><a href='index.php'>return home</a></p>");
                    }

                    if ($action === "upd" && $allgood === "yes"){
                        echo ("<form action='task.php' method='post' enctype=\"multipart/form-data\">");
                            echo ("<input type='hidden' name='xr' value='$xref_id'>");
                            echo ("<input type='hidden' name='i' value='$task_id'>");
                            echo ("<input type='hidden' name='user_is' value='$user_is'>");
                            echo ("<input type='hidden' name='parent_id' value='0'>");
                            echo ("<input type='hidden' name='action' value='upload'>");
                            echo ("<label for='fileToUpload'>Select image to upload:</label> <input type='file' name='fileToUpload' id='fileToUpload'><br />");
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
                        if(isset($_REQUEST["submit"]) && isset($_FILES['file'])) {
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
                                echo "The file " . basename( $_FILES["fileToUpload"]["name"]) . " has been uploaded.";
                            } else {
                                echo "Sorry, there was an error uploading your file.";
                            }
                        }
                    }
                ?>

    </body>
</html>