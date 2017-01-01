<?php

    session_start();

    include("../cfg/connect.php");

    $data = [];
    $error = false;
    $checkPrice = 0;
    $tender = null;

    isset($_GET['transID']) ? $data['transID'] = $transID = $_GET['transID'] : $data['errors'] = $error = true;

    $updateSQL =
        "UPDATE budget.quickEntry SET processed = 'y' WHERE transID = ?"; //sql statement to update quickEntry processed
    $verifySQL =
        "SELECT iPrice, iQty, iSource, iCategory FROM budget.lineItems WHERE transID = ?"; //sql statement to get lineItems saved
    $checkSQL =
        "SELECT amount, tender FROM budget.quickEntry WHERE transID = ?"; //sql statement to get amount from quick entry to check with saved items

    if(!$error) {
        $verify = $conn->prepare($verifySQL);
        $verify->bind_param("s", $transID);
        $verify->execute();
        $verify->store_result();
        $verify->bind_result($iPrice, $iQty, $iSource, $iCategory);

        while($verify->fetch()) {
            $checkPrice += $iPrice * $iQty;
        }

        $data['source'] = $iSource;

        $check = $conn->prepare($checkSQL);
        $check->bind_param("s", $transID);
        $check->execute();
        $check->store_result();
        $check->bind_result($checkAmount, $tender);
        $check->fetch();
        $data['savedItems'] = $checkPrice;
        $data['checkAmount'] = $checkAmount;

        $checkPrice != $checkAmount ?
            $difference = number_format($checkAmount, 2, '.', ',') - number_format($checkPrice, 2, '.', ',')
            : $difference = 0;

        if($difference == 0 && $checkAmount != null) {
            $update = $conn->prepare($updateSQL);
            $update->bind_param("s", $transID);
            $update->execute();
            $update ? $data['processed'] = true : $data['processed'] = false;
            $data['transID'] = $transID;

        } else {
            if($checkAmount != null) {
                $data['errors'] = true;
                $data['difference'] = $difference;
            } else {
                $data['errors'] = true;
                $data['messages'][] = "No Items Entered";
            }
        }
    }


//    $data['variables'] = get_defined_vars();


    echo json_encode($data);