<?php

    session_start();

    include('../cfg/connect.php');

    isset($_POST['transferTender']) ? $_SESSION['transferCode'] = $_POST['transferTender'] : $_SESSION['transferCode'] = null;


    $data['type'] = $_SESSION['type'];

    $from = $_SESSION['tender'];
    $to = $_SESSION['transferCode'];

    $search = [$from, $to];

    for ($i = 0; $i < 2; $i++) {
        $select = $conn->prepare("select tenderName from budget.tender where tenderCode = ?");
        $select->bind_param("s", $search[$i]);
        $select->execute();
        $select->store_result();
        $select->bind_result($tender);
        $select->fetch();

        if ($tender != 'Cash' && $tender != 'Savings') {
            $tender = 'card';
        }

        $search[$i] = strtoupper($tender);
    }

    $from = $search[0];
    $to = $search[1];

    $iName = $from ." TO " .$to;

    $selectXfer = $conn->prepare("select transferID from budget.transfers where transferName = ?");
    $selectXfer->bind_param("s", $iName);
    $selectXfer->execute();
    $selectXfer->store_result();
    $selectXfer->bind_result($iNumber);
    $selectXfer->fetch();

    $data['transID'] = $_SESSION['transID'];
    $data['amount'] = $_SESSION['amount'];
    $data['iNumber'] = $iNumber;
    $data['iName'] = $iName;


    echo json_encode($data);