<?php

    session_start();
    include('../cfg/connect.php');

    define("BR", "</br>");

    $category = $_POST['category'];
    $period = $_POST['period'];
    $start = $period . 'Start';
    $end = $period . 'End';
    $periodStart = $_SESSION[$start];
    $periodEnd = $_SESSION[$end];

    $data = [];
    $data['html'] = "<div id='catDetail' class='popup'>";

    $Start = $periodStart->format('Y-m-d');
    $End = $periodEnd->format('Y-m-d');

    $selectSQL = "SELECT a.iName, a.iPrice, a.iQty, b.transDate, a.lineID FROM budget.lineItems AS a 
                  LEFT JOIN budget.quickEntry AS b ON a.transID = b.transID 
                  WHERE b.transDate BETWEEN ? AND ? AND iCategory = ?
                  ORDER BY b.transDate";

    $select = $conn->prepare($selectSQL);
    $select->bind_param("sss", $Start, $End, $category);
    $select->execute();
    $select->store_result();
    $select->bind_result($iName, $iPrice, $iQty, $transDate, $lineID);

    while($select->fetch()) {

        $transDate = new DateTime($transDate);
        $transDate = $transDate->format("m-d");

        $data['html'] .= "<div class='lineItems' id='" . $lineID .
                         "' style='font-size:small; height:20px;'><div class='quarterWidth' style='white-space:nowrap; float:left;'>" .
                         $transDate .
                         "</div><div class='quarterWidth' style='white-space:nowrap; float:left; overflow:hidden; text-align:left;'>" .
                         $iName .
                         "</div><div class='quarterWidth' style='white-space:nowrap; float:left; text-align:center;'>" .
                         $iPrice . " x " . $iQty .
                         "</div><div class='quarterWidth' style='white-space:nowrap; float:left; text-align:right;'>" .
                         $iPrice * $iQty . "</div></div>";
    }

    $data['html'] .= "</div>";

    echo json_encode($data);

