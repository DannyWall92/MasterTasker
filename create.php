<?php
    ob_start();
        //If the HTTPS is not found to be "on"
        if(!isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] != "on") {
            //Tell the browser to redirect to the HTTPS URL.
            header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"], true, 301);
            //Prevent the rest of the script from executing.
            exit;
        }
    
        include 'config.php';
        $link = mysqli_connect($host, $dbuser, $dbpass, $dbname);

        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $email = mysqli_real_escape_string($link, htmlspecialchars($_POST['email']));
        $name = mysqli_real_escape_string($link, htmlspecialchars($_POST['name']));
        $offset = mysqli_real_escape_string($link, htmlspecialchars($_POST['offset']));
        $user_type = mysqli_real_escape_string($link, htmlspecialchars($_POST['user-type']));
        $query1 = "insert into users (password, email, name, user_type, timezone_offset) VALUES ('$password', '$email', '$name', $user_type, $offset)";
        $result = mysqli_query($link, $query1);

        $subject = 'Confirm Your Master Tasker Account';
        $message = 'To use your Master Tasker account you must confirm your email address by clicking on the link below (please allow up to five minutes to receive the email):' . "\r\n" . "https://dewdevelopment.com/MasterTasker/confirm.php?email=$email";
        $headers = 'From: Master Tasker Support <support@dewdevelopment.com>' . "\r\n" . 'Reply-To: support@dewdevelopment.com' . "\r\n" . 'X-Mailer: PHP/' . phpversion();
        if (strlen($email) > 5) {
            mail($email, $subject, $message, $headers);
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
                    <h1>Confirmation</h1>
                </div>
                <div data-role="main" class="ui-content"><div class="ui-body ui-body-a ui-corner-all">
                    <h2>An email has been sent to you to confirm your email address. </h2>
                    <p>You entered the email address: <?php echo $email ?></p>
                    <p>You will not be able to use your Master Tasker account until the confirmation link has been clicked.</p>
                </div></div>
                <div data-role="footer">
                    <?php
                    include 'footer.php';
                    ?>
                </div>
            </div>
    </body>
</html>