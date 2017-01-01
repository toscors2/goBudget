<?php
    session_start();

    define("BR", "</br>");
    define("FORMAT", "Y-m-d");
    define("DISPLAY", "m/d/Y");

    include('../cfg/connect.php');

    /**
     * @param $conn mysqli
     * @return array
     */
    function getTrans($conn, $periods) {
        $getTrans = $conn->prepare("
SELECT a.amount, a.transDate, a.type, b.iQty, b.iPrice, b.iCategory, d.familyName FROM budget.quickEntry AS a 
LEFT JOIN budget.lineItems AS b ON a.transID = b.transID
LEFT JOIN budget.categories AS c ON b.iCategory = c.catName
LEFT JOIN budget.family AS d ON c.catFamily = d.familyID
WHERE a.processed = 'y' && c.report = 'y'
ORDER BY a.transDate, c.catName");
        $getTrans->execute();
        $getTrans->store_result();
        $getTrans->bind_result($amount, $transDate, $type, $qty, $price, $category, $family);

        while($getTrans->fetch()) {

            $date = new DateTime($transDate);

            if($type == 'tips') {
                $type = 'inc';
            }

            $iAmount = $qty * $price;

            foreach($periods as $period) {

                $start = $period . 'Start';
                $end = $period . 'End';

                if($date >= $_SESSION[$start] && $date <= $_SESSION[$end]) {
                    !isset($_SESSION['report'][$period][$family][$type][$category])
                        ? $_SESSION['report'][$period][$family][$type][$category] = $iAmount
                        : $_SESSION['report'][$period][$family][$type][$category] += $iAmount;

                    !isset($_SESSION['report'][$period][$type]['total'])
                        ? $_SESSION['report'][$period][$type]['total'] = $iAmount
                        : $_SESSION['report'][$period][$type]['total'] += $iAmount;

                    !isset($_SESSION['report'][$period][$family][$type]['total'])
                        ? $_SESSION['report'][$period][$family][$type]['total'] = $iAmount
                        : $_SESSION['report'][$period][$family][$type]['total'] += $iAmount;
                }
            }

        }
    }

    $_SESSION['report'] = [];
    $data['html'] = $data['hiddenHTML'] = '';

    $periods = ['wtd', 'mtd', 'qtd', 'ytd'];
    $times = ['curr', 'previous', 'ly'];
    $types = ['exp', 'inc', 'tips', 'transfer'];
    $families = ['PERSONAL', 'HOUSEHOLD', 'MISC', 'BUSINESS'];
    $catArray = [];

    $categories = $conn->prepare("SELECT catName FROM budget.categories ORDER BY catName");
    $categories->execute();
    $categories->store_result();
    $categories->bind_result($catName);

    while($categories->fetch()) {
        $catArray[] = $catName;
    }

    getTrans($conn, $periods);

    foreach($periods as $period) {

        $start = $period . 'Start';
        $end = $period . 'End';

        isset($_SESSION['report'][$period]['inc']['total'])
            ? $incTotal = $_SESSION['report'][$period]['inc']['total'] * -1
            : $incTotal = 0;
        isset($_SESSION['report'][$period]['exp']['total'])
            ? $expTotal = $_SESSION['report'][$period]['exp']['total']
            : $expTotal = 0;
        isset($_SESSION['report'][$period]['HOUSEHOLD']['inc']['total'])
            ? $houseInc = $_SESSION['report'][$period]['HOUSEHOLD']['inc']['total']
            : $houseInc = 0;

        $cashFlow = number_format(($incTotal - $expTotal), 2, '.', ',');

        $expTotal = $expTotal + $houseInc;  //$houseInc is a negative number as is all income from database
        $incTotal = $incTotal + $houseInc;  //$houseInc is a negative number as is all income from database

        $data['html'] .= "<div id='" . $period . "'>
                  <div class='periodLine'>
                  <div class='period'>
             <h1  class='periodHeader'>" .
                         strtoupper($period) .
                         "</h1></div>
                         <div data-period='" . $period . "' class='timePeriod'><p class='periodText'>(" .
                         $_SESSION[$start]->format(DISPLAY) . " - " .
                         $_SESSION[$end]->format(DISPLAY) . ")</p></div></div>
             <div class='periodSummaryDiv'>
             <div data-type='exp' data-period=" . $period .
                         " class='periodSummary thirdWidth'><div>Expense:</div><div>" . $expTotal . "</div></div>
             <div data-type='inc' data-period=" . $period .
                         " class='periodSummary thirdWidth'><div>Income:</div><div>" . $incTotal . "</div></div>
             <div data-type='cfl' data-period=" . $period .
                         " class='periodSummary thirdWidth'><div>Cash Flow:</div><div>" . $cashFlow . "</div></div>
             </div>";
        if(!isset($_SESSION['report'][$period])) {
            $data['html'] .= "Nothing Entered For This Period";
        } else {

//            $_SESSION['report'][$period]['inc']['total'] += $_SESSION['report'][$period]['tips'];
            $data['html'] .= "<div class='famDiv'>";
            foreach($families as $famName) {
                $divID = $period . $famName;
                switch($famName) {
                    case 'PERSONAL':  //simply total personal expenses for period
                        isset($_SESSION['report'][$period]['PERSONAL']['exp']) ?
                            $famExp = $_SESSION['report'][$period]['PERSONAL']['exp']['total'] : $famExp = 0;
                        break;
                    case 'HOUSEHOLD': //subtracts household income for period from expenses to account for roommates
                        isset($_SESSION['report'][$period]['HOUSEHOLD']['exp']) ?
                            $famExp = $_SESSION['report'][$period]['HOUSEHOLD']['exp']['total'] : $famExp = 0;
                        isset($_SESSION['report'][$period]['HOUSEHOLD']['inc']) ?
                            $famInc = $_SESSION['report'][$period]['HOUSEHOLD']['inc']['total'] * -1 : $famInc = 0;
                        $famExp = $famExp - $famInc;
                        break;
                    case 'MISC': //used to keep track of sales tax
                        isset($_SESSION['report'][$period]['MISC']['exp']) ?
                            $famExp = $_SESSION['report'][$period]['MISC']['exp']['total'] : $famExp = 0;
                        break;
                    case 'BUSINESS':
                        isset($_SESSION['report'][$period]['BUSINESS']['exp']) ?
                            $famExp = $_SESSION['report'][$period]['BUSINESS']['exp']['total'] : $famExp = 0;
                        break;
                    default:
                        $famExp = $famInc = null;
                }

                $data['html'] .= "<div data-family='" . $famName . "' data-period='" . $period .
                                 "'  class='catPopBtn'><h3>" .
                                 $famName . "</h3>

                <p>Exp: " . $famExp . "</p>
                <p>% Total: " . number_format(($famExp / $expTotal) * 100, 2, '.', ',') . "</p>
                </div>";

                $data['hiddenHTML'] .= "<div id='" . $divID . "'  class='popup catPop'>
                <div id='catHeader'><h3>" . strtoupper($period) . " " . $famName .
                                       " EXPENSES</h3></div><div class='catDiv'>";
                foreach($catArray as $catName) {
                    isset($_SESSION['report'][$period][$famName]['exp'][$catName]) ?
                        $catExp = $_SESSION['report'][$period][$famName]['exp'][$catName] : $catExp = 0;
                    isset($_SESSION['report'][$period][$famName]['inc'][$catName]) ?
                        $catInc = $_SESSION['report'][$period][$famName]['inc'][$catName] : $catInc = 0;
                    $catTotal = number_format(($catExp + $catInc), 2, '.', ',');

                    if(isset($_SESSION['report'][$period][$famName]['exp'][$catName])) {
                        $data['hiddenHTML'] .= "<div data-period='" . $period . "' data-category='" . $catName .
                                               "'  class='catLine'><div class='catLabel'>" . $catName .
                                               "</div><div class='catTotal'> " .
                                               $catTotal .
                                               "</div></div>";
                    }
                }

                $data['hiddenHTML'] .= "</div></div>";
            }
            $data['html'] .= "</div></div>";

        }

    }

    $data['session'] = $_SESSION;

    echo json_encode($data);