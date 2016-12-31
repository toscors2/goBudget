<?php

    session_start();

    include("../cfg/connect.php");

    $data = [];
    $data['errors'] = false;
    $boxItemTotal = 0;
    $lineItem = '';
    $source = null;
    $transID = null;

    if(isset($_POST['transID'])) {
        $transID = $_POST['transID'];
    } else {
        $transID = $_SESSION['transID'];
    }

    $displaySQL =
        "SELECT transID, iSource, iName, iPrice, lineID, iQty 
          FROM budget.lineItems 
          WHERE transID = '" . $transID . "'";

    $display = $conn->prepare($displaySQL);
    $display->execute();
    $display->store_result();
    $lineCount = $display->num_rows;
    $display->bind_result($transID, $iSource, $iName, $iPrice, $lineID, $iQty);

    if($lineCount > 0) {
        while($display->fetch()) {

            if($source == null) {
                $source = $iSource;
            }

            $totalPrice = number_format(($iPrice * $iQty), 2, '.', ',');

            $boxItemTotal += $totalPrice;

            $lineItem .= "<div id=" . $lineID . " class='lineItems' style='width:100%;font-size:small;'>
            <div style='width:33%; float:left;' class='iName'>" . $iName .
                         "</div><div style='width:33%; float:left; text-align:center;'>" .
                         number_format($iPrice, 2, '.', ',') . " x " . number_format($iQty, 0) .
                         "</div><div style='width:33%; float:left; text-align:right;'>" .
                         $totalPrice . "</div></div>";
        }
    }

    strlen($lineItem) > 5 ? $data['lineItems'] = trim($lineItem) : $data['lineItems'] = $lineItem;

    $select = $conn->prepare("SELECT amount FROM budget.quickEntry WHERE transID = '" . $transID . "'");
    $select->execute();
    $select->store_result();
    $select->bind_result($amount);
    $select->fetch();

    $difference = $amount - $boxItemTotal;

    $data['difference'] = $difference;
    $data['boxItemTotal'] = $boxItemTotal;

    $data['source'] = $source;
    $data['transID'] = $transID;
    $data['lineID'] = $lineID;

    echo json_encode($data);

