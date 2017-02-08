<?php

    session_start();

    include("../cfg/connect.php");

    isset($_POST['quickEntry']) ? $quickEntry = $_POST['quickEntry'] : $quickEntry = false;

    $data = [];
    $data['errors'] = $data['transfer'] = false;
//    $lineItem = 'noItems';
    $iSource = '';
    $iTransID = '';
    $entryID = '';
    $amount = '';
    $lineID = null;
    function test_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);

        return $data;
    }

    isset ($_POST['iTransID']) ? $item = $_POST : $item = $_SESSION['recurringPayment'];

    if($item['iPrice'] != '' && $item['iName'] != '') {

        $iTransID = test_input($item['iTransID']);
        $iSource = test_input($item['iSource']);
        $iNumber = test_input($item['iNumber']);
        $iName = test_input($item['iName']);
        $iCategory = test_input($item['iCategory']);
        $iPrice = test_input($item['iPrice']);
        $iQty = test_input($item['iQty']);
        $iPack = test_input($item['iPack']);
        $iSize = test_input($item['iSize']);

        $insertSQL =
            'INSERT INTO budget.lineItems (transID, iSource, iNumber, iName, iCategory, iPrice, iPack, iSize, iQty) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $selectSQL = 'SELECT amount FROM budget.quickEntry WHERE transID = ?';
        $checkSQL = 'SELECT sum(iPrice) FROM budget.lineItems WHERE transID = ?';

        $select = $conn->prepare($selectSQL);
        $select->bind_param("s", $iTransID);
        $select->execute();
        $select->store_result();
        $select->bind_result($amount);
        $select->fetch();

        if($amount < 0) {
            $iPrice = $iPrice * -1;
        }

        if($iCategory == 'TRANSFER') {
            $data['transfer'] = 'select';
            $_SESSION['updateBalance'][] = 'transfer';
            $_SESSION['transferAmount'] = $iPrice;
        }

        $insert = $conn->prepare($insertSQL);
        $insert->bind_param("sssssssss", $iTransID, $iSource, $iNumber, $iName, $iCategory, $iPrice, $iPack, $iSize,
            $iQty);
        $insert->execute();

        $check = $conn->prepare($checkSQL);
        $check->bind_param("s", $iTransID);
        $check->execute();
        $check->store_result();
        $check->bind_result($checkAmount);
        $check->fetch();

        $_SESSION['difference'] = $data['difference'] = $amount - $checkAmount;
        $_SESSION['boxAmount'] = $data['boxAmount'] = $checkAmount;

        $check ? $data['check'] = true : $data['check'] = false;
        $insert ? $data['insert'] = true : $data['insert'] = false;

    } else {
        $data['errors'] = 'nothing to insert or insufficient information sent';
    }

    $data['source'] = $iSource;
    $data['transID'] = $iTransID;
    $data['quickEntry'] = $quickEntry;

    echo json_encode($data);

