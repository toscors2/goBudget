<?php

    session_start();

    /**
     * @param $startDate object
     */
    function setWTD($startDate) {
        $todayEnd = $startDate->format('Y-m-d 23:59:59');
        $todayStart = $startDate->format('Y-m-d 0:0:0');

        $startDate->format('l') != 'Saturday' ?
            $_SESSION['wtdStart'] = new DateTime(date('Y-m-d 0:0:0', strtotime($todayStart . ' last Saturday')))
            : $_SESSION['wtdStart'] = $startDate;
        $startDate->format('l') != 'Friday' ?
            $_SESSION['wtdEnd'] = new DateTime(date('Y-m-d 23:59:59', strtotime($todayStart . ' next Friday')))
            : $_SESSION['wtdEnd'] = new DateTime(strtotime($todayEnd));
    }

    function setQTD($quarter) {
        switch($quarter) {
            case 0:
                $qtdStart = (date('Y-m-d 0:0:0', strtotime('-3 months')));
                break;
            case 1:
                $qtdStart = (date('Y-m-d 0:0:0', strtotime('January 1')));
                break;
            case 2:
                $qtdStart = date('Y-m-d 0:0:0', strtotime('April 1'));
                break;
            case 3:
                $qtdStart = date('Y-m-d 0:0:0', strtotime('July 1'));
                break;
            case 4:
                $qtdStart = date('Y-m-d 0:0:0', strtotime('October 1'));
                break;
            default:
                $qtdStart = null;
                $qtdEnd = null;
                break;
        }

        $_SESSION['qtdEnd'] = new DateTIme (date('Y-m-t 23:59:59', strtotime($qtdStart . " +2 months")));
        $_SESSION['qtdStart'] = new DateTime($qtdStart);
    }

    function setMTD($month) {
        $_SESSION['mtdStart'] = new DateTime (date('Y-m-01 0:0:0', strtotime($month)));
        $_SESSION['mtdEnd'] = new DateTIme (date('Y-m-t 23:59:59', strtotime($month)));
    }

    function setYTD($year) {
        $_SESSION['ytdStart'] = new DateTime (date('Y-01-01 0:0:0', strtotime($year)));
        $_SESSION['ytdEnd'] = new DateTime (date('Y-12-31 23:59:59', strtotime($year)));
    }

    isset($_POST['period']) ? $period = $_POST['period'] : $period = 'current';

    $startDate = new DateTime(date('Y-m-d 0:0:0'));
    $quarter = ceil(date('m') / 3);
    $month = date('Y-m-d');
    $year = date('Y-m-d');

    switch ($period) {
        case 'lastWeek':
            $startDate->modify('-1 week');
            break;
        case 'lastMonth':
            $month = date('Y-m-d', strtotime('last Month'));
            break;
        case 'lastYear':
            $year = date('Y-m-d', strtotime('last Year'));
            break;
        case 'lastQtr':
            $quarter -= 1;
            break;
        default:
            break;
    }

    setWTD($startDate);
    setMTD($month);
    setQTD($quarter);
    setYTD($year);


    $_SESSION['startDate'] = $startDate;

    echo json_encode($_SESSION);
