<?php

    session_start();

    include('../cfg/connect.php');

    function test_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);

        return $data;
    }

    if(isset($_POST)) {

        $lineID = $_POST['lineID'];

        foreach($_POST as $column => $value) {
            if($column != 'lineID') {
                $columnValue = test_input($value);

                $updateSql =
                    "UPDATE budget.lineItems SET " . $column . " = '" . $columnValue . "' WHERE lineID = '" . $lineID .
                    "'";
                $update = $conn->prepare($updateSql);
                $update->execute();
            }
        }
    }
