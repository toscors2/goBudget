<?php
    session_start();
    include("../cfg/connect.php");

    $_SESSION['updateBalance'] = false;

    $testDate = [];
    $dailyBudget = 10.00;
    $format = "l, F dS Y";

    $today = date_create("today", timezone_open('America/New_York'));
    $todayTest = $today->format("Y-m-d");
    $data = [];
    $interval = $difference = $test = '';

    $getTransDates =
        $conn->prepare("
                    SELECT transDate, dateTime, amount, type, category 
                    FROM budget.quickEntry 
                    WHERE amount > 0 and processed != 'd'and transDate = ?");
    $getTransDates->bind_param("s", $todayTest);
    $getTransDates->execute();
    $getTransDates->store_result();
    $getTransDates->bind_result($transDate, $entryDate, $amount, $type, $category);

    while($getTransDates->fetch()) {
        $dailyBudget -= $amount;
    }

    $data['dailyBudget'] = $dailyBudget;
//    $data['dayInterval'] = $interval;
//    $data['today'] = $today->format($format);
//    $data['testDate'] = $testDate->format($format);
//    $data['difference'] = $difference;

    echo json_encode($data);

