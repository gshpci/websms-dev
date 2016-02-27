<?php
require 'header.php';
require_once 'config.inc.php';
?>
<script type="text/javascript" src="js/jquery.min.js"></script>

<script type="text/javascript">

if (document.addEventListener) {
    document.addEventListener('load', activityHandler.flagActivity, false);
}
else if (document.attachEvent) {
    document.attachEvent('load', activityHandler.flagActivity);
}

</script>

<?php

require_once "Imap.php";

$mailbox = CONF_WEBHOST.':993';
$username = CONFG_IMAP_USER;
$password = CONFG_PASSWORD;
$encryption = 'ssl'; // or ssl or ''

// open connection
$imap = new Imap($mailbox, $username, $password, $encryption);

// stop on error
if($imap->isConnected()===false)
    die($imap->getError());

// get all folders as array of strings
//$folders = $imap->getFolders();
//foreach($folders as $folder)
//    echo $folder.'<br>';

// select folder Inbox
$imap->selectFolder('INBOX');

// count messages in current folder
$overallMessages = $imap->countMessages();
$unreadMessages = $imap->countUnreadMessages();

//echo "Message Count: ".$overallMessages." | ".$unreadMessages."<br>";

// fetch all messages in the current folder
$emails = $imap->getMessages();

//var_dump($emails);
$datetime = '';
$subject = '';
$uid = '';
$sms = '';

$col = 0;
$cnt = 0;

echo "<div id='load' class ='container-fluid'>";
//echo "<p style='color:#fff; font-size:16px;'> Emails: <b>".count($emails)."</b> <p>";
?>
<script>
    $(window).ready(function(){
        //alert('Hi');
        activityHandler.start();
    });
</script>

<fieldset>
    <h5 style="color:white;"><blockquote>
        <span class="glyphicon glyphicon-envelope">&nbsp;<strong>Inbox</strong>
    </blockquote>
</fieldset>

<table class="rwd-table" style="width: 100%">
    <thead>
        <tr>
            <th class = "listtd" style="width: 15%;">Datetime</th>
            <th class = "listtd" style="width: 25%; text-align: left">From</th>
            <th class = "listtd" style="width: 40%; text-align: left">SMS</th>
            <th class = "listtd" style="width: 20%; text-align: left">Action</th>
        </tr>
    </thead>
    <tbody>
<?php
foreach ($emails as $em) {
    //echo "<br>";
    $cnt++;
    $col = 0;
    echo "<tr class = 'listtr' >";
?>

<?php
    $u = CONF_DB_USER;
    $p = CONF_DB_PASS;
    $h = CONF_WEBHOST;
    $dbase = CONF_DB_NAME;

    mysql_connect($h, $u, $p) or die (mysql_error());
    mysql_select_db($dbase) or die (mysql_error());

    foreach ($em as $k => $val) {
        //echo "$k :- $val<br>";
        if ($k == "date") {
            $d = new DateTime($val);
            $val = $d->format('m-d-Y H:i:s');
            $datetime = $val;
        } else if ($k == "subject") {



            $mob_no = substr($val,12,10);
            $query = mysql_query("select concat(Lastname,', ', Firstname) as name from phonebook where Number = concat('0', '$mob_no') || Number = concat('+63', '$mob_no')");

            if ($row = mysql_fetch_array($query)) {
                $subject = $row['name']." <0".substr($val,12,10).">";
            } else {
                $subject = "UNKNOWN <0".substr($val,12,10).">";
            }
        } else if ($k == "uid") {
            $uid = $val;
        } else if ($k == "body") {
            $sms = $val;
        }
    }
?>

    <td class = "listtd" style="text-align: center"><?php echo $datetime ?></td>
    <td class = "listtd"><?php echo $subject ?></td>
    <td class = "listtd">
        <div style="width: 100%; float:right;" >
            <?php
                if(strlen($sms) > 40){
                    echo substr($sms, 0, 40)."<span style='font-size:14 px; color:red;'> ... (truncated). </span>";
            ?>
            &nbsp;
            <button onclick="activityHandler.stop();" style="text-decoration:none;cursor:pointer;" class = "btn btn-info" data-toggle="modal" data-target="#ViewSMS-<?php echo $uid; ?>">
                <span class="glyphicon glyphicon-eye-open" aria-hidden="true" style = "color:#fff;"></span>
                View All
            </button >&nbsp;&nbsp;

            <?php
                }else{
                    echo substr($sms, 0, 40);
                }
            ?>
        </div>
    </td>
    <td class = "listtd">
        <div style="width: 100%; float:right;" >
            &nbsp;&nbsp;
            <button onclick="activityHandler.stop();" style="text-decoration:none;cursor:pointer;" class = "btn btn-info" data-toggle="modal" data-target="#RelpySMS-<?php echo $uid; ?>">
                <span class="glyphicon glyphicon-envelope" aria-hidden="true" style = "color:#fff;"></span>
                Reply
            </button >
        </div>
    </td>

<?php
    echo "</tr>";
?>

<!-- View Modal -->
<div class="modal fade" id="ViewSMS-<?php echo $uid; ?>" tabindex="-<?php echo $uid; ?>" role="dialog" aria-labelledby="myModalLabel-<?php echo $uid; ?>" aria-hidden="true">
      <div class="modal-dialog">
    <div class="modal-content">

            <div class="modal-header">
                    <span id="IL_AD4" class="IL_AD">
                <a onclick="activityHandler.start();" type="button" class="close glyphicon glyphicon-remove" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">

                    </span>
                </a>
                </span>

                    <h4 class="modal-title" id="myModalLabel-<?php echo $uid; ?>">SMS Details</h4>
            </div>

        <div class="modal-body" >
            <form action="delete.php" method="post">
                <input type="hidden" id="id" name="id" value="<?php echo $uid; ?>">

                <h4 class="modal-title" id="lblmessage">Message &nbsp;</h4>
                    <textarea type="text" class="form-control" id="message" name="message" value="<?php echo $sms; ?>" style="height:33em" disabled><?php echo $sms; ?></textarea>

        </div>


            <div class="modal-footer">
                    <button onclick="activityHandler.start();" type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                <!--
                <input type="submit" value="Yes, Delete!" class="btn btn-danger">
                -->
            </div>
            </div>
        </form>
     </div>
    </div>
</div>
<!-- End View Modal -->

<!-- Reply Modal -->
<div class="modal fade" id="RelpySMS-<?php echo $uid; ?>" tabindex="-<?php echo $uid; ?>" role="dialog" aria-labelledby="myModalLabel-<?php echo $uid; ?>" aria-hidden="true">
      <div class="modal-dialog">
    <div class="modal-content">

            <div class="modal-header">
                    <span id="IL_AD4" class="IL_AD">
                <a onclick="activityHandler.start();" type="button" class="close glyphicon glyphicon-remove" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">

                    </span>
                </a>
                </span>

                    <h4 class="modal-title" id="myModalLabel-<?php echo $uid; ?>">SMS Details</h4>
            </div>

        <div class="modal-body" >


        <form action = "sendfunction.php" method="POST" >

                <input type="hidden" class="form-control" name="From_Name" id="From_Name" value="<?php echo $from_name; ?>" style="width:120%;height:2em">
                <input type="hidden" class="form-control" id="From_Email" name="From_Email" value="<?php echo $from_email; ?>" style="width:120%;height:2em">
                <input type="hidden" class="form-control" id="To_Name" name="To_Name" value="<?php echo $to_name; ?>" style="width:120%;height:2em">
                <input type="hidden" class="form-control" id="To_Email" name="To_Email" value="<?php echo $to_email; ?>" style="width:120%;height:2em">
                <input type="hidden" class="form-control" id="cc_Email" name="cc_Email" value="<?php echo $cc_email; ?>" style="width:120%;height:2em">
                <input type="hidden" class="form-control" id="bcc_Email" name="bcc_Email" value="<?php echo $bcc_email; ?>" style="width:120%;height:2em">

                <label class="control-label" for="Message">Mobile :</label>

                <select size="1" class="form-control" id="Subject" name="Subject">
                    <option value="0<?php echo $mob_no; ?>"><?php echo $subject; ?></option>
                </select>

                <br><br>
                <label class="control-label" for="Message">Message :</label>
                <textarea type="text" class="form-control" id="Message" name="Message" value="<?php echo $message; ?>" style="height:30em" required></textarea>


            <div class="modal-footer">
                    <button onclick="activityHandler.start();" type="button" class="btn btn-danger" data-dismiss="modal">Close</button>

                <input type="submit" value="Send" name="submit" class="colrite btn btn-success pull-right">
            </div>
        </form>

     </div>
    </div>
</div>
<!-- End Reply Modal -->


<?php
}

//echo "Emails Read: $cnt";

?>
    </tbody>
</table>
</div>

