<?php

    session_start();
    include('../cfg/connect.php');

    /**
     * @param $conn mysqli
     * @return array
     */
    function getPeriodTips($conn, $Start, $End) {

        $tips = [];

        $selectSQL = "SELECT amount, transDate, transID FROM budget.quickEntry 
                  WHERE transDate BETWEEN ? AND  ? AND type = 'tips' AND processed != 'd'
                  ORDER BY transDate";

        $select = $conn->prepare($selectSQL);
        $select->bind_param("ss", $Start, $End);
        $select->execute();
        $select->store_result();
        $select->bind_result($amount, $transDate, $transID);
        while($select->fetch()) {
            $tips[] = "<div class='lineItems' id='" . $transID .
                      "' style='font-size:small; height:20px;'><div class='halfWidth' style='white-space:nowrap; float:left;'>" .
                      $transDate .
                      "</div><div class='halfWidth' style='white-space:nowrap; float:left; overflow:hidden; text-align:left;'>" .
                      $amount . "</div></div>";
        }

        return $tips;
    }

    /**
     * @param $conn mysqli
     * @return array
     */
    function getPeriodInc($conn, $Start, $End) {

        $inc = [];
        $selectSQL = "SELECT b.iCategory, a.amount, a.transDate, b.lineID FROM budget.quickEntry AS a 
                    LEFT JOIN budget.lineItems AS b ON a.transID = b.transID
                  WHERE a.transDate BETWEEN ? AND  ? AND a.type = 'inc' AND a.processed != 'd' AND b.iCategory != 'HOUSE BILL'
                  ORDER BY a.transDate";

        $select = $conn->prepare($selectSQL);
        $select->bind_param("ss", $Start, $End);
        $select->execute();
        $select->store_result();
        $select->bind_result($category, $amount, $transDate, $lineID);

        while($select->fetch()) {
            $inc[] = "<div class='lineItems' id='" . $lineID .
                     "' style='font-size:small; height:20px;'><div class='thirdWidth' style='white-space:nowrap; float:left;'>" .
                     $transDate .
                     "</div><div class='thirdWidth' style='white-space:nowrap; float:left; overflow:hidden; text-align:left;'>" .
                     $category .
                     "</div><div class='thirdWidth' style='white-space:nowrap; float:left; text-align:center;'>" .
                     $amount . "</div></div>";
        }

        return $inc;
    }

    define("BR", "</br>");

    $period = $_POST['period'];
    $type = $_POST['type'];
    $start = $period . 'Start';
    $end = $period . 'End';
    $periodStart = $_SESSION[$start];
    $periodEnd = $_SESSION[$end];
    $data = [];

    $Start = $periodStart->format('Y-m-d');
    $End = $periodEnd->format('Y-m-d');

    switch ($type) {
        case 'tips':
            $lineItems = getPeriodTips($conn, $Start, $End);
            break;
        case 'inc':
            $lineItems = getPeriodInc($conn, $Start, $End);
            break;
        default:
            $lineItems = null;
    }

    if($lineItems != null) {
        echo "<div id='incDetail' class='popup'>";

        foreach ($lineItems as $lines) {
            echo $lines;
        }
        echo "</div>";
    }

    //    echo json_encode($data);

