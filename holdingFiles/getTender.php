<?php
    include('../cfg/connect.php');


    $tenderSQL = $conn->prepare("SELECT tenderName, tenderCode, balance FROM budget.tender");
    $tenderSQL->execute();
    $tenderSQL->store_result();
    $tenderSQL->bind_result($tenderName, $tenderCode, $balance);

    while($tenderSQL->fetch()) {

        if(isset($_POST['request'])) {
            switch($_POST['request']) {
                case 'select':
                    echo "<option class='tender' data-process='null' value='$tenderCode'>" . $tenderName . "-" . $tenderCode .
                         "</option>";
                    break;
                case 'balance':
                    echo "<div class='fullWidth' style='height:30px;'>
                            <div class='quarterWidth'>$tenderCode</div>
                            <div class='quarterWidth'>$tenderName</div>
                            <div class='quarterWidth'><label><input type='tel' id='$tenderCode' value='$balance'/></label></div>
                            <div class='quarterWidth'><button data-code='$tenderCode' class='reconcileBtn'>Update</button></div>
                          </div>";
            }
        }

    }