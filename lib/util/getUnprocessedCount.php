<?php
    header("Access-Control-Allow-Origin: *");

    include('../cfg/connect.php');

    $returnData = [];
    $unprocessedCount = 0;

    $selectQry = "SELECT entryID FROM budget.quickEntry WHERE processed = 'n' ORDER BY transDate";

    $getUnprocessed = $conn->prepare($selectQry);
    $getUnprocessed->execute();
    $getUnprocessed->store_result();
    $unprocessedCount = $getUnprocessed->num_rows;

    $returnData['unprocessedCount'] = $unprocessedCount;

    echo json_encode($returnData);
