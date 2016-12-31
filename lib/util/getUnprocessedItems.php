<?php

    session_start();

    header("Access-Control-Allow-Origin: *");

    include('../cfg/connect.php');

    $returnData = [];
    $unprocessedCount = 0;

    if(isset($_GET['filterOption'])) {
        $option = $_GET['filterOption'];
    } else {
        $option = 'uAll';
        $returnData['errors'][] = 'no options chosen';
    }

    switch($option) {
        case 'uReceipt':
            $selectQry = "SELECT receipt, amount, tender, transDate, category, entryID, processed, userID 
                                      FROM budget.quickEntry 
                                      WHERE processed = 'n' && receipt = 'y'
                                      ORDER BY transDate";
            break;
        case 'uNoReceipt':
            $selectQry = "SELECT receipt, amount, tender, transDate, category, entryID, processed, userID 
                                      FROM budget.quickEntry 
                                      WHERE processed = 'n' && receipt = 'n'
                                      ORDER BY transDate";
            break;
        case 'uAll':
            $selectQry = "SELECT receipt, amount, tender, transDate, category, entryID, processed, userID 
                                      FROM budget.quickEntry 
                                      WHERE processed = 'n' 
                                      ORDER BY transDate";
            break;
        default:
            $data['errors'][] = 'no filter switched';
    }

    $getUnprocessed = $conn->prepare($selectQry);
    $getUnprocessed->execute();
    $getUnprocessed->store_result();
    $getUnprocessed->bind_result($qeReceipt, $qeAmount, $qeTender, $qeTransDate, $qeCategory, $entryID, $processed,
        $user);

    while($getUnprocessed->fetch()) {
        $unprocessedCount++;
        $_SESSION['entries'] = $returnData['entries'][] =
            ['receipt'  => $qeReceipt, 'amount' => $qeAmount, 'tender' => $qeTender, 'transDate' => $qeTransDate,
             'category' => $qeCategory, 'entryID' => $entryID, 'processed' => $processed, 'user' => $user];
    }

//    $returnData['unprocessedCount'] = $unprocessedCount;

    echo json_encode($returnData);
