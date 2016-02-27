<script  type="text/javascript" src="js/jquery.min.js"></script>

<?php
//require_once 'header.php';
require_once 'config.inc.php';
require_once "Imap.php";

$u = CONF_DB_USER;
$p = CONF_DB_PASS;
$h = CONF_WEBHOST;
$dbase = CONF_DB_NAME;

//connection to the database
$conn = mysql_connect($h, $u, $p)
 or die("Unable to connect to MySQL");
//echo "Connected to MySQL<br>";

mysql_select_db($dbase);
//select a database to work with
$selected = mysql_select_db($dbase, $conn)
  or die("Could not select examples");

$sql = "select *, timestampdiff(second, datetime,  concat(curdate(),' ', curtime())) from logstrigger order by datetime desc limit 1";

//execute the SQL query and return records
$result = mysql_query("$sql");

//fetch tha data from the database
if ($row = mysql_fetch_array($result)) {
    if ($row['action'] == "Logout") {
        echo "<script>window.location.href='index.php';</script>";
    } else {

        $sql = "SELECT station from logstrigger order by `datetime` desc limit 1";
        $result = mysql_query("$sql");

        //fetch tha data from the database
        if ($row = mysql_fetch_array($result)) {
            $from_name = (isset($_POST['From_Name'])) ? $_POST['From_Name'] : $row{'station'};
        }
    }

} else {
    $sql = "SELECT station from logstrigger order by `datetime` desc limit 1";
    $result = mysql_query("$sql");

    if ($row = mysql_fetch_array($result)) {
        $from_name = (isset($_POST['From_Name'])) ? $_POST['From_Name'] : $row{'station'};
    }
}

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
?>

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
            <button style="text-decoration:none;cursor:pointer;" class = "btn btn-info" data-toggle="modal" data-target="#ViewSMS-<?php echo $uid; ?>">
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
            <button style="text-decoration:none;cursor:pointer;" class = "btn btn-info" data-toggle="modal" data-target="#RelpySMS-<?php echo $uid; ?>">
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
                        <a type="button" class="close glyphicon glyphicon-remove" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true"></span>
                        </a>
                    </span>
                    <h4 class="modal-title" id="myModalLabel-<?php echo $uid; ?>">SMS Details</h4>
                </div>

                <div class="modal-body" >
                    <input type="hidden" id="id" name="id" value="<?php echo $uid; ?>">
                    <h4 class="modal-title" id="lblmessage">Message &nbsp;</h4>
                    <textarea type="text" class="form-control" id="message" name="message" value="<?php echo $sms; ?>" style="height:33em" disabled><?php echo $sms; ?></textarea>
                </div>


                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                </div>
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
                        <a type="button" class="close glyphicon glyphicon-remove" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true"></span>
                        </a>
                    </span>
                    <h4 class="modal-title" id="myModalLabel-<?php echo $uid; ?>">SMS Details</h4>
                </div>

                <div class="modal-body" >
                    <form action = "sendfunction.php" method="POST" >
                        <input type="hidden" class="form-control" name="From_Name" id="From_Name" value="<?php echo $from_name; ?>" style="width:120%;height:2em">

                        <label class="control-label" for="Message">Mobile :</label>
                        <select size="1" class="form-control" id="Subject" name="Subject">
                            <option value="0<?php echo $mob_no; ?>"><?php echo $subject; ?></option>
                        </select>
                        <br><br>
                        <label class="control-label" for="Message">Message :</label>
                        <textarea type="text" class="form-control" id="Message" name="Message" value="" style="height:30em" required></textarea>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                            <input type="submit" value="Send" name="submit" class="colrite btn btn-success pull-right">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- End Reply Modal -->

<?php } ?>

    </tbody>
</table>
