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
                <h1>Help</h1>
                <a href="index.php" class="ui-btn ui-shadow ui-corner-all ui-icon-home ui-btn-icon-notext">Home</a>
            </div>
            <div data-role="main" class="ui-content"><div class="ui-body ui-body-a ui-corner-all">
                <p>The Master Tasker app was made to be fairly flexible.  It can allow someone to have both multiple people above them and multiple below them.  Further it also allows for someone to be both above someone and below them, switching (you just make the connection twice, once with you above the other person and once with you below).</p>
                <p>This is why a user account is not created with a title in the user name, or at least it should not be.  A title is created when you connect with someone.  At that time is when you say what the titles for each of you will be.</p>
                <p>The app is further set up so that a task is given by the person that is above; but saying the task has been started, leaving notes, and saying it has been completed is all done by the person that is below as that is the person actually performing the task.  However the person that is above gets to see everything that is done as it is being done.</p>
                <p>For those of you who will have a single person you are connected to the functionality being the same between what is on your main feed compared to the functionality available when you click on the button associated to the person you are connected with may seem odd.</p>
                <p>However for those that have multiple people above them or below them (or both) this will make much more sense as it will allow you to see only the activity for that person.</p>
                <p>Further, for the person that has someone below them, it is only when viewing that person that title changes can be made or tasks deleted.</P>
                <p>Please also note that if you have someone below you, the only way you give the person a task is by clicking the connection button for that person in the top navigation.</p>
                <p><strong>Important Info:</strong> to make things a little more obvious, it is set up right now so that a task must be started before it can be completed.  In other words, if you are given a task, tap the "started" button and then begin the task; tapping "Mark Completed" when you have finished the task.  This will let the person above you see how long the task took you to accomplish.  Keep in mind you can also always leave notes as well.</p>
                <p><strong><em>FURTHER IMPORTANT NOTE:</em></strong>, once a task has been marked as failed, notes can no longer be left on that task.  So make sure you leave any notes BEFORE you tap the "failed" button.</p>
                <p>In fact because tasks can be deleted we recommend that you just play around with this tool for a little bit and sort of test drive the various pieces of functionality.</p>
                <p><strong>About Task Notes And Replies:</strong> You can add notes to a task and those notes can be replied to.  Understand that this is <em>deliberately</em> a simple system.  The person that was given the task can leave notes about it and the person that gave the task can reply to a note.  This system is <em>not</em> intended to be a robust chat facility.
                <p>If you have any questions or would like to request features improvements or additions please do not hesitate to email <a href="mailto:support@dewdevelopment.com">support</a> and we will answer any questions about Master Tasker that you have.</P>
            </div></div>
            <div data-role="footer">
            <?php
                include 'footer.php';
            ?>
            </div>
        </div>
    </body>
</html>