<?php
require_once 'header.php';
?>

<div id= 'load' class ='container-fluid'>
    <fieldset>
        <h5 style="color:white;"><blockquote>
            <span class="glyphicon glyphicon-envelope">&nbsp;<strong>Inbox</strong>
        </blockquote></h5>
    </fieldset>

    <script type="text/javascript">// <![CDATA[
        $(document).ready(function() {
            $.ajaxSetup({ cache: false }); // This part addresses an IE bug. without it, IE will only load the first number and will never refresh
            setInterval(function() {
                $('#results').load('inbox-table.php');
            }, 30000); // the "3000" here refers to the time to refresh the div. it is in milliseconds.
        });
        // ]]>
    </script>

    <div id="results">
        <?php require 'inbox-table.php'; ?>
    </div>
</div>
