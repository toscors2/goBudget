<?php
    session_start();

    include('../cfg/connect.php');

    /**
     * @param $conn mysqli
     * @return array
     */
    function buildTransArray($conn, $periods) {
        $report = [];
        $getTrans = $conn->prepare("
SELECT a.amount, a.transDate, a.type, b.iQty, b.iPrice, b.iCategory, d.familyName FROM budget.quickEntry AS a 
LEFT JOIN budget.lineItems AS b ON a.transID = b.transID
LEFT JOIN budget.iCategories AS c ON b.iCategory = c.catName
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
                    !isset($report[$period][$family][$type][$category])
                        ? $report[$period][$family][$type][$category] = $iAmount
                        : $report[$period][$family][$type][$category] += $iAmount;

                    !isset($report[$period][$type]['total'])
                        ? $report[$period][$type]['total'] = $iAmount
                        : $report[$period][$type]['total'] += $iAmount;

                    !isset($report[$period][$family][$type]['total'])
                        ? $report[$period][$family][$type]['total'] = $iAmount
                        : $report[$period][$family][$type]['total'] += $iAmount;
                }
            }

        }

        return $report;
    }

    function defineConstants() {
        define("BR", "</br>");
        define("FORMAT", "Y-m-d");
        define("DISPLAY", "m/d/Y");
    }

    function buildCatArray($conn) {

        $catArray = [];

        $categories = $conn->prepare("SELECT catName FROM budget.iCategories ORDER BY catName");
        $categories->execute();
        $categories->store_result();
        $categories->bind_result($catName);

        while($categories->fetch()) {
            $catArray[] = $catName;
        }

        return $catArray;
    }

    function figurePeriodSummary($report, $periods) {

        $data = [];

        foreach($periods as $period) {
            isset($report[$period]['inc']['total'])
                ? $incTotal = $report[$period]['inc']['total'] * -1
                : $incTotal = 0;
            isset($report[$period]['exp']['total'])
                ? $expTotal = $report[$period]['exp']['total']
                : $expTotal = 0;
            isset($report[$period]['HOUSEHOLD']['inc']['total'])
                ? $houseInc = $report[$period]['HOUSEHOLD']['inc']['total']
                : $houseInc = 0;

            $cashFlow = number_format(($incTotal - $expTotal), 2, '.', ',');

            $expTotal = $expTotal + $houseInc;  //$houseInc is a negative number as is all income from database
            $incTotal = $incTotal + $houseInc;  //$houseInc is a negative number as is all income from database

            $data[$period] = ['exp' => $expTotal, 'inc' => $incTotal, 'cashFlow' => $cashFlow];
        }

        return $data;
    }

    /**
     * @param $period string
     * @param $periodSummary array
     * @return string
     */
    function showPeriodSummary($period, $periodSummary) {

        $start = $period . 'Start';
        $end = $period . 'End';

        $periodStart = $_SESSION[$start];
        $periodEnd = $_SESSION[$end];

        $html = "<div id='" . $period . "'>
                  <div class='periodLine'>
                  <div class='period'>
             <h1  class='periodHeader'>" .
                strtoupper($period) .
                "</h1></div>
                         <div data-period='" . $period . "' class='timePeriod'><p class='periodText'>(" .
                $periodStart->format(DISPLAY) . " - " .
                $periodEnd->format(DISPLAY) . ")</p></div></div>
             <div class='periodSummaryDiv'>
             <div data-type='exp' data-period=" . $period .
                " class='periodSummary thirdWidth'><div>Expense:</div><div>" . $periodSummary['exp'] . "</div></div>
             <div data-type='inc' data-period=" . $period .
                " class='periodSummary thirdWidth'><div>Income:</div><div>" . $periodSummary['inc'] . "</div></div>
             <div data-type='cfl' data-period=" . $period .
                " class='periodSummary thirdWidth'><div>Cash Flow:</div><div>" .
                $periodSummary['cashFlow'] . "</div></div>
             </div>";

        return $html;
    }

    function figureFamilyExp($report, $period, $famName) {
        switch($famName) {
            case 'PERSONAL':  //simply total personal expenses for period
                isset($report[$period]['PERSONAL']['exp']) ?
                    $famExp = $report[$period]['PERSONAL']['exp']['total'] : $famExp = 0;

                break;
            case 'HOUSEHOLD': //subtracts household income for period from expenses to account for roommates
                isset($report[$period]['HOUSEHOLD']['exp']) ?
                    $famExp = $report[$period]['HOUSEHOLD']['exp']['total'] : $famExp = 0;
                isset($report[$period]['HOUSEHOLD']['inc']) ?
                    $famInc = $report[$period]['HOUSEHOLD']['inc']['total'] * -1 : $famInc = 0;
                $famExp = $famExp - $famInc;
                break;
            case 'MISC': //used to keep track of sales tax
                isset($report[$period]['MISC']['exp']) ?
                    $famExp = $report[$period]['MISC']['exp']['total'] : $famExp = 0;
                break;
            case 'BUSINESS':
                isset($report[$period]['BUSINESS']['exp']) ?
                    $famExp = $report[$period]['BUSINESS']['exp']['total'] : $famExp = 0;
                break;
            default:
                $famExp = null;;
        }

        return $famExp;
    }

    function getHiddenHtml($divID, $period, $famName, $arrays, $report) {
        $html = "<div id='" . $divID . "'  class='popup catPop'>
                <div id='catHeader'><h3>" . strtoupper($period) . " " . $famName .
                " EXPENSES</h3></div><div class='catDiv'>";
        foreach($arrays['categories'] as $catName) {
            isset($report[$period][$famName]['exp'][$catName]) ?
                $catExp = $report[$period][$famName]['exp'][$catName] : $catExp = 0;
            isset($report[$period][$famName]['inc'][$catName]) ?
                $catInc = $report[$period][$famName]['inc'][$catName] : $catInc = 0;
            $catTotal = number_format(($catExp + $catInc), 2, '.', ',');

            if(isset($report[$period][$famName]['exp'][$catName])) {
                $html .= "<div data-period='" . $period . "' data-category='" . $catName .
                         "'  class='catLine'><div class='catLabel'>" . $catName .
                         "</div><div class='catTotal'> " .
                         $catTotal .
                         "</div></div>";
            }
        }

        $html .= "</div></div>";

        return $html;
    }

    function arrays($conn) {
        $periods = ['wtd', 'mtd', 'qtd', 'ytd'];
        $times = ['curr', 'previous', 'ly'];
        $types = ['exp', 'inc', 'tips', 'transfer'];
        $families = ['PERSONAL', 'HOUSEHOLD', 'MISC', 'BUSINESS'];
        $catArray = buildCatArray($conn);

        return ['periods'    => $periods, 'times' => $times, 'types' => $types, 'families' => $families,
                'categories' => $catArray];
    }

    defineConstants();

    $data['html'] = $data['hiddenHTML'] = '';

    $arrays = arrays($conn);

    $report = buildTransArray($conn, $arrays['periods']);

    $periodSummary = figurePeriodSummary($report, $arrays['periods']);

    foreach($periodSummary as $period => $values) {

        $data['html'] .= showPeriodSummary($period, $values);

        if(!isset($report[$period])) {
            $data['html'] .= "Nothing Entered For This Period";
        } else {

            $data['html'] .= "<div class='famDiv'>";
            foreach($arrays['families'] as $famName) {
                $divID = $period . $famName;

                $famExp = figureFamilyExp($report, $period, $famName);

                $expPercent = number_format(($famExp / $periodSummary[$period]['exp']) * 100, 2, '.', ',');

                $data['html'] .= "<div data-family='" . $famName . "' data-period='" . $period .
                                 "'  class='catPopBtn'><h3>" .
                                 $famName . "</h3>

                <p>Exp: " . $famExp . "</p>
                <p>% Total: " . $expPercent . "</p>
                </div>";

                $data['hiddenHTML'] .= getHiddenHtml($divID, $period, $famName, $arrays, $report);

            }

            $data['html'] .= "</div></div>";

        }

    }

    echo json_encode($data);