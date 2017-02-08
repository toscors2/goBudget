<?php

    session_start();

    include('../cfg/connect.php');


//    var_dump($_SESSION);

    $cash = $tips = $savings = $transferAmount = $transferAmount = $updateCode = $updateAmount = null;

        $_SESSION['balanceUpdates'] = [];

    /**@param $conn mysqli */
    function updateBalance($amount, $code, $conn) {
        if($amount != null and $code != null) {
            $select = $conn->prepare("select balance from budget.tender where tenderCode = ?");
            $select->bind_param("s", $code);
            $select->execute();
            $select->store_result();
            $select->bind_result($balance);
            $select->fetch();

            $newBalance = $balance + $amount;

            $_SESSION['balanceUpdates'][] = ['code' => $code, 'amount' => $amount];

            $update = $conn->prepare("update budget.tender set balance = ? where tenderCode = ?");
            $update->bind_param("ss", $newBalance, $code);
            $update->execute();
        } else {
            if($amount == null) {
                $data['error'] = 'No Amount Sent, Please Try Again';
            }
            if($code == null) {
                $data['error'] = 'No Code Sent, Please Try Again';
            }
        }
    }

    $data['error'] = false;

    isset($_SESSION['updateBalance']) ? $updateType = $_SESSION['updateBalance'] : $updateType = null;

    foreach($updateType as $update) {
        switch($update) {
            case 'tips':
                $tips = $_SESSION['totalTips'];
                if($tips != null) {
                    $savings = floor($tips * .1);
                    $cash = $tips - $savings;
                    $codes = [1001, 1002];

                    updateBalance($cash, 1001, $conn);
                    updateBalance($savings, 1002, $conn);

//                    foreach($codes as $code) {
//                        $select = $conn->prepare("SELECT balance FROM budget.tender WHERE tenderCode = ?");
//                        $select->bind_param("s", $code);
//                        $select->execute();
//                        $select->store_result();
//                        $select->bind_result($balance);
//                        $select->fetch();
//
//                        switch($code) {
//                            case 1001:
//                                $newBalance = $balance + $cash;
//                                break;
//                            case 1002:
//                                $newBalance = $balance + $savings;
//                                break;
//                            default:
//                                $newBalance = null;
//                                break;
//                        }
//
//                        $update = $conn->prepare("UPDATE budget.tender SET balance = ? WHERE tenderCode = ?");
//                        $update->bind_param("ss", $newBalance, $code);
//                        $update->execute();
//                    }
                } else {
                    $data['error'] = 'No Tips Submitted';
                }
                break;
            case 'transfer':
                $transferAmount = $_SESSION['transferAmount'];
                $transferCode = $_SESSION['transferCode'];

                updateBalance($transferAmount, $transferCode, $conn);

                break;
            case 'update':
                if($tips == null) {
                    $updateAmount = $_SESSION['updateAmount'];
                    $updateAmount *= -1;
                    $updateCode = $_SESSION['updateCode'];

                    updateBalance($updateAmount, $updateCode, $conn);
                }
                break;
            case 'reconcile':
                isset($_POST['code']) ? $code = $_POST['code'] : $code = null;
                isset($_POST['balance']) ? $balance = $_POST['balance'] : $balance = null;

                if ($code != null && $balance != null) {
                    $update = $conn->prepare("update budget.tender set balance = ? where tenderCode = ?");
                    $update->bind_param("ss", $balance, $code);
                    $update->execute();
                }

                break;
            default:
                break;
        }
    }
    $data['session'] = $_SESSION;

    $_SESSION['updateBalance'] = [];


    echo json_encode($data);

