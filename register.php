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
                <h1>Master Tasker (public beta) Registration</h1>
            </div>
        
            <div data-role="main" class="ui-content"><div class="ui-body ui-body-a ui-corner-all">
                <h4>Create Your Account</h4>
                <p>Important: In the "Name" field do not enter a title of any kind.  Your title is established later.  This should be your name only.</p>
                <form action="create.php" method="post">
                    <input type="hidden" name="c" id="c" value="<?php echo ($c) ?>">
                    <table border='0'>
                        <tr><td><label for="email">Email:</label></td><td><input type="text" id="email" name="email" placeholder="Your email address"></td></tr>
                        <tr><td><label for="name">Name:</label></td><td><input type="text" id="name" name="name" placeholder="Your name"></td></tr>
                        <tr><td colspan='2'>We recommend using your first name only</td></tr>
                        <tr><td><label for='offset'>Your Offset from GMT (standard time):</label></td><td><input type='number' name='offset' min='-14' max='14' id='offset'>
                        <tr><td colspan='2'>As an example, US pacific is -8, mountain is -7, central is -6, and eastern is -5 </td></tr>
                        <tr><td><label for="password">Password:</label></td><td><input type="text" id="password" name="password" placeholder="The password you want"></td></tr>
                        <tr><td colspan='2'>
                            <fieldset data-role="controlgroup">
                                <legend>User Type:</legend>
                                <input type="radio" name="user-type" id="radio-user-type-a" value="1" checked="checked">
                                <label for="radio-user-type-a">Personal: I will be giving or receiving tasks from family or friends</label>
                                <input type="radio" name="user-type" id="radio-user-type-b" value="2">
                                <label for="radio-user-type-b">Business: I will be giving or receiving tasks from business collegues</label>
                            </fieldset>
                        </td></tr>
                        <tr><td colspan='2'><input type="submit" id="submit" name="submit" value="Submit"></td></tr>
                    </table>
                </form>
            </div></div><!-- /content -->
        
            <div data-role="footer">
            <h4>By creating an account and using this app/site you agree to our terms of service, privacy policy, and cookie policy</h4>
            <?php
                include 'footer.php';
            ?>
            </div>
        </div>        
    </body>
</html>