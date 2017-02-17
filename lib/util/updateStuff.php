<?php

    include('../cfg/connect.php');

    define("BR", "</br>");

    /**@param $conn mysqli */
    function makeTextUpper($conn) {
        $selectSQL = "SELECT iName, iSource, iCategory, dateTime FROM budget.lineItems";

        $updateSQL = "UPDATE budget.lineItems SET iName = ?, iSource = ?, iCategory = ? WHERE dateTime = ?";

        $select = $conn->prepare($selectSQL);
        $select->execute();
        $select->store_result();
        $select->bind_result($itemName, $itemSource, $itemCategory, $dateTime);

        while($select->fetch()) {
            $newItemName = strtoupper($itemName);
            $newItemSource = strtoupper($itemSource);
            $newItemCategory = strtoupper($itemCategory);

            if($newItemCategory == 'TAXES') {
                $newItemCategory = 'SALES TAX';
            }

            echo $newItemName . " : " . $newItemCategory . " : " . $newItemSource . " : " . $dateTime . "<br>";
            $update = $conn->prepare($updateSQL);
            $update->bind_param("ssss", $newItemName, $newItemSource, $newItemCategory, $dateTime);
            $update->execute();
        }
    }

    /**@param $conn mysqli */
    function removeZeroAmount($conn) {

        $selectSql = 'SELECT dateTime, iPrice FROM budget.lineItems WHERE iPrice = 0';
        $removeSQL = 'DELETE FROM budget.lineItems WHERE iPrice = 0';

        $select = $conn->prepare($selectSql);
        $select->execute();
        $select->store_result();
        $select->bind_result($dateTime, $itemPrice);

        while($select->fetch()) {
            echo $dateTime . ": " . $itemPrice . "<br>";

            $remove = $conn->prepare($removeSQL);
            $remove->execute();
        }

    }

    /**@param $conn mysqli */
    function QEsNotInLineItems($conn) {

        $selectQEsql = 'SELECT transID FROM budget.quickEntry ORDER BY transDate';

        $selectLIsql = 'SELECT transID FROM budget.lineItems GROUP BY transID';

        $selectSQL =
            "SELECT transID, transDate, amount FROM quickEntry WHERE NOT exists (SELECT 1 FROM lineItems WHERE quickEntry.transID = lineItems.transID) && processed != 'd'";

        $selectQE = $conn->prepare($selectQEsql);
        $selectQE->execute();
        $selectQE->store_result();
        $selectQE->bind_result($qeTransID);

        $select = $conn->prepare($selectSQL);
        $select->execute();
        $select->store_result();
        $select->bind_result($transID, $transDate, $transAmount);

        $selectLI = $conn->prepare($selectLIsql);
        $selectLI->execute();
        $selectLI->store_result();
        $selectLI->bind_result($liTransID);

        while($select->fetch()) {
            echo $transID . ": " . $transDate . " for " . $transAmount . "<br>";

            $unprocessSQL =
                "UPDATE quickEntry SET processed = 'n' WHERE transDate = '" . $transDate . "' && transID = '" .
                $transID . "' && amount = " . $transAmount;

            $unprocess = $conn->prepare($unprocessSQL);
            $unprocess->execute();

        }
    }

    /**@param $conn mysqli */
    function fillSourceTbl($conn) {

        $selectSourceQry =
            "SELECT iSource, transID FROM lineItems WHERE iSource NOT IN (SELECT sourceName FROM sources) GROUP BY iSource";

        $selectSource = $conn->prepare($selectSourceQry);
        $selectSource->execute();
        $selectSource->store_result();
        $selectSource->bind_result($itemSource, $transID);

        while($selectSource->fetch()) {
            echo $itemSource . "<br>";
            $insertQry = "INSERT INTO sources (sourceName) VALUES ('" . $itemSource . "')";
            $insert = $conn->prepare($insertQry);
            $insert->execute();
        }

//    $updateSQL = "update lineItems set itemSource = 'WALGREENS' where itemSource = 'WALGREEN'";
//
//    $selectTest = "select transID, itemSource from lineItems where itemSource = 'ARBY\'S'";
//
//    $update = $conn->prepare($updateSQL);
//    $update->execute();
    }

    /**@param $conn mysqli */
    function fillCategoryTbl($conn) {

        $selectCategoryQry =
            "SELECT iCategory, transID FROM lineItems WHERE iCategory NOT IN (SELECT catName FROM iCategories) GROUP BY iCategory";

        $selectCategory = $conn->prepare($selectCategoryQry);
        $selectCategory->execute();
        $selectCategory->store_result();
        $selectCategory->bind_result($itemCategory, $transID);

        while($selectCategory->fetch()) {
            echo $itemCategory . "<br>";
            $insertQry = "INSERT INTO budget.iCategories (catName) VALUES ('" . $itemCategory . "')";
            $insert = $conn->prepare($insertQry);
            $insert->execute();
        }

        $updateSQL = "UPDATE lineItems SET iCategory = 'PERSONAL BILL' WHERE iCategory = 'PERSONAL BILLS'";
//
//    $selectTest = "select transID, itemCategory from lineItems where itemCategory = 'ARBY\'S'";
//
//    $update = $conn->prepare($updateSQL);
//    $update->execute();
    }

    /**@param $conn mysqli */
    function separateSizeFromIname($conn) {
        $selectQry = "SELECT iName FROM lineItems GROUP BY iName";

        $select = $conn->prepare($selectQry);
        $select->execute();
        $select->store_result();
        $select->bind_result($originalItemName);

        while($select->fetch()) {
            //        echo $originalItemName ."<br>";

            $itemDetail = explode(":", $originalItemName);

            $newItemName = $itemDetail[0];
            isset($itemDetail[1]) ? $itemSize = $itemDetail[1] : $itemSize = null;
            isset($itemDetail[2]) ? $itemPack = $itemDetail[2] : $itemPack = null;

            $itemSize == null ? $size = " has undetermined size" : $size = "contains " . $itemSize;
            $itemPack == null ? $pack = " and no pack size<br>" : $pack = " and comes in a " . $itemPack . "<br>";

            $updateQry =
                "UPDATE lineItems SET iName = '" . trim($newItemName) . "', itemSize = '" . trim($itemSize) .
                "', itemPack = '" . trim($itemPack) . "' WHERE iName = '" . $originalItemName . "'";
            $update = $conn->prepare($updateQry);
            $update->execute();
        }
    }

    /**@param $conn mysqli */
    function updateiNumbers($conn) {

//        $updateQRY = ;

        $updateQRY = "UPDATE budget.lineItems SET iNumber = 2001 WHERE iName = 'BUS FARE'";
//        $updateQRY[] = "UPDATE budget.lineItems SET itemNum = 1002 WHERE itemName = 'CARD TO CASH'";
//        $updateQRY[] = "UPDATE budget.lineItems SET itemNum = 1003 WHERE itemName = 'CARD TO CARD'";
//        $updateQRY[] = "UPDATE budget.lineItems SET itemNum = 1004 WHERE itemName = 'CASH TO SAVINGS'";
//        $updateQRY[] = "UPDATE budget.lineItems SET itemNum = 1005 WHERE itemName = 'CARD TO SAVINGS'";
//        $updateQRY[] = "UPDATE budget.lineItems SET itemNum = 1006 WHERE itemName = 'SAVINGS TO CASH'";
//        $updateQRY[] = "UPDATE budget.lineItems SET itemNum = 1007 WHERE itemName = 'SAVINGS TO CARD'";

        $update = $conn->prepare($updateQRY);
        $update->execute();

    }

    /**@param $conn mysqli */
    function removeINumbers($conn) {

        $updateQRY = "UPDATE budget.lineItems SET iNumber = '' WHERE iSource = 'SAV-A-LOT'";

        $update = $conn->prepare($updateQRY);
        $update->execute();

    }

    /**@param $conn mysqli */
    function changeTaxCategory($conn) {

        $select =
            $conn->prepare("SELECT lineID, iName, iCategory FROM budget.lineItems WHERE iName LIKE 'sales tax'");
        $select->execute();
        $select->store_result();
        $select->bind_result($ID, $iName, $iCategory);

        while($select->fetch()) {
            echo $ID . ": " . $iName . " - " . $iCategory . BR;

            $update =
                $conn->prepare("UPDATE budget.lineItems SET iName = 'SALES TAX', iCategory = 'TAXES' WHERE lineID = ?");
            $update->bind_param("s", $ID);
            $update->execute();
        }

    }

    /**@param $conn mysqli */
    function changeSnackCategory($conn) {



            $update = $conn->prepare("UPDATE budget.lineItems SET iCategory = 'SNACKS' WHERE iCategory = 'SNACK'");
            $update->execute();


    }

    /**@param $conn mysqli */
    function changeTipsName($conn) {

        $select = $conn->prepare("SELECT lineID, iName FROM budget.lineItems WHERE iCategory = 'TIPS'");
        $select->execute();
        $select->store_result();
        $select->bind_result($ID, $iName);

        while($select->fetch()) {
            echo $ID . ": " . $iName . BR;

            $update = $conn->prepare("UPDATE budget.lineItems SET iName = 'CASH TIPS' WHERE lineID = ?");
            $update->bind_param("s", $ID);
            $update->execute();
        }

    }

    /**@param $conn mysqli */
    function changeFinancialFee($conn) {
        $update =
            $conn->prepare("UPDATE budget.lineItems SET iCategory = 'FINANCIAL FEE' WHERE iCategory = 'BANK FEE' OR iCategory = 'FINANCE FEE'");
        $update->execute();
    }

    /**@param $conn mysqli */
    function setOldReconciled($conn) {
        $getOldTrans =
            $conn->prepare("SELECT transID FROM budget.quickEntry WHERE transDate < '2016-11-22' AND reconciled = FALSE");
        $getOldTrans->execute();
        $getOldTrans->store_result();
        $getOldTrans->bind_result($transID);

        while($getOldTrans->fetch()) {
            echo $transID . BR;

            $reconcile = $conn->prepare("UPDATE budget.quickEntry SET reconciled = TRUE WHERE transID = ?");
            $reconcile->bind_param("s", $transID);
            $reconcile->execute();

        }
    }

    /**
     * @param $conn mysqli
     */
    function resetReconInfo($conn) {

        $resetDate = '2017-01-01';

        $getQEids = $conn->prepare("SELECT entryID FROM budget.recon WHERE reconDate > ?");
        $getQEids->bind_param("s", $resetDate);
        $getQEids->execute();
        $getQEids->store_result();
        $getQEids->bind_result($entryID);

        while($getQEids->fetch()) {

            echo $entryID .BR;

            $resetQE = $conn->prepare("UPDATE budget.quickEntry SET reconciled = FALSE WHERE entryID = ?");
            $resetQE->bind_param("s", $entryID);
            $resetQE->execute();
        }

        $resetRecon = $conn->prepare("UPDATE budget.recon SET reconStatus = FALSE WHERE reconDate > ?");
        $resetRecon->bind_param("s", $resetDate);
        $resetRecon->execute();

    }


    changeSnackCategory($conn);







