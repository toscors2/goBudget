<?php
    /**
     * Created by PhpStorm.
     * User: root
     * Date: 2/16/17
     * Time: 10:29 AM
     */

    require("Options.php");

    $test = new Options;

    foreach ($test->getTypeOptions() as $results) {
        echo $results ."</br>";
    }
