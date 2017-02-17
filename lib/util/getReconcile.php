<?php
    /**
     * Created by PhpStorm.
     * User: root
     * Date: 2/7/17
     * Time: 9:32 PM
     */

    include('../cfg/connect.php');

    define("BR", "</br>");

    function reconNotFound() {

    }

    $transCount = 0;

    $reconArray = $transArray = $notFoundArray = $foundArray = [];
    $found = $notFound = [];

    /**
     * @param $conn mysqli
     * @return array
     */
    function getReconInfo($conn) {

        $reconInfo = [];

        $getReconcile =
            $conn->prepare("SELECT reconID, reconAmount, reconDesc, reconDate FROM budget.recon WHERE reconStatus = FALSE ORDER BY reconDate");
        $getReconcile->execute();
        $getReconcile->store_result();
        $getReconcile->bind_result($reconID, $reconAmount, $reconDesc, $reconDate);

        while($getReconcile->fetch()) {
            $reconInfo[] =
                ['id' => $reconID, 'amount' => $reconAmount, 'desc' => $reconDesc, 'date' => $reconDate];
        }

        return $reconInfo;
    }

    function getSearchDates($startDate) {
        $bankDate = new DateTime($startDate);
        $graceDate = new DateTime($startDate);
        $graceDate->modify("-5 days");
        $sqlBankDate = $bankDate->format('Y-m-d');
        $sqlGraceDate = $graceDate->format('Y-m-d');

        $dates = ['start' => $sqlGraceDate, 'end' => $sqlBankDate];

        return $dates;

    }

    /**
     * @param $conn mysqli
     * @param $searchDates object
     * @param $recons object
     */
    function getTransFillArrays($conn, $searchDates, $recons) {

        $foundArray = $notFoundArray = [];

        $negReconAmount = $recons->amount * -1;

        $getTrans = $conn->prepare("SELECT a.transDate, a.amount, b.iSource, a.entryID FROM budget.quickEntry AS a 
LEFT JOIN budget.lineItems AS b ON a.transID = b.transID
WHERE a.reconciled = FALSE && a.transDate BETWEEN ? AND ? && (a.amount = ? || a.amount = ?) && a.processed != 'd' && a.tender = '1533'
GROUP BY b.isource");
        $getTrans->bind_param("ssss", $searchDates->start, $searchDates->end, $recons->amount, $negReconAmount);
        $getTrans->execute();
        $getTrans->store_result();
        $getTrans->bind_result($transDate, $transAmount, $transSource, $transID);

        if($getTrans->num_rows == 0) {
            $notFoundArray = ['recon' =>
                                  ['date' => $recons->date, 'amount' => $recons->amount, 'desc' => $recons->desc,
                                   'id'   => $recons->id]];
        }

        while($getTrans->fetch()) {
//            $transCount++;

//            echo "found: " . $transDate . BR;

            $foundArray = ['trans' => ['date' => $transDate, 'amount' => $transAmount,
                                       'desc' => $transSource,
                                       'id'   => $transID],
                           'recon' => ['date' => $recons->date, 'amount' => $recons->amount,
                                       'desc' => $recons->desc, 'id' => $recons->id]];

        }

        $return = ['foundArray' => $foundArray, 'notFoundArray' => $notFoundArray];

        return $return;
    }

    function displayFound($divCount, $info) {
        $html = "<div><div id='" . $divCount .
                "' class='mainReconDiv' data-found='true'>";
        $html .= "<div>
        <div class='reconHeader'>Reconcile Info</div>
        <div class='reconHeader'>Transaction Info</div>
        </div>";
        foreach($info as $key => $content) {

//            echo "key: " . $key . BR;

            $html .= "<div data-button='" . $key . "' data-trans='found' data-id='" . $content['id'] .
                     "' class='matchedData reconFound'><div>" .
                     $content['date'] . "</div><div>" . $content['amount'] .
                     "</div><div class='reconDesc'>" . $content['desc'] . "</div></div>";
        }
        $html .= "<div class='reconBtnCont' style=''>
        <div data-action='recon' class='reconThirdWidth reconBtn'>Recon</div>
        <div data-action='delete' class='reconThirdWidth reconBtn'>Delete</div>
        <div data-action='dismiss' class='reconThirdWidth reconBtn'>Dismiss</div>
        </div>";
        $html .= "</div>";

        return $html;
    }

    function displayNotFound($divCount, $info) {
        $html = "<div><div id='" . $divCount .
                "' class='mainReconDiv' data-found='false'>";
        $html .= "<div>
        </div>";
        foreach($info as $key => $content) {
            $html .= "<div data-button='" . $key . "' data-trans='notFound' data-id='" . $content['id'] .
                     "' class='matchedData reconNotFound'><div>" .
                     $content['date'] . "</div><div>" . $content['amount'] .
                     "</div><div class='reconDesc'>" . $content['desc'] . "</div></div>";
        }
        $html .= "<div class='reconBtnCont'>
        <div data-action='recon' class='reconFourthWidth reconBtn'>Save</div>
        <div data-action='delete' class='reconFourthWidth reconBtn'>Delete</div>
        <div data-action='dismiss' class='reconFourthWidth reconBtn'>Dismiss</div>
        <div data-action='add' class='reconFourthWidth reconBtn'>Add</div>
        </div>";
        $html .= "</div>";

        return $html;

    }

    $reconInfo = json_decode(json_encode(getReconInfo($conn)));

    $reconcileCount = count($reconInfo);

    foreach($reconInfo as $recons) {

        $negReconAmount = $recons->amount * -1;

        $searchDates = json_decode(json_encode(getSearchDates($recons->date)));

        $arrays = getTransFillArrays($conn, $searchDates, $recons);

        if(!empty($arrays['foundArray'])) {
            $foundArray[] = $arrays['foundArray'];
        }
        if(!empty($arrays['notFoundArray'])) {
            $notFoundArray[] = $arrays['notFoundArray'];
        }

    }

    $divCount = 0;

    foreach($foundArray as $label => $info) {
        $found[] = displayFound($divCount, $info);
        $divCount++;
    }

    foreach($notFoundArray as $label => $info) {

        $notFound[] = displayNotFound($divCount, $info);
        $divCount++;
    }

    $summary =
        "reconcile Count: " . $reconcileCount . BR . "trans Count: " . $transCount . BR . "found transactions: " .
        count($found) . BR . "not found transactions: " . count($notFound) . BR . BR;

    $data = ['found' => $found, 'notFound' => $notFound, 'summary' => $summary];

    echo json_encode($data);