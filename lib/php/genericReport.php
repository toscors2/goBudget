<?php

    include('../cfg/connect.php');

    define("S", " : ");
    define("BR", "<br>");
    define("UL", "<ul>");
    define("LI", "<li>");
    define("_UL", "</ul>");
    define("_LI", "</li>");

    $itemCount = 0;
    $arrayItemCount = 0;

    /**
     * @param $conn mysqli
     * @return array
     */
    function allTransTree($conn) {
        $itemCount = 0;
        $data = [];
        $categoryQry =
            "SELECT a.iQty, b.transDate, b.transID, b.amount, a.iPrice, 
              a.itemCategory, c.catFamily, a.iName, a.itemSource 
              FROM budget.lineItems AS a 
              JOIN budget.quickEntry AS b ON a.transID = b.transID 
              JOIN budget.iCategories AS c ON a.itemCategory = c.catName 
              WHERE b.processed = 'y' ORDER BY c.catFamily, c.catName, b.transDate";
        $categories = $conn->prepare($categoryQry);
        $categories->execute();
        $categories->store_result();
        $categories->bind_result($itemQty, $transDate, $transID, $totalPrice, $itemPrice, $category, $family, $itemName,
            $source);
        while($categories->fetch()) {

            if(!isset($data[$transID]['amount'])) {
                $data[$transID]['amount'] = 0;
            }
            if(!isset($data[$transID][$family]['amount'])) {
                $data[$transID][$family]['amount'] = 0;
            }
            if(!isset($data[$transID][$family][$category]['amount'])) {
                $data[$transID][$family][$category]['amount'] = 0;
            }
            $itemCount++;

            $qtyPrice = $itemPrice * $itemQty;
            $data[$transID]['amount'] += $qtyPrice;
            $data[$transID]['transDate'] = $transDate;
            $data[$transID]['source'] = $source;
            $data[$transID][$family]['amount'] += $qtyPrice;
            $data[$transID][$family][$category]['amount'] += $qtyPrice;
            $data[$transID][$family][$category][$itemName] = ['itemQty' => $itemQty, 'itemPrice' => $itemPrice];
        }

        $data = json_decode(json_encode($data));

        foreach($data as $transID => $transValue) {
            echo $transID . S . $transValue->transDate . S . $transValue->source . S . $transValue->amount . UL;
            foreach($transValue as $family => $familyValue) {
                if($family != 'transDate' && $family != 'source' && $family != 'amount') {
                    echo LI . $family . S . $familyValue->amount . UL;
                    foreach($familyValue as $category => $categoryValue) {
                        if($category != 'amount') {
                            echo LI . $category . S . $categoryValue->amount . UL;
                            foreach($categoryValue as $item => $details) {
                                if($item != 'amount') {
                                    echo LI . $item . S . $details->itemQty . S . $details->itemPrice . _LI;
                                }
                            }
                            echo _UL . _LI;
                        }
                    }
                    echo _UL . _LI;
                }
            }
            echo _UL . BR;
        }

        return $data;
    }

    $data = allTransTree($conn);

    var_dump($data);




