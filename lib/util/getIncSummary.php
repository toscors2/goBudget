<?php

    session_start();
    include('../cfg/connect.php');

    /**@param $conn mysqli */
    function getPeriodTips($conn, $Start, $End) {
        $selectSQL = "SELECT sum(amount) FROM budget.quickEntry 
                  WHERE transDate BETWEEN ? AND  ? AND type = 'tips' AND processed != 'd'";

        $select = $conn->prepare($selectSQL);
        $select->bind_param("ss", $Start, $End);
        $select->execute();
        $select->store_result();
        $select->bind_result($amount);
        $select->fetch();

        return $amount;
    }

    /**@param $conn mysqli */
    function getPeriodInc($conn, $Start, $End) {
        $selectSQL = "SELECT sum(a.amount) FROM budget.quickEntry AS a 
                    LEFT JOIN budget.lineItems AS b ON a.transID = b.transID
                  WHERE a.transDate BETWEEN ? AND  ? AND a.type = 'inc' AND a.processed != 'd' AND b.iCategory != 'HOUSE BILL'";

        $select = $conn->prepare($selectSQL);
        $select->bind_param("ss", $Start, $End);
        $select->execute();
        $select->store_result();
        $select->bind_result($amount);
        $select->fetch();

        return $amount;
    }

    define("BR", "</br>");

    $period = $_POST['period'];
    $start = $period . 'Start';
    $end = $period . 'End';
    $periodStart = $_SESSION[$start];
    $periodEnd = $_SESSION[$end];
    $data = [];

    $Start = $periodStart->format('Y-m-d');
    $End = $periodEnd->format('Y-m-d');
    
    $tips = getPeriodTips($conn, $Start, $End);
    $inc = getPeriodInc($conn, $Start, $End);

    echo "<div id='incSummary' class='popup'>";

//    echo "<h1>Start: " .$Start ." & End: " .$End ."</h1>";

    echo "<div class='incSummary' data-type='tips' data-period='".$period."' id='tipSummary' style='font-size:small; height:20px;'><div class='halfWidth' style='white-space:nowrap; float:left;'>Tips: " .
         "</div><div class='halfWidth' style='white-space:nowrap; float:left; overflow:hidden; text-align:left;'>" .
         $tips .
         "</div></div>";
    echo "<div class='incSummary' data-type='inc' data-period='".$period."' id='incSummary' style='font-size:small; height:20px;'><div class='halfWidth' style='white-space:nowrap; float:left;'>Other Income: " .
         "</div><div class='halfWidth' style='white-space:nowrap; float:left; overflow:hidden; text-align:left;'>" .
         $inc .
         "</div></div>";
    echo "</div>";

    //    echo json_encode($data);

