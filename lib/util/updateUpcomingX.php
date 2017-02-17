<?php
    /**
     * Created by PhpStorm.
     * User: root
     * Date: 2/7/17
     * Time: 6:23 PM
     */

    session_start();

    include('../cfg/connect.php');

    isset($_POST['transID']) ? $transID = $_POST['transID'] : $transID = null;
    isset($_POST['formData']) ? $formData = $_POST['formData'] : $formData = null;

    if ($formData != null) {
        $tender = $formData['tender'];
        $transDate = $formData['transDate'];
    } else {
        $tender = $transData = null;
    }

    if ($transID != null) {

        $update = $conn->prepare("update budget.upcomingX set xPd = true, xTender = ?, xPdDate = ? where id = ?");
        $update->bind_param("sss", $tender, $trandDate, $transID);
        $update->execute();

    }