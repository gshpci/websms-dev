<?php
require_once 'config.inc.php';

if (!isset($_SESSION['station'])) {
    header("location: index.php");
    exit;
}

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 28800)) {
    // last request was more than 8 hours ago
    session_unset();     // unset $_SESSION variable for the run-time
    session_destroy();   // destroy session data in storage
    header("location: index.php");
}
$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp

$from_name = $_SESSION['station'];

require_once 'PHPMailerAutoload.php';

$sel_id = '';
$sel_Name = '';
$sel_Number = '';

$port = '';
$u = CONF_DB_USER;
$p = CONF_DB_PASS;
$h = CONF_WEBHOST;
$dbase = CONF_DB_NAME;

//connection to the database
$conn = mysql_connect($h, $u, $p)
 or die("Unable to connect to MySQL");

$selected = mysql_select_db($dbase, $conn)
  or die("Could not select database");

$CFG['From_Email'] = CONFG_FROMEMAIL;
$CFG['To_Name'] = CONFG_TONAME;
$CFG['To_Email'] = CONFG_TOEMAIL;
$CFG['cc_Email'] = CONFG_CCEMAIL;

$CFG['smtp_debug'] = 1; //0 == off, 1 for client output, 2 for client and server
$CFG['smtp_debugoutput'] = 'html';
$CFG['smtp_server'] = CONF_WEBHOST;
$CFG['smtp_port'] = CONFG_PORT;
$CFG['smtp_authenticate'] = true;
$CFG['smtp_username'] = CONFG_USERNAME;
$CFG['smtp_password'] = CONFG_PASSWORD;
$CFG['smtp_secure'] = 'SSL';

$from_email = (isset($_POST['From_Email'])) ? $_POST['From_Email'] : $CFG['From_Email'];
$to_name = (isset($_POST['To_Name'])) ? $_POST['To_Name'] : $CFG['To_Name'];
$to_email = (isset($_POST['To_Email'])) ? $_POST['To_Email'] : $CFG['To_Email'];
$cc_email = (isset($_POST['cc_Email'])) ? $_POST['cc_Email'] : $CFG['cc_Email'];
$bcc_email = (isset($_POST['bcc_Email'])) ? $_POST['bcc_Email'] : '';
$subject = (isset($_POST['Subject'])) ? $_POST['Subject'] : '';
$message = (isset($_POST['Message'])) ? $_POST['Message'] : '';
$test_type = (isset($_POST['test_type'])) ? $_POST['test_type'] : 'smtp';
$smtp_debug = (isset($_POST['smtp_debug'])) ? $_POST['smtp_debug'] : $CFG['smtp_debug'];
$smtp_server = (isset($_POST['smtp_server'])) ? $_POST['smtp_server'] : $CFG['smtp_server'];
$smtp_port = (isset($_POST['smtp_port'])) ? $_POST['smtp_port'] : $CFG['smtp_port'];
$smtp_secure = strtoupper((isset($_POST['smtp_secure'])) ? $_POST['smtp_secure'] : $CFG['smtp_secure']);
$smtp_authenticate = (isset($_POST['smtp_authenticate'])) ?
    $_POST['smtp_authenticate'] : $CFG['smtp_authenticate'];
$authenticate_password = (isset($_POST['authenticate_password'])) ?
    $_POST['authenticate_password'] : $CFG['smtp_password'];
$authenticate_username = (isset($_POST['authenticate_username'])) ?
    $_POST['authenticate_username'] : $CFG['smtp_username'];

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>SMS</title>

    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>


    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/sms.css" type="text/css" rel="stylesheet">


  <!-- -->


<script src="js/jquery-1.9.1.min.js"></script>

<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">

<link rel="stylesheet" href="css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

<!-- Latest compiled and minified JavaScript -->
<script src="js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>


<!-- -->

    <script>
        function startAgain() {
            var post_params = {
                "From_Name": "<?php echo $from_name; ?>",
                "From_Email": "<?php echo $from_email; ?>",
                "To_Name": "<?php echo $to_name; ?>",
                "To_Email": "<?php echo $to_email; ?>",
                "cc_Email": "<?php echo $cc_email; ?>",
                "bcc_Email": "<?php echo $bcc_email; ?>",
                "Subject": "<?php echo $subject; ?>",
                "Message": "<?php echo $message; ?>",
                "test_type": "<?php echo $test_type; ?>",
                "smtp_debug": "<?php echo $smtp_debug; ?>",
                "smtp_server": "<?php echo $smtp_server; ?>",
                "smtp_port": "<?php echo $smtp_port; ?>",
                "smtp_secure": "<?php echo $smtp_secure; ?>",
                "smtp_authenticate": "<?php echo $smtp_authenticate; ?>",
                "authenticate_username": "<?php echo $authenticate_username; ?>",
                "authenticate_password": "<?php echo $authenticate_password; ?>"
            };

            var resetForm = document.createElement("form");
            resetForm.setAttribute("method", "POST");
            resetForm.setAttribute("path", "index.php");

            for (var k in post_params) {
                var h = document.createElement("input");
                h.setAttribute("type", "hidden");
                h.setAttribute("name", k);
                h.setAttribute("value", post_params[k]);
                resetForm.appendChild(h);
            }

            document.body.appendChild(resetForm);
            resetForm.submit();
        }

        function showHideDiv(test, element_id) {
            var ops = {"smtp-options-table": "smtp"};

            if (test == ops[element_id]) {
                document.getElementById(element_id).style.display = "block";
            } else {
                document.getElementById(element_id).style.display = "none";
            }
        }

    </script>

</head>
<p style="font-size:15px; color:#fff; margin-right:10px;">Signed in as <strong><?php echo $from_name;?><strong></p><body id="body">
<nav class="navbar navbar-default">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <img class="navbar-brand" src="images/1.png">
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav">
        <input type="hidden" id="From_Name" name="From_Name" value="<?php echo $from_name ?>">

        <?php
        $menu_links = array('sms' => 'Compose', 'inbox' => 'Inbox', 'sent' => 'Sent');
        foreach ($menu_links as $ln => $val) {
        ?>
            <li style="font-size:15px;" class="active">
            <form action = "action.php" method="post">
                <input type="hidden" id="source" name="source" value="<?php echo $_SERVER['SCRIPT_NAME'] ?>">
                <input type="hidden" id="action" name="action" value="<?php echo $ln ?>.php">
                <input type="hidden" id="station" name="station" value="<?php echo $from_name ?>">
                <input type="submit" value="<?php echo $val ?>" style="background:transparent; border:none; padding:15px 15px 15px 15px; font-weight: bold;">
            </form>
            </li>
        <?php
        }
        ?>
      </ul>

      <ul class="nav navbar-nav navbar-right">
        <br>
        <li>
        <form action = "action.php" method="post">
            <input type="hidden" id="source" name="source" value="<?php echo $_SERVER['SCRIPT_NAME'] ?>">
            <input type="hidden" id="action" name="action" value="Logout">
            <input type="hidden" id="station" name="station" value="<?php echo $from_name ?>">
            <span class="glyphicon glyphicon-off" style="background:transparent; border:none; font-weight: bold; color:red; font-size: 20px;"></span>
            <input type="submit" value="Sign Out!" style="background:transparent; border:none; font-weight: bold; color:red; font-size: 16px;">
        </form>
        </li>
      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>
