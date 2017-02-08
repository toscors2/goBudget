<?php
    /**
     * Created by PhpStorm.
     * User: root
     * Date: 2/2/17
     * Time: 10:30 AM
     */

    session_start();

    include("../cfg/connect.php");

    define('BR', '</br>');

    isset ($_POST) ? $sentData = $_POST : $sentData = null;
    $data = [];
    $data['error']['status'] = false;
    $button = null;
    $xid = null;

    if($sentData != null) {

        $xid = $sentData['xid'];
        $recurID = $sentData['recurID'];
        $button = $sentData['button'];
        $xBillDate = $sentData['xBillDate'];
        $xDueDate = $sentData['xDueDate'];
        $xAmount = $sentData['xAmount'];

        switch ($button) {
            case 'addToPay':
                $insert =
                    $conn->prepare("INSERT INTO budget.upcomingX (recurID, xid, xAmount, xBillDate, xDueDate) VALUES (?, ?, ?, ?, ?)");
                break;
            case 'markPaid':
                $insert =
                    $conn->prepare("INSERT INTO budget.upcomingX (recurID, xid, xAmount, xBillDate, xDueDate, xPd) VALUES (?, ?, ?, ?, ?, TRUE)");
                break;
            default:
                $insert = null;
        }

        if($insert != null) {
            $insert->bind_param("sssss", $recurID, $xid, $xAmount, $xBillDate, $xDueDate);
            $insert->execute();
        }

        if(!$insert) {
            $data['error']['status'] = true;
            $data['error']['message'][] = 'Error Inserting New Recurring Trans';
        }

        $updateLastAdd = $conn->prepare('UPDATE budget.recurringSources SET lastAdd = ? WHERE id = ?');
        $updateLastAdd->bind_param("ss", $xDueDate, $recurID);
        $updateLastAdd->execute();

        if(!$updateLastAdd) {
            $data['error']['status'] = true;
            $data['error']['message'][] = 'Error UpDating Last Add Date';
        }

    } else {
        $data['error']['status'] = true;
        $data['error']['message'][] = 'No Data Sent';
    }

    if($button != null) {
        $data['button'] = $button;
    } else {
        $data['error']['status'] = true;
        $data['error']['message'][] = 'No Button Info';
    }

    $data['divID'] = $xid;

    echo json_encode($data);