<?php
 $host="10.11.12.2";
 $uname="root";
 $pass="@gsh1234";
 $database = "gsh_sms";
 $connection=mysql_connect($host,$uname,$pass) 
 or die("Database Connection Failed");
 
 $result=mysql_select_db($database)
 or die("database cannot be selected");
 
?>