<?php
    ob_start();
        //If the HTTPS is not found to be "on"
        if(!isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] != "on") {
            //Tell the browser to redirect to the HTTPS URL.
            header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"], true, 301);
            //Prevent the rest of the script from executing.
            exit;
        }
    ob_end_flush();
?>
<html>
    <head>
    <meta name="viewport" content="width=device-width, initial-scale=1"> 
        <link rel="stylesheet" href="https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.css" />
        <script src="https://code.jquery.com/jquery-1.11.1.min.js"></script>
        <script src="https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js"></script>
        <link rel="stylesheet" href="themes/MT3.min.css" />
        <link rel="stylesheet" href="themes/jquery.mobile.icons.min.css" />
        <title>Master Tasker</title>
    </head>
    <body>
        <div data-role="header">
            <h1>Master Tasker Login</h1>
        </div>

        <div data-role="main" class="ui-content"><div class="ui-body ui-body-a ui-corner-all">
            <h4>Login</h4>
            <form action="signin.php" method="post">
                <table>
                    <tr><td><label for="email">Email:</label></td><td><input type="email" id="email" name="email" placeholder="your email"></td></tr>
                    <tr><td><label for="password">Password:</label></td><td><input type="password" id="password" name="password" placeholder="your password"></td></tr>
                    <tr><td colspan='2'><input type="submit" id="submit" name="submit" value="Submit"></td></tr>
                </table>
            </form>
            <p> Don't have a login? <a href="register.php">Register</a> an account.</p>
            <p><a href="reset.php">Forgot your password?</a></p>
        </div></div><!-- /content -->
        <div data-role="footer">
        <?php
                include 'footer.php';
            ?>
        </div>
    </body>
</html>