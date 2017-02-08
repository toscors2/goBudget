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

    if ($transID != null) {

        $update = $conn->prepare("update budget.upcomingX set xPd = true where id = ?");
        $update->bind_param("s", $transID);
        $update->execute();

    }