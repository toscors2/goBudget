<?php
    session_start();

    include("../cfg/connect.php");

    $entryDate = date_create(null, new DateTimeZone('America/New_York'));
    $user = 1;
    $transDate = $_POST['transDate'];
    $receipt = $_POST['receipt'];
    $tender = $_POST['tender'];
    $type = $_POST['type'];
    $category = $_POST['category'];
    $amount = $_POST['amount'];
    $data['tips'] = $data['transfer'] = false;

    if($amount > 0 && !preg_match('/./', $amount)) {
        $amount = number_format($amount / 100, 2, '.', ',');
    }

    if($type == 'inc' || $type == 'tips') {
        $amount = $amount * -1;
    }

    switch($type) {
        case 'tips':
            $data['tips'] = true;
            $_SESSION['updateBalance'][] = 'tips';
            $_SESSION['totalTips'] = $amount * -1;
            break;
        case 'transfer':
            $data['transfer'] = true;
            $_SESSION['updateBalance'][] = 'transfer';
            $_SESSION['transferAmount'] = $amount;
            break;
        default:
            $data['transfer'] = false;
            $data['tips'] = false;
    }

    $_SESSION['updateBalance'][] = 'update';
    $_SESSION['updateAmount'] = $amount;
    $_SESSION['updateCode'] = $tender;

    $date = date_create($transDate);

    $thisDate = $entryDate->format("Y-m-d H:i:s");

    $transDate = date_format($date, "Y/m/d");
    $dateCode = date_format($date, "m-d");

    $queryID = "SELECT * FROM budget.quickEntry";
    $idSQL = $conn->prepare($queryID);
    $idSQL->execute();
    $idSQL->store_result();
//    $idCount = $idSQL->last;

    $insert =
        $conn->prepare("INSERT INTO budget.quickEntry (dateTime, transDate, receipt, tender, type, category, amount, userID) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $insert->bind_param("ssssssss", $thisDate, $transDate, $receipt, $tender, $type, $category, $amount, $user);
    $insert->execute();
    $idCount = $insert->insert_id;

    $transID = $idCount . "-" . $dateCode;

    $_SESSION['transID'] = $data['transID'] = $transID;
    $_SESSION['type'] = $data['type'] = $type;
    $_SESSION['amount'] = $data['amount'] = $amount;
    $_SESSION['tender'] = $tender;

    $update = $conn->prepare("UPDATE budget.quickEntry SET transID = ? WHERE entryID = ?");
    $update->bind_param("ss", $transID, $idCount);
    $update->execute();

    echo json_encode($data);
