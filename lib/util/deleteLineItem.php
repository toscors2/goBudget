<?php

    session_start();
    include('../cfg/connect.php');

    isset ($_POST) ? $lineID = $_POST['lineID'] : $lineID = null;

    if ($lineID != null) {
        $deleteLine = $conn->prepare("delete from budget.lineItems where lineID = ?");
        $deleteLine->bind_param("s", $lineID);
        $deleteLine->execute();
    }