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
                $user_type = $user_data->data->user_type;
                if (isset($_POST['submit'])) {
                    $connect_email = $_POST['connect_email'];
                    $connect_email = mysqli_real_escape_string($link, htmlspecialchars($connect_email));
                    $person_is = mysqli_real_escape_string($link, htmlspecialchars($_POST['person_is']));
                    $connect_title = mysqli_real_escape_string($link, htmlspecialchars($_POST['connect_title']));
                    $user_title = mysqli_real_escape_string($link, htmlspecialchars($_POST['user_title']));
                    $query = "select user_id, email from users where email like '$connect_email'";
                    $result = mysqli_query ($link, $query);
                    $num_rows = mysqli_num_rows ($result);
                    if ($num_rows == 1) {
                        $userfound = true;
                        $row = mysqli_fetch_assoc ($result);
                        $connect_id = $row['user_id'];
                        if ($person_is === "above") {
                            $connect_query = "insert into user_xref (superior_id, subordinate_id, superior_title, subordinate_title, submitted_by) VALUES ($connect_id, $user_id, '$connect_title', '$user_title', 'sub', $user_id)";
                            $result = mysqli_query($link, $connect_query);
                            $subject = '[Master Tasker] Someone wants to connect with you';
                            $message = 'Someone wants to connect with you on Master Tasker.  Find out who!' . "\r\n" . "http://dewdevelopment.com/MasterTasker/index.php";
                            $headers = 'From: Master Tasker Support <support@dewdevelopment.com>' . "\r\n" . 'Reply-To: support@dewdevelopment.com' . "\r\n" . 'X-Mailer: PHP/' . phpversion();
                            mail($email, $subject, $message, $headers);
                        }
                        if ($person_is === "below") {
                            $connect_query = "insert into user_xref (superior_id, subordinate_id, superior_title, subordinate_title, submitted_by) VALUES ($user_id, $connect_id, '$user_title', '$connect_title', 'sup', $user_id)";
                            $result = mysqli_query($link, $connect_query);
                            $subject = '[Master Tasker] Someone wants to connect with you';
                            $message = 'Someone wants to connect with you on Master Tasker.  Find out who!' . "\r\n" . "http://dewdevelopment.com/MasterTasker/index.php";
                            $headers = 'From: Master Tasker Support <support@dewdevelopment.com>' . "\r\n" . 'Reply-To: support@dewdevelopment.com' . "\r\n" . 'X-Mailer: PHP/' . phpversion();
                            mail($email, $subject, $message, $headers);
                        }
                    } else {
                        $userfound = false;
                    }
                }
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
                <h1>Master Tasker</h1>
                <a href="index.php" class="ui-btn ui-shadow ui-corner-all ui-icon-home ui-btn-icon-notext">Home</a>
            </div>
            <div data-role="main" class="ui-content"><div class="ui-body ui-body-a ui-corner-all">
            <p><strong>NOTE:</strong> This is intended to connect two different people.  It does not function properly trying to connect to yourself. If you have updated your profile to show if you are looking to give or receive tasks then you can <a href='connection_listings.php'>Check Out The Connection Listings</a> to see if there is someone to connect to if you do not have someone yet.</p>
            <?php 
                if (!isset($_POST["submit"])) {
                    if (!isset($_GET['action'])){
                        $action = "new";
                    } else {
                        $action = mysqli_real_escape_string($link, htmlspecialchars($_GET['action']));
                    }
                    if ($action === "from") {
                        // grab url vars which may be present if person clicked on a connection listing
                        $ref = mysqli_real_escape_string($link, htmlspecialchars($_GET['ref']));
                        $person_is = mysqli_real_escape_string($link, htmlspecialchars($_GET['person_is']));
                        $connect_title = mysqli_real_escape_string($link, htmlspecialchars($_GET['connect_title']));
                        if ($person_is === "above"){
                        }
                        if ($person_is === "below"){

                        }
                        echo ("<form action='connect.php' method='post'>");
                            echo ("<table style='width: 100%;'>");
                                echo ("<tr><td><label for='connect_email'>Email Address:</td><td><input type='text' name='connect_email' id='connect_email' value='$connect_email'></td></tr>");
                                echo ("<tr><td colspan='2'>");
                                    echo ("<fieldset data-role='controlgroup'>");
                                        echo ("<legend>Person is:</legend>");
                                        if ($person_is == "below") {
                                            echo ("<input type='radio' name='person_is' id='person_is_above' value='above'>");
                                        } else {
                                            echo ("<input type='radio' name='person_is' id='person_is_above' value='above' checked='checked'>");
                                        }
                                        echo ("<label for='person_is_above'>Above you</label>");
                                        if ($person_is == "below") {
                                            echo ("<input type='radio' name='person_is' id='person_is_below' value='below' checked='checked'>");
                                        } else {
                                            echo ("<input type='radio' name='person_is' id='person_is_below' value='below'>");
                                        }
                                        echo ("<label for='person_is_below'>Below you</label>");
                                    echo ("</fieldset>");
                                echo ("</td></tr>");
                                echo ("<tr><td><label for='connect_title'>They are to you (title):</td><td><input type='text' name='connect_title' id='connect_title' value='$connect_title'></td></tr>");
                                if ($user_type == "1") {
                                    echo ("<tr><td colspan='2'>Examples if person is above: Sir, Dom, Master<br />Examples if person is below: submissive, slave, property</td></tr>");
                                } else {
                                    echo ("<tr><td colspan='2'>Examples if person is above you: Director, manager, supervisor<br >Examples if person is below: Employee, direct report, assistant</td></tr>");
                                }
                            
                                echo ("<tr><td><label for='user_title'>You are to them (title):</td><td><input type='text' name='user_title' id='user_title'></td></tr>");
                                echo ("<tr><td colspan='2'>Same examples as above</td></tr>");
                                echo ("<tr><td colspan='2'><input type='submit' name='submit' value='Submit Connection'></td></tr>");
                                echo ("<tr><td colspan='2'>After you submit the connection the user need to confirm they want to connect with you.</td></tr>");
                            echo ("</table>");
                        echo ("</form>");
                    }
                    if ($action == "new") {
                        // grab url vars which may be present if person clicked on a connection listing
                        $connect_email = mysqli_real_escape_string($link, htmlspecialchars($_GET['connect_email']));
                        $person_is = mysqli_real_escape_string($link, htmlspecialchars($_GET['person_is']));
                        $connect_title = mysqli_real_escape_string($link, htmlspecialchars($_GET['connect_title']));
                        echo ("<form action='connect.php' method='post'>");
                            echo ("<table style='width: 100%;'>");
                                echo ("<tr><td><label for='connect_email'>Email Address:</td><td><input type='text' name='connect_email' id='connect_email' value='$connect_email'></td></tr>");
                                echo ("<tr><td colspan='2'>");
                                    echo ("<fieldset data-role='controlgroup'>");
                                        echo ("<legend>Person is:</legend>");
                                        if ($person_is == "below") {
                                            echo ("<input type='radio' name='person_is' id='person_is_above' value='above'>");
                                        } else {
                                            echo ("<input type='radio' name='person_is' id='person_is_above' value='above' checked='checked'>");
                                        }
                                        echo ("<label for='person_is_above'>Above you</label>");
                                        if ($person_is == "below") {
                                            echo ("<input type='radio' name='person_is' id='person_is_below' value='below' checked='checked'>");
                                        } else {
                                            echo ("<input type='radio' name='person_is' id='person_is_below' value='below'>");
                                        }
                                        echo ("<label for='person_is_below'>Below you</label>");
                                    echo ("</fieldset>");
                                echo ("</td></tr>");
                                echo ("<tr><td><label for='connect_title'>They are to you (title):</td><td><input type='text' name='connect_title' id='connect_title' value='$connect_title'></td></tr>");
                                if ($user_type == "1") {
                                    echo ("<tr><td colspan='2'>Examples if person is above: Sir, Dom, Master<br />Examples if person is below: submissive, slave, property</td></tr>");
                                } else {
                                    echo ("<tr><td colspan='2'>Examples if person is above you: Director, manager, supervisor<br >Examples if person is below: Employee, direct report, assistant</td></tr>");
                                }
                            
                                echo ("<tr><td><label for='user_title'>You are to them (title):</td><td><input type='text' name='user_title' id='user_title'></td></tr>");
                                echo ("<tr><td colspan='2'>Same examples as above</td></tr>");
                                echo ("<tr><td colspan='2'><input type='submit' name='submit' value='Submit Connection'></td></tr>");
                                echo ("<tr><td colspan='2'>After you submit the connection the user need to confirm they want to connect with you.</td></tr>");
                            echo ("</table>");
                        echo ("</form>");
                    }
                } else {
                    if ($userfound) {
                        echo ("<h2>Connection Pending</h2>");
                        echo ("<p>Once the user has logged in and confirmed the connection they will show up as a user you can interact with and in your home screen feed.</p>");
                        echo ("<p>You can <a href='index.php'>return to the home screen</a> or you can <a href='connect.php'>connect with someone else</a></p>");
                        // echo ("<p> $connect_query </p>");
                    } else {
                        echo ("<h2>User not found</h2>");
                        echo ("<p>We could not find a user with the email address you entered in our system.  You may need to contact them and ask them to join.</p>");
                        echo ("<p>You can <a href='index.php'>return to the home screen</a> or you can <a href='connect.php'>connect with someone else</a></p>");
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