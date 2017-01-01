<?php
    session_start();

    define("BR", "</br>");
    define("FORMAT", "Y-m-d");
    define("DISPLAY", "m/d/Y");

    include('../cfg/connect.php');

    function setStartEnd() {
        $today = new DateTime(date('Y-m-d 0:0:0'));

        $today->format('l') != 'Saturday' ?
            $_SESSION['wtdStart'] = new DateTime(date('Y-m-d 0:0:0', strtotime('last Saturday')))
            : $_SESSION['wtdStart'] = $today;
        $today->format('l') != 'Friday' ?
            $_SESSION['wtdEnd'] = new DateTime(date('Y-m-d 23:59:59', strtotime('next Friday')))
            : $_SESSION['wtdEnd'] = $today;

        $quarter = ceil($today->format('m') / 3);

        switch($quarter) {
            case 1:
                $qtdStart = (date('Y-m-d 0:0:0', strtotime('January 1')));
                break;
            case 2:
                $qtdStart = date('Y-m-d 0:0:0', strtotime('April 1'));
                break;
            case 3:
                $qtdStart = date('Y-m-d 0:0:0', strtotime('July 1'));
                break;
            case 4:
                $qtdStart = date('Y-m-d 0:0:0', strtotime('October 1'));
                break;
            default:
                $qtdStart = null;
                $qtdEnd = null;
                break;
        }

        $_SESSION['qtdEnd'] = new DateTIme (date('Y-m-t 23:59:59', strtotime($qtdStart . " +2 months")));
        $_SESSION['qtdStart'] = new DateTime($qtdStart);

        $thisMonth = $today->format('m');
        $thisYear = $today->format('Y');

        $_SESSION['mtdStart'] = new DateTime (date('Y-m-01 0:0:0', strtotime('today')));
        $_SESSION['mtdEnd'] = new DateTIme (date('Y-m-t 23:59:59', strtotime('today')));

        $_SESSION['ytdStart'] = new DateTime (date('Y-01-01 0:0:0', strtotime('today')));
        $_SESSION['ytdEnd'] = new DateTime (date('Y-12-31 23:59:59', strtotime('today')));
    }

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

    setStartEnd();

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
                  <div class='periodLine' style=''>
                  <div class='period' style=''>
             <h1 style='' class='periodHeader'>" .
                         strtoupper($period) .
                         "</h1></div>
                         <div class='timePeriod' style=''><p class='periodText'>(" .
                         $_SESSION[$start]->format(DISPLAY) . " - " .
                         $_SESSION[$end]->format(DISPLAY) . ")</p></div></div>
             <div class='periodSummaryDiv' style=''>
             <div class='periodSummaryLabels' style=''>
             <div class='periodSummary' style=''>Expense:</div>
            <div class='periodSummary' style=''>Income:</div>             
            <div class='periodSummary' style=''>Cashflow:</div>
             </div>
             <div class='periodSummaryTotals' style=''>
             <div class='periodSummary' style=''>" . $expTotal . "</div>
            <div class='periodSummary' style=''>" . $incTotal . "</div>             
            <div class='periodSummary' style=''>" . $cashFlow . "</div>
             </div>
             </div>";
        if(!isset($_SESSION['report'][$period])) {
            $data['html'] .= "Nothing Entered For This Period";
        } else {

//            $_SESSION['report'][$period]['inc']['total'] += $_SESSION['report'][$period]['tips'];
            $data['html'] .= "<div class='famDiv' style=''>";
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
                                 "' style='' class='catPopBtn'><h3>" .
                                 $famName . "</h3>

                <p>Exp: " . $famExp . "</p>
                <p>% Total: " . number_format(($famExp / $expTotal) * 100, 2, '.', ',') . "</p>
                </div>";

                $data['hiddenHTML'] .= "<div id='" . $divID . "' style='' class='popup catPop'>
                <div id='catHeader'><h3>".strtoupper($period)." ".$famName." EXPENSES</h3></div><div class='catDiv' style=''>";
                foreach($catArray as $catName) {
                    isset($_SESSION['report'][$period][$famName]['exp'][$catName]) ? $catExp = $_SESSION['report'][$period][$famName]['exp'][$catName] : $catExp = 0;
                    isset($_SESSION['report'][$period][$famName]['inc'][$catName]) ? $catInc = $_SESSION['report'][$period][$famName]['inc'][$catName] : $catInc = 0;
                    $catTotal = number_format(($catExp + $catInc), 2, '.', ',');
                    
                    if(isset($_SESSION['report'][$period][$famName]['exp'][$catName])) {
                        $data['hiddenHTML'] .= "<div data-period='".$period."' data-category='".$catName."'  class='catLine'><div class='catLabel'>" . $catName . "</div><div class='catTotal'> " .
                                               $catTotal .
                                               "</div></div>";
                    }
                }

                $data['hiddenHTML'] .= "</div></div>";
            }
            $data['html'] .= "</div></div>";

        }

    }

    echo json_encode($data);