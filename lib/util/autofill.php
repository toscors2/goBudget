<?php

    include("../cfg/connect.php");

    define("S", " : ");
    define("BR", "<br>");
    define("UL", "<ul>");
    define("_UL", "</ul>");
    define("LI", "<li>");
    define("_LU", "</li>");

    function test_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        $data = strtoupper($data);

        return $data;
    }

    function stuff() {

        $data = [];

        if($input == 'itemSource') {
            $data['yes']['sources'] = $data['no']['sources'] = false;
            $data['yes']['count']   = $data['no']['count'] = 0;
            while($srcSrch->fetch()) {

                $strTst = -1;
                $strTst = stripos($sourceName, $str);

                if($strTst !== false) {
                    $data['yes']['sources'][] = $sourceName;
                    $data['yes']['count']++;
                } else {
                    $data['no']['sources'][] = $sourceName;
                    $data['no']['count']++;
                }
            }
        }
        echo "<html><body><div data-id='sources' class='afSourceBox' style=''>";
        foreach($data['yes']['sources'] as $sources) {

            echo "<div data-id='sourceName' class='afSourceLine' style=''>" . $sources . "</div>";

        }
        echo "</div></body></html>";
    }

    /**
     * @param $term
     * @param $request
     * @param $conn mysqli
     * @return array
     */
    function autoCompleteInput($term, $request, $conn) {

        $data = [];

        switch($request) {
            case 'source':
                $searchSQL = "SELECT sourceName, sourceID FROM budget.sources WHERE sourceName LIKE '%" . $term .
                             "%' GROUP BY sourceName";
                break;
            case 'category':
                $searchSQL = "SELECT catName, catID FROM budget.categories WHERE catName LIKE '%" . $term .
                             "%' GROUP BY catName";
                break;
            case 'name':
                $searchSQL = "SELECT iName, transID FROM budget.lineItems WHERE iName LIKE '%" . $term .
                             "%' GROUP BY iName";
                break;
            case 'number':
                $searchSQL = "SELECT iNumber, transID FROM budget.lineItems WHERE iNumber LIKE '%" . $term .
                             "%' GROUP BY iNumber";
                break;
            default:
                $searchSQL = null;
                break;
        }

        if($searchSQL != null) {
            $search = $conn->prepare($searchSQL);
            $search->execute();
            $search->store_result();
            $search->bind_result($name, $id);

            while($search->fetch()) {
                $data[] = $name;
            }
        } else {
            $data[] = 'error in search';
        }

        return $data;
    }

    /**@param $conn mysqli */
    function autoFillForm($term, $function, $conn, $transID) {

        $data = [];

        switch($function) {
            case 'fillName':
                $searchSQL
                    = "SELECT iName, iNumber, iPrice, iPack, iSize, iCategory FROM budget.lineItems 
                              WHERE iName = '" . $term . "'";
                break;
            case 'fillNumber':
                $searchSQL
                    = "SELECT iName, iNumber, iPrice, iPack, iSize, iCategory FROM budget.lineItems 
                              WHERE iNumber = '" . $term . "'";
                break;
            default:
                $searchSQL = null;
                break;
        }

        $search = $conn->prepare($searchSQL);
        $search->execute();
        $search->store_result();
        $search->bind_result($iName, $iNumber, $iPrice, $iPack, $iSize, $iCategory);
        $search->fetch();

        $data = ['iName' => $iName, 'iNumber' => $iNumber, 'iPrice' => $iPrice, 'iPack' => $iPack, 'iSize' => $iSize,
                 'transID' => $transID, 'iCategory'=>$iCategory];

        return $data;

    }

    isset ($_GET['term']) ? $term = $_GET['term'] : $term = null;
    isset ($_GET['request']) ? $request = $_GET['request'] : $request = null;
    isset($_GET['function']) ? $function = $_GET['function'] : $function = null;
    isset ($_GET['transID']) ? $transID = $_GET['transID'] : $transID = null;

    $request != 'fill' ? $data = autoCompleteInput($term, $request, $conn)
        : $data = autoFillForm($term, $function, $conn, $transID);

    echo json_encode($data);







