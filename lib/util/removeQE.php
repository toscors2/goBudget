<?php

    include('../cfg/connect.php');

    $data = [];

    isset($_POST['transID']) ? $transID = $_POST['transID'] : $transID = null;

    if($transID != null) {
        $query = "UPDATE budget.quickEntry SET processed = 'd' WHERE transID = ?";

        $trashQE = $conn->prepare($query);
        $trashQE->bind_param("s", $transID);
        $trashQE->execute();

        $deleteLines = $conn->prepare("update budget.lineItems set iStatus = 'd' where transID = ?");
        $deleteLines->bind_param("s", $transID);
        $deleteLines->execute();
    }

    $data['transID'] = $transID;

    echo json_encode($data);