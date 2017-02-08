<?php

    include("../cfg/connect.php");

    define("BR", "</br>");
    define("FORMAT", "D, M d Y");

    $dayInterval = new DateInterval("P1D");
    $monthInterval = new DateInterval("P1M");

    $today = new DateTime();

    $tomorrow = new DateTime();
    $tomorrow->add($dayInterval);

    $yesterday = new DateTime();
    $yesterday->sub($dayInterval);

    $thisMonth = new DateTime();

    $nextMonth = new DateTime();
    $nextMonth->add($monthInterval);

    $lastMonth = new DateTime();
    $lastMonth->sub($monthInterval);

    $testMonth = new DateTime();
    $testMonth->sub(new DateInterval("P6M"));

    $month = $lastMonth->format('m');
    $year = $lastMonth->format('Y');

//    $testMonth = $lastMonth->format('m');

    $firstWeekday = date('Y-m-d', strtotime('last Saturday'));
    $lastWeekday = date('Y-m-d', strtotime('next Friday'));
    $tomorrow = date("Y-m-d", strtotime('2 days later'));

    echo $firstWeekday . BR;
    echo $lastWeekday . BR . BR;
    echo $tomorrow . BR;

    $wtdTipsSQL =
        $conn->prepare("SELECT sum(amount * -1) FROM budget.quickEntry WHERE (transDate BETWEEN ? AND ?) AND type = 'tips'");
    $wtdTipsSQL->bind_param("ss", $firstWeekday, $lastWeekday);
    $wtdTipsSQL->execute();
    $wtdTipsSQL->store_result();
    $wtdTipsSQL->bind_result($tips);
    $wtdTipsSQL->fetch();

    echo $tips . BR;

    $quarter = ceil($testMonth->format('m') / 3);
    echo "test Month: " . $testMonth->format('m') . BR;
    echo "quarter: " . $quarter . BR;

//var_dump($testMonth);