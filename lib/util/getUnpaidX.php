<?php
    /**
     * Created by PhpStorm.
     * User: root
     * Date: 2/4/17
     * Time: 9:00 AM
     */

    session_start();

    include('../cfg/connect.php');
    include('../class/Options.php');

    function transHTML($transID, $source, $iName, $category, $type, $billDate, $dueDate, $amount, $family) {
        echo "<div><div id='" . $transID . "' class='upcomingTrans'>
    <div id='upcomingXHeader-" . $transID . "' style='height:55px;'>
        <h1 style='margin:0;'>" . $source . "</h1>
        <div class='thirdWidth' style='font-size:small; float:left; white-space: nowrap; overflow: hidden;'><h3
                style='margin:0;'>" . $iName . "</h3></div>
        <div class='thirdWidth' style='font-size:small; float:left; white-space: nowrap; overflow: hidden;'><h3
                style='margin:0;'>" . $category . "</h3></div>
        <div class='thirdWidth' style='font-size:small; float:left; white-space: nowrap; overflow: hidden;'><h3
                style='margin:0;'>" . $type . "</h3></div>
    </div>
    <div id='billingInfo-" . $transID . "' class='billingInfo' style=''>
        <div class='halfWidth Lfloat'>Billed On: " . $billDate . "</div>
        <div class='halfWidth Lfloat'>Due On: " . $dueDate . "</div>
    </div>
    <div id='paymentInfo-" . $transID . "' class='paymentInfo' style=''>
        <form id='recurPaymentForm-" . $transID . "' style='height:auto;' name='recurPaymentForm' method='post' action=''>
            <div class='quarterWidth '><label for='xPdDate-" . $transID . "'>Pay Date: </label></div>
            <div class='quarterWidth payInput'><input type='text' id='xPdDate-" . $transID .
             "' name='transDate' class='payInfo' value='" . $dueDate . "' />
            </div>
            <div class='quarterWidth'><label for='xAmount-" . $transID . "'>Amount: </label></div>
            <div class='quarterWidth payInput'><input type='text' id='xAmount-" . $transID .
             "' name='amount' class='payAmount' value='" . $amount . "'/></div>
            <div>
            <div class='halfWidth Lfloat'>Select Tender: </div>
            <div class='halfWidth Lfloat'><select class='recurTender' style='height:30px; width:95%;' name='tender'>".getTender()."</select></div>
            </div>
            <input type='hidden' name='iCategory' value='" . $category . "'/>
            <input type='hidden' name='iSource' value='" . $source . "'/>
            <input type='hidden' name='iName' value='" . $iName . "'/>
            <input type='hidden' name='type' value='" . $type . "'/>
            <input type='hidden' name='category' value='" . $family . "'/>
            
        </form>
    </div>
    <div id='paymentCtrl-" . $transID . "' style='height:60px; padding:2px;'>
    <div data-id='" . $transID . "' data-status='payNow' class='recurPayBtn Lfloat center'><h3>Pay Now</h3></div>
    <div data-id='" . $transID . "' data-status='dontPay' class='recurPayBtn Lfloat center'><h3>Do Not Pay</h3></div>
    </div>
</div></div>";
    }

    function getTender() {
        $html = '';
        $options = new Options();

        $tender = $options->getTenderOptions();

        foreach ($tender as $option) {
            $html .= $option;
        }

        return trim($html);
    }

    $getTrans = $conn->prepare("SELECT a.xBillDate, a.xDueDate, a.xAmount, a.id, b.source, c.catName, b.name, b.type, d.familyNick
    FROM budget.upcomingX AS a 
    LEFT JOIN budget.recurringSources AS b ON a.recurID = b.id
    LEFT JOIN budget.iCategories AS c ON b.category = c.catID
    LEFT JOIN budget.family AS d ON c.catFamily = d.familyID
    WHERE a.xPd = FALSE
    ORDER BY b.source DESC , a.xDueDate");
    $getTrans->execute();
    $getTrans->store_result();
    $getTrans->bind_result($billDate, $dueDate, $amount, $transID, $source, $category, $iName, $type, $family);

    while($getTrans->fetch()) {

        transHTML($transID, $source, $iName, $category, $type, $billDate, $dueDate, $amount, $family);

    }


