<?php

    session_start();

    include('../cfg/connect.php');

    $returnData = $entriesArray = [];
    $html = $receipt = '';

    function lineItems($lineItem) {

        $lineItem = json_decode(json_encode($lineItem));

//        if(isset($_SESSION['itemInfo']['location']) && !empty($_SESSION['itemInfo']['location'])) {
//            $location = $_SESSION['itemInfo']['location'];
//        } else {
//            $location ='';
//        }
        $amount = $lineItem->amount;
        $transID = $lineItem->transID;
        $tender = $lineItem->tender;
        $html = <<<EOF
        <div id='lineItemDiv$transID' class='fullwidth hiddenLineItemDiv lineItemDiv'>
    <div id='lineItem$transID' class='lineItem center relative' style=''>
        <div class='quarterWidth pad5' style='font-size:x-small;'> $transID </div>
        <div class='quarterWidth pad5'> $tender </div>
        <div class='quarterWidth pad5'> $amount </div>
        <div class='quarterWidth fullHeight pad5'>
            <div class='processEntry' data-id='$transID' style=''>File</div>
        </div>
    </div>
    
</div>
EOF;

        return $html;

    }

    $unprocessed = $conn->prepare("select transID, tender, amount from budget.quickEntry where processed = 'n' order by transDate");
    $unprocessed->execute();
    $unprocessed->store_result();
    $unprocessed->bind_result($transID, $tender, $amount);

    while($unprocessed->fetch()) {

        $lineItem = ['amount' => $amount, 'tender' => $tender, 'transID' => $transID];
        $html .= lineItems($lineItem);
        $returnData['lineItem'][] = $lineItem;
        $returnData['transID'] = $transID;
    }

    $returnData['html'] = trim(preg_replace('/[\s\t\n\r\s]+/', ' ', $html));
    $returnData['testing'] = "this is just a test";
    $returnData['receipt'] = $receipt;

    echo json_encode($returnData);