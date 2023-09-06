<?php
    ob_start();
        //If the HTTPS is not found to be "on"
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

    
        include 'config.php';
        $link = mysqli_connect($host, $dbuser, $dbpass, $dbname);
        if (isset($_GET['action'])) {
            $action = mysqli_real_escape_string($link, htmlspecialchars($_GET['action']));
            if ($action === "send"){
                $email = mysqli_real_escape_string($link, htmlspecialchars($_GET['email']));
                $getEmQuery = "select user_id from users where email like '$email'";
                $getEmResult = mysqli_query($link, $getEmQuery);
                if (mysqli_num_rows($getEmResult) == 1) {
                    // send reset email link with ID labeled item
                    $hash = mysqli_real_escape_string($link, htmlspecialchars($_GET['hash']));
                    $subject = 'Password Reset Link';
                    $message = 'To reset your password click on the link below' . "\r\n" . "http://dewdevelopment.com/HomeService/reset.php?action=reset&hash=chi937djkieydyns736djleuidnmw83490dhjk36&email=$email";
                    $headers = 'From: Danny <danny@dewdevelopment.com>' . "\r\n" . 'Reply-To: danny@dewdevelopment.com' . "\r\n" . 'X-Mailer: PHP/' . phpversion();
                    mail($email, $subject, $message, $headers);
                    $send = "yes";
                } else {
                    $send = "crash";
                }
            }
            if ($action == "reset") {
                $email = mysqli_real_escape_string($link, htmlspecialchars($_GET['email']));
                $hash = mysqli_real_escape_string($link, htmlspecialchars($_GET['hash']));
                if ($hash = "chi937djkieydyns736djleuidnmw83490dhjk36") {
                    $resetQuery = "Select * from users where email like '$email'";
                    $resetResult = mysqli_query($link, $resetQuery);
                    if (mysqli_num_rows($resetResult) == 1) {
                        $Row = mysqli_fetch_assoc($resetResult);
                        $email = $Row['email'];
                        $send = "edit";
                    } else {
                        $send = "crash";
                    }
                }
            }
        }
        if (isset($_POST['doit'])) {
            $doit = mysqli_real_escape_string($link, htmlspecialchars($_POST['doit']));
            if ($doit == "modify") {
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $email = mysqli_real_escape_string($link, htmlspecialchars($_POST['email']));
                $updQuery = "update users set password = '$password' where email like '$email'";
                $updResult = mysqli_query($link, $updQuery);
                $send = "complete";
            } else {
                $send = "crash";
            }
        }
        

        // header("Location: index.php");
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
                <h1>Password Reset</h1>
                <a href="index.php" class="ui-btn ui-shadow ui-corner-all ui-icon-home ui-btn-icon-notext">Home</a>
            </div>

        
            <div data-role="main" class="ui-content"><div class="ui-body ui-body-a ui-corner-all">

                <?php
                if (isset($_Get['action']) != true && isset($send) != true) {
                ?>
                    <h2>Enter the email address you registered with to receive a reset link</h2>
                    <form action="reset.php" method="get">
                        <input type="hidden" name="action" value="send">
                        <label for="email">Email:</label> <input type="email" name="email" id="email" placeholder="your@email.com"><br />
                        <input type="submit" name="submit" value="Submit">
                    </form>
                <?php
                }
                ?>

                <?php
                if ($send == "yes") {
                ?>
                    <h2>Reset link has been sent to your email</h2>
                <?php
                }
                if ($send == "crash") {
                ?>
                    <h2>Fatal error: contact support</h2>
                <?php
                }
                if ($send == "edit") {
                ?>
                    <h2>Reset your password</h2>
                    <form action="reset.php" method="post">
                        <input type="hidden" name="doit" value="modify">
                        <input type="hidden" name="email" value='<?php echo ("$email") ?>'>
                        Email: <?php echo ("$email") ?> <br />
                        <label for='password'>Password:</label> <input type='text' name='password' id='password'><br />
                        <input type="submit" name="submit" value="Update">
                    </form>
                <?php
                }
                if ($send == "complete"){
                ?>
                    <h2>Password Now Reset</h2>
                    Return to <a href="login.php">Login</a>
                <?php
                }
                ?>
            </div></div><!-- /content -->
        
            <div data-role="footer">
            <?php
                include 'footer.php';
            ?>
            </div>
        </div>
    </body>
</html>