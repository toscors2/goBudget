<?php
    /**
     * Created by PhpStorm.
     * User: root
     * Date: 1/31/17
     * Time: 6:43 PM
     */

    include('../cfg/connect.php');

    isset($_POST) ? $recurInfo = $_POST : $recurInfo = null;

    $info = [];
    $errors = [];

    $errors['status'] = false;

    if($recurInfo != null) {
        foreach($recurInfo as $column => $value) {
            $info[$column] = $value;
        }
        $insert = $conn->prepare("INSERT INTO budget.recurringSources 
                            (dueOn, source, type, category, startDate, frequency, name) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insert->bind_param("sssssss", $info['dueOn'], $info['source'], $info['type'], $info['category'],
            $info['startDate'], $info['frequency'], $info['name']);
        $insert->execute();

        if (!$insert) {
            $errors['status'] = true;
            $errors['messages'][] = 'Trans Info Not Inserted In Database';
        }

    } else {
        $errors['status'] = true;
        $errors['messages'][] = 'No Info Submitted';
    }

    echo json_encode($errors);



