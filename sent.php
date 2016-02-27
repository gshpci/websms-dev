<?php
require 'header.php';
 ?>

<style>
  .dropdown {
    position: relative;
    width: 350px;
  }
  .dropdown select{
    width: 100%;
  }
  .dropdown > * {
    box-sizing: border-box;
    height: 1.5em;
  }
  .dropdown option{
  color: green;
  }
  .dropdown input {
  /*width: calc(100% - 20px);*/
  padding: 0 2.5em 0 2.5em;
  width: 100%;
  color: black;
  }
</style>

  <div class="container-fluid" id="cc">
    <fieldset>
        <h5 style="color:white;"><blockquote>
            <span class="glyphicon glyphicon-envelope">&nbsp;<strong>Sent Items</strong>
        </blockquote>
    </fieldset>
                <div class="col-sm-12 text-right" style ="float:right">
                  <ul class="pagination pagination-md pager" id="myPager"></ul>
                </div>

<?php

$u = CONF_DB_USER;
$p = CONF_DB_PASS;
$h = CONF_WEBHOST;
$dbase = CONF_DB_NAME;

$dbhandle = mysql_connect($h, $u, $p) or die("Unable to connect to MySQL");
$selected = mysql_select_db($dbase,$dbhandle) or die("Could not select examples");

if ($from_name == 'Admin') {
    $sql = "SELECT message, id, datetime, mobile_no, station, remarks, port FROM sms s order by datetime desc limit 500";
} else {
    //$sql = "SELECT message, id, datetime, mobile_no, station, remarks FROM sms s where station = '{$from_name}' order by datetime desc limit 15" ;
    $sql = "SELECT message, id, datetime, mobile_no, station, remarks, port FROM sms s where station = '{$from_name}' order by datetime desc limit 500" ;
}

$result = mysql_query($sql);
?>

  <div class="row">

  <table class="rwd-table" style="width: 100%;" > 
      <thead>
        <tr class="listtr">
          <th class="listtd" style="text-align: center; color: white;" width="5%">Station</th>
          <th class="listtd" style="text-align: center; color: white;" width="12%">Datetime</th>
          <th class="listtd" style="text-align: center; color: white;" width="24%">Recipient</th>
          <?php
            if ($from_name == 'Admin') {
                echo "<th class='listtd' style='text-align: center; color: white;' width='5%'>Port</th>";
            }
          ?>
          <th class="listtd" style="text-align: center; color: white;" width="36%">Message</th>
          <th class="listtd" style="text-align: center; color: white;" width="25%">Status</th>
        </tr>
      </thead>
  </table>

  <div class="table-responsive"  style="height: 480px;">

    <table class="rwd-table" style="width: 100%;">
      <tbody id="myTable">
        <tr  class="listtr">
<?php
    while ($row = mysql_fetch_array($result)) {
                  echo "
                  <td class='listtd' style='width: 5%; color: white;'>".$row{'station'}."</td>
                  <td class='listtd' style='width: 12%; color: white;'>".$row{'datetime'}."</td>";

                  $mobile = $row{'mobile_no'};
                  $q = mysql_query("select concat(Lastname,', ', Firstname) as name from phonebook where Number = $mobile");

                  if ($r = mysql_fetch_array($q)) {
                    $to = $r['name']." <".$mobile.">";
                  } else {
                    $to = "UNKNOWN <".$mobile.">";
                  }

                  echo "<td class='listtd' style='width: 25%; color: white'>";

                  echo $to;
                      if (substr($to, 0,7) == "UNKNOWN") {

                  ?>
                      <button style="text-decoration:none;cursor:pointer; font-size: 10px;" class = "btn btn-success" data-toggle="modal" data-target="#AddData1-<?php echo $row['id']; ?>" data-backdrop="static" data-keyboard="false"><span class="glyphicon glyphicon-pencil" aria-hidden="true" style = "color:#ffffff;"></span><font size="1em">Add to Phonebook</font></button >
                      
                  <?php } 

                  echo "</td>";
                  
                  if ($from_name == 'Admin') {
                    echo "<td class='listtd' style='width: 5%; color: white'>".$row{'port'}."</td>";
                    }
                  ?>

                  <td class="listtd" style='width: 36%; color: white'>
                      <div style="width: 100%; float:right;" >
                      <?php
                      $smsid = $row{'id'};
                      $sms = $row{'message'};
                      if (strlen($sms) > 50) {
                        echo substr($sms, 0, 50)."<span style='font-size:16 px; color:white;'> ... (trunc). </span>";
                      ?>
                        <button style="text-decoration:none;cursor:pointer; font-size: 10px;" class = "btn btn-info" data-toggle="modal" data-target="#ViewSMS-<?php echo $row['id']; ?>" data-backdrop="static" data-keyboard="false">
                          <span class="glyphicon glyphicon-eye-open" aria-hidden="true" style = "color:#fff;"></span>
                          View
                        </button >

                      <?php
                      } else {
                        echo substr($sms, 0, 50);
                      }
                      ?>
                      </div>
                  </td>
                  <td  class="listtd" style='color: white'><?php echo $row{'remarks'} ?> &nbsp;&nbsp;
                    <button style="text-decoration:none;cursor:pointer; font-size: 10px;" class = "btn btn-info" data-toggle="modal" data-target="#ForwardSMS-<?php echo $row['id']; ?>" data-backdrop="static" data-keyboard="false">
                        <span class="glyphicon glyphicon-envelope" aria-hidden="true" style = "color:#fff;"></span>
                        <span class="glyphicon glyphicon-arrow-right" aria-hidden="true" style = "color:#fff;"></span>
                        Forward
                    </button >
                  </td>
              </tr>

              <!--Modal for Add Contact-->
                      <div class="modal fade" id="AddData1-<?php echo $row['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabe">
                          <div class="modal-dialog" role="document">
                              <div class="modal-content">
                                  <div class="modal-header">
                                      <h4 class="modal-title" id="myModalLabe">Add New Contact</h4>
                                  </div>
                                  <div class="modal-body">
                                      <div class="container-fluid">
                                          <br>
                                          <br>
                                          <br>
                                          <center>
                                              <form action="add.php" method="post">
                                              <input type="hidden" id="Modal" name="Modal" value="myAddRecord">
                                              <input type="hidden" id="action" name="action" value="Add Contact">
                                              <input type="hidden" id="station" name="station" value="<?php echo $from_name; ?>">
                                                  <div class="form-group has-info">
                                                      <label class="control-label" for="From_Name">Lastname *</label>
                                                          <input type="text" class="form-control" name="lName" style="width:50%;height:2em" required>
                                                  </div>
                                                  <div class="form-group has-info">
                                                      <label class="control-label" for="From_Name">Firstname *</label>
                                                          <input type="text" class="form-control" name="fName" style="width:50%;height:2em" required>
                                                  </div>
                                                  <div class="form-group has-info">
                                                      <label class="control-label" for="From_Name">Middlename</label>
                                                          <input type="text" class="form-control" name="mName" style="width:50%;height:2em">
                                                  </div>

                                                  <div class="form-group has-info">
                                                      <label class="control-label" for="From_Name">Number (11-Digit Format: 09XXXXXXXXX) *</label>
                                                      <br>
                                                      <h4 class="control-label" for="From_Name" style="border-style: solid; border-radius: 6px; border-width: 1px; padding: 5px 0px 5px 0px; border-color: #CCCCCC; width: 50%;"><?php echo $mobile; ?></h4>
                                                          <input pattern="^09[0-9]{9}" value ="<?php echo $mobile; ?>" title="11 digits and numbers only. Must start with 09"  maxlength="11" onkeypress="return /\d/.test(String.fromCharCode(((event||window.event).which||(event||window.event).which)));" type="hidden" class="form-control" name="Number" style="width:50%;height:2em" >
                                                  </div>
                                                  * - Required field.
                                          </center>
                                          <br>
                                          <br>
                                          <br>
                                      </div>
                                  </div>

                                  <div class="modal-footer">
                                      <button type="submit" class="btn btn-success">Save</button>
                                      <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                                  </div>
                              </div>
                          </div>
                      </div>
              <!--End Modal for Add Contact-->

              
        <!-- View Modal -->
        <div class="modal fade" id="ViewSMS-<?php echo $row['id']; ?>" tabindex="-<?php echo $row['id']; ?>" role="dialog" aria-labelledby="myModalLabel-<?php echo $row['id']; ?>" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                  <span id="IL_AD4" class="IL_AD">
                    <a type="button" class="close glyphicon glyphicon-remove" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true"></span>
                    </a>
                  </span>
                  <h4 class="modal-title" id="myModalLabel-<?php echo $row['id']; ?>">SMS Details</h4>
                </div>

                <div class="modal-body" >
                  <form action="delete.php" method="post">
                    <input type="hidden" id="id" name="id" value="<?php echo $row['id']; ?>">
                    <h4 class="modal-title" id="lblmessage">Message &nbsp;</h4>
                    <textarea type="text" class="form-control" id="message" name="message" value="<?php echo $row['message']; ?>" style="height:33em" disabled><?php echo $row['message']; ?></textarea>
                  </form>
                </div>

                <div class="modal-footer">
                  <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                </div>
            </div>
          </div>
        </div>
        <!-- End View Modal -->

        <!-- Forward Modal -->
        <div class="modal fade" id="ForwardSMS-<?php echo $row['id']; ?>" tabindex="-<?php echo $row['id']; ?>" role="dialog" aria-labelledby="myModalLabel-<?php echo $row['id']; ?>" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header">
                      <span id="IL_AD4" class="IL_AD">
                        <a type="button" class="close glyphicon glyphicon-remove" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true"></span>
                        </a>
                      </span>
                      <h4 class="modal-title" id="myModalLabel-<?php echo $row['id']; ?>">
                        <span class="glyphicon glyphicon-envelope" aria-hidden="true" style = "color:#fff;"></span>
                        <span class="glyphicon glyphicon-arrow-right" aria-hidden="true" style = "color:#fff;"></span>
                        Forward SMS
                      </h4>
                    </div>

                    <div class="modal-body" >
                      <form action = "sendfunction.php" method="POST" >

                            <input type="hidden" class="form-control" name="From_Name" id="From_Name" value="<?php echo $from_name; ?>" style="width:120%;height:2em">
                            <input type="hidden" class="form-control" id="From_Email" name="From_Email" value="<?php echo $from_email; ?>" style="width:120%;height:2em">
                            <input type="hidden" class="form-control" id="To_Name" name="To_Name" value="<?php echo $to_name; ?>" style="width:120%;height:2em">
                            <input type="hidden" class="form-control" id="To_Email" name="To_Email" value="<?php echo $to_email; ?>" style="width:120%;height:2em">
                            <input type="hidden" class="form-control" id="cc_Email" name="cc_Email" value="<?php echo $cc_email; ?>" style="width:120%;height:2em">
                            <input type="hidden" class="form-control" id="bcc_Email" name="bcc_Email" value="<?php echo $bcc_email; ?>" style="width:120%;height:2em">

                            <?php
                            if ($from_name == 'Admin') {
                            ?>
                              <label class="control-label" for="port">Port :</label>
                              <div class="dropdown">
                                  <select size="1" name="port" id="port" class="form-control" style="width: 45%;">
                                      <option value='auto'>Auto</option>
                                      <option value='port:11'>GLOBE</option>
                                      <option value='port:13'>SUN</option>
                                      <option value='port:15'>SMART</option>
                                   </select>
                              </div>
                              <br>
                            <?php
                            }
                            ?>

                            <label class="control-label" for="Subject">Mobile :</label>
                            <div class="dropdown">
                              <input pattern="^09[0-9]{9}" title="11 digits and numbers only. Must start with 09" style="font-size: 14px;" maxlength="11" onkeypress="return /\d/.test(String.fromCharCode(((event||window.event).which||(event||window.event).which)));" type="text" id="Subject" name="Subject" placeholder='ENTER 09xxxxxxxxx or SELECT below' required />
                              <select onchange="this.previousElementSibling.value=this.value; this.previousElementSibling.focus()" size="1" class="form-control">
                                  <option value='09xxxxxxxxx'>SELECT HERE</option>

                                      <?php
                                      $u = CONF_DB_USER;
                                      $p = CONF_DB_PASS;
                                      $h = CONF_WEBHOST;
                                      $dbase = CONF_DB_NAME;
                                      $connect_db = mysql_connect($h, $u, $p) or die("Cannot connect to server");
                                      mysql_select_db($dbase,$connect_db) or die("Cannot connect to the database");

                                      $query = mysql_query("select id, concat(Lastname,', ',Firstname) as name, Number from phonebook order by name asc");

                                                          while ($row = mysql_fetch_array($query)) {
                                                      echo "<option value =".$row['Number'].">".$row['name']." &nbsp; < ".$row['Number']." ></option>";
                                                  }
                                      ?>
                              </select>
                            </div>

                            <br>
                            <label class="control-label" for="Message">Message :</label>
                            <textarea type="text" class="form-control" id="Message1-<?php echo $smsid; ?>" name="Message" maxlength="480" value="<?php echo $sms; ?>" style="height:30em" required><?php echo $sms; ?></textarea>
                            <div id="Message_feedback1-<?php echo $smsid; ?>" style="color:green; font-size:16px;"></div>

                            <div class="modal-footer">
                              <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                              <input type="submit" value="Send" name="submit" class="colrite btn btn-success pull-right">
                            </div>
                      </form>
                    </div>
                </div>
            </div>
        </div>




<?php
    }

    mysql_close($dbhandle);
?>
      </tbody>
    </table>

      </div>
  </div>
</div>

        <!-- End Forward Modal -->

<script>
$.fn.pageMe = function(opts){
    var $this = this,
        defaults = {
            perPage: 100,
            showPrevNext: false,
            hidePageNumbers: false
        },
        settings = $.extend(defaults, opts);
    
    var listElement = $this;
    var perPage = settings.perPage; 
    var children = listElement.children();
    var pager = $('.pager');
    
    if (typeof settings.childSelector!="undefined") {
        children = listElement.find(settings.childSelector);
    }
    
    if (typeof settings.pagerSelector!="undefined") {
        pager = $(settings.pagerSelector);
    }
    
    var numItems = children.size();
    var numPages = Math.ceil(numItems/perPage);

    pager.data("curr",0);
    
    if (settings.showPrevNext){
        $('<li><a href="#" class="prev_link">«</a></li>').appendTo(pager);
    }
    
    var curr = 0;
    while(numPages > curr && (settings.hidePageNumbers==false)){
        $('<li><a href="#" class="page_link">'+(curr+1)+'</a></li>').appendTo(pager);
        curr++;
    }
    
    if (settings.showPrevNext){
        $('<li><a href="#" class="next_link">»</a></li>').appendTo(pager);
    }
    
    pager.find('.page_link:first').addClass('active');
    pager.find('.prev_link').hide();
    if (numPages<=1) {
        pager.find('.next_link').hide();
    }
    pager.children().eq(1).addClass("active");
    
    children.hide();
    children.slice(0, perPage).show();
    
    pager.find('li .page_link').click(function(){
        var clickedPage = $(this).html().valueOf()-1;
        goTo(clickedPage,perPage);
        return false;
    });
    pager.find('li .prev_link').click(function(){
        previous();
        return false;
    });
    pager.find('li .next_link').click(function(){
        next();
        return false;
    });
    
    function previous(){
        var goToPage = parseInt(pager.data("curr")) - 1;
        goTo(goToPage);
    }
     
    function next(){
        goToPage = parseInt(pager.data("curr")) + 1;
        goTo(goToPage);
    }
    
    function goTo(page){
        var startAt = page * perPage,
            endOn = startAt + perPage;
        
        children.css('display','none').slice(startAt, endOn).show();
        
        if (page>=1) {
            pager.find('.prev_link').show();
        }
        else {
            pager.find('.prev_link').hide();
        }
        
        if (page<(numPages-1)) {
            pager.find('.next_link').show();
        }
        else {
            pager.find('.next_link').hide();
        }
        
        pager.data("curr",page);
        pager.children().removeClass("active");
        pager.children().eq(page+1).addClass("active");
    
    }
};

$(document).ready(function() {
    var text_max = 480;
    $('#Message_feedback1-'+<?php  echo $smsid; ?>).html(text_max + ' characters remaining');

    $('#Message1-'+<?php  echo $smsid; ?>).keyup(function() {
        var text_length = $('#Message1-'+<?php  echo $smsid; ?>).val().length;
        var text_remaining = text_max - text_length;

        $('#Message_feedback1-'+<?php  echo $smsid; ?>).html(text_remaining + ' characters remaining');
    });
        var text_length1 = $('#Message1-'+<?php  echo $smsid; ?>).val().length;
        var text_remaining1 = text_max - text_length1;

        $('#Message_feedback1-'+<?php  echo $smsid; ?>).html(text_remaining1 + ' characters remaining');


        $('#myTable').pageMe({pagerSelector:'#myPager',showPrevNext:true,hidePageNumbers:false,perPage:100});
});
</script>
</body>
</html>
