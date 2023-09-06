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
                <h1>Connection Listings</h1>
                <a href="index.php" class="ui-btn ui-shadow ui-corner-all ui-icon-home ui-btn-icon-notext">Home</a>
            </div>
            <div data-role="main" class="ui-content"><div class="ui-body ui-body-a ui-corner-all">
                <?php
                if (!isset($_GET['action'])){
                    $usr_query = "select listings from users where user_id = $user_id";
                    $usr_result = mysqli_query($link, $usr_query);
                    $usr_row = mysqli_fetch_assoc($usr_result);
                    $listings = $usr_row['listings'];
                    if ($listings == "1"){
                        echo ("<h2>No listings for how your user profile is configured</h2>");
                        echo ("<p>Your profile is not set up for you to be a part of the connection listings and therefore we can not show them to you</p>");
                        echo ("<p>Go to <a href='profile.php'>your profile page</a> and change your listing status to either give tasks to someone or to receive them from someone to see listings that match.</p>");
                    }
                    if ($listings == "2"){
                        echo ("<h2>People who want to receive tasks</h2>");
                        $listings_query = "select user_id, name, gender, preferred_title from users where listings = 3";
                        $listings_result = mysqli_query($link, $listings_query);
                        $listings_num_rows = mysqli_num_rows($listings_result);
                        if ($listings_num_rows > 0){
                            echo ("<p><strong>Click on a link below and you will be taken back to the connection page with that person filled in.  Click submit and that person will then have to confirm that they want to connect with you.</strong></p>");
                            while ($row = mysqli_fetch_assoc($listings_result)) {
                                $ref = $row['user_id'];
                                $connection_name = $row['name'];
                                $gender = $row['gender'];
                                $title = $row['preferred_title'];
                                echo ("<hr /><p><a class='ui-btn ui-btn-icon-top ui-icon-user' href='connection_listings.php?&action=from&ref=$ref&connect_title=$title&person_is=below'>$connection_name</a><br />This person identifies as: $gender and wants to be called: $title</p>");
                            }
                        } else {
                            echo ("<p>Unfortunately there is presently no one looking to receive tasks from others</p>");
                        }
                    }
                    if ($listings == "3"){
                        echo ("<h2>People who want to give tasks</h2>");
                        $listings_query = "select user_id, name, gender, preferred_title from users where listings = 2";
                        $listings_result = mysqli_query($link, $listings_query);
                        $listings_num_rows = mysqli_num_rows($listings_result);
                        if ($listings_num_rows > 0){
                            echo ("<p><strong>Click on a link below and you will be taken back to the connection page with that person filled in.  Click submit and that person will then have to confirm that they want to connect with you.</strong></p>");
                            // echo ("$listings_query");
                            while ($row = mysqli_fetch_assoc($listings_result)) {
                                $ref = $row['user_id'];
                                $connection_name = $row['name'];
                                $gender = $row['gender'];
                                $title = $row['preferred_title'];
                                echo ("<hr /><p><a class='ui-btn ui-btn-mini ui-btn-icon-top ui-icon-user' href='connection_listing.php?action=from&ref=$ref&connect_title=$title&connection_name=$connection_name&person_is=above'>$connection_name</a><br /> This person identifies as: $gender and wants to be called: $title</p>");
                            }
                        } else {
                            echo ("<p>Unfortunately there is presently no one looking to give tasks to others</p>");
                        }
                    }
                } else {
                    $action = mysqli_real_escape_string($link, htmlspecialchars($_GET['action']));
                    if ($action === "from"){
                        $connect_id = mysqli_real_escape_string($link, htmlspecialchars($_GET['ref']));
                        $connect_title = mysqli_real_escape_string($link, htmlspecialchars($_GET['connect_title']));
                        $person_is = mysqli_real_escape_string($link, htmlspecialchars($_GET['person_is']));
                        $connection_name = mysqli_real_escape_string($link, htmlspecialchars($_GET['connection_name']));
                        if ($person_is === "above"){
                            $check_query = "select email from users where user_id = $connect_id and preferred_title = '$connect_title' and listings=2";
                            $check_result = mysqli_query($link, $check_query);
                            $num_rows = mysqli_num_rows($check_result);
                            if ($num_rows == 1){
                                $row = mysqli_fetch_assoc($check_result);
                                $connect_email = $row['email'];
                                $subject = '[Master Tasker] Someone wants to connect with you';
                                $message = 'Someone wants to connect with you on Master Tasker.  Find out who!' . "\r\n" . "http://dewdevelopment.com/MasterTasker/index.php";
                                $headers = 'From: Master Tasker Support <support@dewdevelopment.com>' . "\r\n" . 'Reply-To: support@dewdevelopment.com' . "\r\n" . 'X-Mailer: PHP/' . phpversion();
                                mail($connect_email, $subject, $message, $headers);
                                $ins_query = "insert into user_xref (superior_id, subordinate_id, superior_title, subordinate_title, submitted_by) VALUES ($connect_id, $user_id, '$connect_title', '', $user_id)";
                                $ins_result = mysqli_query($link, $ins_query);
                                echo ("<h2>Connection Request Set</h2>");
                                echo ("You have sent a connection request to $connect_title $connection_name for them to give you tasks which they will have to confirm they want to connect with you to give them to you.  This connection was created without giving them a title to use for you which they can do themselves.  In the very near future you will be able to message this person.");
                            }
                        }
                        if ($person_is === "below"){
                            $check_query = "select email from users where user_id = $connect_id and preferred_title = '$connect_title' and listings=3";
                            $check_result = mysqli_query($link, $check_query);
                            $num_rows = mysqli_num_rows($check_result);
                            if ($num_rows == 1){
                                $row = mysqli_fetch_assoc($check_result);
                                $connect_email = $row['email'];
                                $subject = '[Master Tasker] Someone wants to connect with you';
                                $message = 'Someone wants to connect with you on Master Tasker.  Find out who!' . "\r\n" . "http://dewdevelopment.com/MasterTasker/index.php";
                                $headers = 'From: Master Tasker Support <support@dewdevelopment.com>' . "\r\n" . 'Reply-To: support@dewdevelopment.com' . "\r\n" . 'X-Mailer: PHP/' . phpversion();
                                mail($connect_email, $subject, $message, $headers);
                                $ins_query = "insert into user_xref (subordinate_id, superior_id, subordinate_title, superior_title, submitted_by) VALUES ($connect_id, $user_id, '$connect_title', '', $user_id)";
                                $ins_result = mysqli_query($link, $ins_query);
                                echo ("<h2>Connection Request Set</h2>");
                                echo ("You have sent a connection request to $connect_title $connection_name for you to give them tasks which they will have to confirm they want to connect with you before you can give them tasks.  This connection was created without giving them a title to use for you which you can do in the Update Titles area once they have confirmed the connection.  In the very near future you will be able to message this person.  You can make initial contact now however through giving the person a task to which they can leave notes and you can reply.");;
                            }
                        }

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