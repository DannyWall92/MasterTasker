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
                <h1>Your User Profile</h1>
                <a href="index.php" class="ui-btn ui-shadow ui-corner-all ui-icon-home ui-btn-icon-notext">Home</a>
            </div>
            <div data-role="main" class="ui-content"><div class="ui-body ui-body-a ui-corner-all">
                <?php
                if (isset($_POST['action'])){
                    $action = htmlspecialchars($_POST['action']);
                } else {
                    $action = "new";
                }
                if ($action === "new") {
                    $user_query = "select * from users where user_id = $user_id";
                    $user_result = mysqli_query($link, $user_query);
                    $num_rows = mysqli_num_rows($user_result);
                    if ($num_rows == 1) {
                        $row = mysqli_fetch_assoc($user_result);
                        $email = $row['email'];
                        $name = $row['name'];
                        $offset = $row['timezone_offset'];
                        $listings = $row['listings'];
                        $gender = $row['gender'];
                        $preferred_title = $row['preferred_title'];
                        echo ("<form action='profile.php' method='post'>");
                            echo ("<input type='hidden' name='user_id' value='$user_id'>");
                            echo ("<input type='hidden' name='action' value='edit'>");
                            echo ("<table>");
                            echo ("<tr><td colspan='2'><h2>Main Profile Info:</h2></td></tr>");
                                echo ("<tr><td><label? for='email'>Email Address:</label?</td><td><input type='email' name='email' id='email' value='$email'></td></tr>");
                                echo ("<tr><td colspan='2'>Changing your email address also changes the email address for login</td></tr>");
                                echo ("<tr><td><label for='name'>Your Name:</label></td><td><input type='text' name='name' id='name' value='$name'>");
                                echo ("<tr><td><label for='offset'>Your Offset from GMT (standard time):</label></td><td><input type='number' min='-14' max='14' name='offset' id='offset' value='$offset'>");
                                echo ("<tr><td colspan='2'>As an example, US pacific is -8, mountain is -7, central is -6, and eastern is -5 </td></tr>");
                                if ($user_type == "1") {
                                    echo ("<tr><td colspan='2'><H2>Connection Search Info</h2></td></tr>");
                                    echo ("<tr><td colspan='2'><p>The information below will determine if, and how you show up to other people.  If you select &quot;I will manually connect&quote; then you will not show up in search and will have to manually connect to people using their email address.  If you select either of the other two options then you will show up in search listings and may receive connection requests from people you do not know.</td></tr>");
                                    echo ("<tr><td>I want someone to give tasks to</td><td>");
                                    echo ("<tr><td colspan='2'>");
                                        echo ("<fieldset data-role='controlgroup'>");
                                            echo ("<legend>Tasks:</legend>");
                                            if ($listings == "1") {
                                                echo ("<input type='radio' name='connection' id='connection-a' value='1' checked='checked'>");
                                            } else {
                                                echo ("<input type='radio' name='connection' id='connection-a' value='1'>");
                                            }
                                            echo ("<label for='connection-a'>Do not have me show up in connection listings, I will connect manually</label>");
                                            if ($listings == "2") {
                                                echo ("<input type='radio' name='connection' id='connection-b' value='2' checked='checked'>");
                                            } else {
                                                echo ("<input type='radio' name='connection' id='connection-b' value='2'>");
                                            }
                                            echo ("<label for='connection-b'>I want to show up in the connection listings so I can give tasks to others</label>");
                                            if ($listings == "3") {
                                                echo ("<input type='radio' name='connection' id='connection-c' value='3' checked='checked'>");
                                            } else {
                                                echo ("<input type='radio' name='connection' id='connection-c' value='3'>");
                                            }
                                            echo ("<label for='connection-c'>I want to show up in the connection listings so I can receive tasks from others</label>");
                                        echo ("</fieldset>");
                                    echo ("</td></tr>");
                                    echo ("<tr><td><label for='gender'>My Gender</label></td><td><input type='text' name='gender' id='gender' value='$gender'></td></tr>");
                                    echo ("<tr><td><label for='preferred_title'>Title I Want To Be Called</label></td><td><input type='text' name='preferred_title' id='preferred_title' value='$preferred_title'></td></tr>");
                                }
                                echo ("<tr><td colspan='2'><input type='submit' name='submit' value='Submit'></td></tr>");
                            echo ("</table>");
                        echo ("</form>");
                        echo ("IF you would like to change your password, do that using the password reset function available from <a href='login.php'>the login page</a>");
                    }
                } 
                    if ($action === "edit") {
                        $name = mysqli_real_escape_string($link, htmlspecialchars($_POST['name']));
                        $email = mysqli_real_escape_string($link, htmlspecialchars($_POST['email']));
                        $offset = mysqli_real_escape_string($link, htmlspecialchars($_POST['offset']));
                        $form_id = mysqli_real_escape_string($link, htmlspecialchars($_POST['user_id']));
                        $connection = mysqli_real_escape_string($link, htmlspecialchars($_POST['connection']));
                        $gender = mysqli_real_escape_string($link, htmlspecialchars($_POST['gender']));
                        $preferred_title = mysqli_real_escape_string($link, htmlspecialchars($_POST['preferred_title']));
                        if ($form_id == $user_id) {
                            echo ("<form action='profile.php' method='post'>");
                            echo ("<input type='hidden' name='name' value='$name'>");
                            echo ("<input type='hidden' name='email' value='$email'>");
                            echo ("<input type='hidden' name='offset' value='$offset'>");
                            echo ("<input type='hidden' name='user_id' value='$user_id'>");
                            echo ("<input type='hidden' name='connection' value='$connection'>");
                            echo ("<input type='hidden' name='gender' value='$gender'>");
                            echo ("<input type='hidden' name='preferred_title' value='$preferred_title'>");
                            echo ("<input type='hidden' name='action' value='update'>");
                                echo ("<h2>Confirm setting your profile to:</h2>");
                                echo ("<table>");
                                    echo ("<tr><td>Your Email:</td><td>$email</td></tr>");
                                    echo ("<tr><td>Your Name:</td><td>$name</td></tr>");
                                    echo ("<tr><td>Your Offset:</td><td>$offset</td></tr>");
                                    echo ("<tr><td>Your Gender:</td><td>$gender</td></tr>");
                                    echo ("<tr><td>Your Preferred Title:</td><td>$preferred_title</td></tr>");
                                    if ($connection == "1"){
                                        echo ("<tr><td>Connection Listings:</td><td>Do Not Show In Listings</td></tr>");
                                    }
                                    if ($connection == "2"){
                                        echo ("<tr><td>Connection Listings:</td><td>Show to give tasks to others</td></tr>");
                                    }
                                    if ($connection == "3"){
                                        echo ("<tr><td>Connection Listings:</td><td>Show to receive tasks from others</td></tr>");
                                    }
                                    echo ("<tr><td colspan='2'><input type='submit' name='submit' value='Yes, Do It'></td></tr>");
                                echo ("</table>");
                            echo ("</form>");
                            echo ("<a href='index.php?ref=fromprofile' class='ui-btn ui-corner-all ui-btn-icon-left ui-icon-forbidden'>Return Home Without Making Any Changes</a>");
                        } else {
                            echo ("Fatal Error");
                        }
                    }
                    if ($action === "update") {
                        $name = mysqli_real_escape_string($link, htmlspecialchars($_POST['name']));
                        $email = mysqli_real_escape_string($link, htmlspecialchars($_POST['email']));
                        $offset = mysqli_real_escape_string($link, htmlspecialchars($_POST['offset']));
                        $form_id = mysqli_real_escape_string($link, htmlspecialchars($_POST['user_id']));
                        $connection = mysqli_real_escape_string($link, htmlspecialchars($_POST['connection']));
                        $gender = mysqli_real_escape_string($link, htmlspecialchars($_POST['gender']));
                        $preferred_title = mysqli_real_escape_string($link, htmlspecialchars($_POST['preferred_title']));
                        if ($user_id == $form_id) {
                            $upd_query = "update users set email = '$email', name = '$name', timezone_offset = $offset, gender='$gender', preferred_title='$preferred_title', listings=$connection where user_id = $user_id";
                            $upd_result = mysqli_query($link, $upd_query);
                            echo ("<h2>Profile changes made</h2><p>You can now <a href='index.php?ref=fromprofileupdate'>Return to the home page</a></p>");
                            // echo ("<p>$upd_query</p>");
                        } else {
                            echo ("Fatal Error");
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