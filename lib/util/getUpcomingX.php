<?php
    /**
     * Created by PhpStorm.
     * User: root
     * Date: 1/31/17
     * Time: 9:03 PM
     */

    session_start();

    function showUpcomingX($id, $billDate, $recurID) {
        $html = "<div id='" . $id .
                "' class='newRecurXContainer' style='margin: 2px 0; padding:5px; border-radius: 7px; text-align:center; border:1px solid black;'><div class='addFormContainer'><form id='form-" .
                $id . "' name='addUpcomingXForm' method='post' action=''>
    <div style='width:100%; height:50px'>
        <div style='width:33%; height:50px; float:left;'>
            <div style='text-align:center; height:20px;'><label for='billDate-" . $id . "'>Bill Date</label></div>
            <div style='text-align:center; height:30px; '><input id='billDate-" . $id .
                "' class='dateInput' type='text' name='xBillDate' value='" .
                $billDate . "'/></div>
        </div>
        <div style='width:33%; height:50px; float:left;'>
            <div style='text-align:center; height:20px;'><label for='dueDate-" . $id . "'>Due Date</label></div>
            <div style='text-align:center; height:30px;'><input id='dueDate-" . $id .
                "' class='dateInput' type='text' name='xDueDate' value='" .
                $billDate . "'/></div>
        </div>
        <div style='width:33%; height:50px; float:left;'>
            <div style='text-align:center; height:20px;;'><label for='amount-" . $id . "'>Amount</label></div>
            <div style='text-align:center; height:30px;'><input id='amount-" . $id . "' type='text' name='xAmount' placeholder='Enter Amount'/></div>
        </div>
    </div>
</form></div>
<div style='height:40px; background-color:white; border: 1px solid black; text-align:center; padding-top:10px; border-radius:7px;'>
<div data-switch='addToPay' data-xid='" . $id . "' data-recurid='" . $recurID . "' class='upcomingXCtrl add' style='float:left; width:calc(50% - 2px); border-right: 1px solid black;'>Add To Pay</div>
<div data-switch='markPaid' data-xid='" . $id . "' data-recurid='" . $recurID . "' class='upcomingXCtrl mark' style='float:left; width:50%;'>Mark Paid</div>

</div></div>";

        return $html;
    }

    include('../cfg/connect.php');

    define("BR", "</br>");

    $endDate = new DateTime();
    $endDate->modify("+14 days");
    $end = $endDate->format("m/d/Y");

    //    echo $end . BR;

    $getTrans =
        $conn->prepare("SELECT a.dueOn, a.source, a.type, b.catName, a.startDate, a.frequency, a.name, a.id, a.lastAdd
FROM budget.recurringSources AS a 
LEFT JOIN budget.iCategories AS b ON a.category = b.catID
WHERE a.active = TRUE");
    $getTrans->execute();
    $getTrans->store_result();
    $getTrans->bind_result($dueOn, $source, $type, $category, $startDate, $frequency, $name, $recurID, $lastAdd);

    while($getTrans->fetch()) {

        $transArray = [];

        $frequency == 'monthly' ? $interval = new DateInterval('P1M') : $interval = new DateInterval('P1W');

        $lastAdd == 'none' ? $startDate = new DateTime($startDate) : $startDate = new DateTime($lastAdd);

        if($frequency == 'monthly') {
            $startDate->add($interval);
        }

        if($frequency == 'monthly') {
            $month = $startDate->format('m');
            $year = $startDate->format('y');
            $day = $startDate->format('d');

            if($day != $dueOn) {
                $searchDate = $month . "/" . $dueOn . "/" . $year;
                $dueDate = new DateTime(date('m/d/Y', strtotime($searchDate)));
            } else {
                $dueDate = $startDate;
            }

            $dateDue = $dueDate->format('m/d/Y');

        } else {
            $dueDate = new DateTime(date('m/d/Y', strtotime($startDate->format('m/d/Y') . " next " . $dueOn)));
            $dateDue = $dueDate->format('m/d/Y');
        }

        $searchPeriod = new DatePeriod($dueDate, $interval, $endDate);


        foreach($searchPeriod as $searchDate) {
            $id = $searchDate->format('Ymd') . $recurID;
            $billDate = $searchDate->format('m/d/Y');
            $searchDate->modify('+5 Days');
            $dueDate = $searchDate->format('m/d/Y');

            $transArray[] = showUpcomingX($id, $billDate, $recurID);
        }

        if(count($transArray) != 0) {
            echo "<h2>" . $source .
                 ": </h2>";
            foreach($transArray as $html) {
                echo $html . BR;
            }
        }

    }

?>

<!--<form id='' name='addUpcomingXForm' method='post' action=''>--><!--    <div style='width:100%; height:30px'>--><!--        <div style='width:20%; height:30px; float:left;'>--><!--            <div style='text-align:center'><label for=''></label></div>--><!--            <div style='text-align:center'><input id='' type='text' name='' value=''/></div>--><!--        </div>--><!--        <div style='width:20%; height:30px; float:left;'>--><!--            <div style='text-align:center'><label for=''></label></div>--><!--            <div style='text-align:center'><input id='' type='text' name='' value=''/></div>--><!--        </div>--><!--        <div style='width:20%; height:30px; float:left;'>--><!--            <div style='text-align:center'><label for=''></label></div>--><!--            <div style='text-align:center'><input id='' type='text' name='' value=''/></div>--><!--        </div>--><!--    </div>--><!--</form>-->
