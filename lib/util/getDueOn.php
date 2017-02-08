<?php
    /**
     * Created by PhpStorm.
     * User: root
     * Date: 1/31/17
     * Time: 12:14 PM
     */

    session_start();

    isset($_POST['frequency']) ? $frequency = $_POST['frequency'] : $frequency = null;

    if ($frequency != null) {

        switch ($frequency) {

            case 'monthly':
                include('getMonthlyOptions.php');
                break;
            case 'weekly':
                include('getWeeklyOptions.php');
                break;
            case 'biWeekly':
                include('getWeeklyOptions.php');
                break;
            case 'daily':
                include('getVariableOptions.php');
                break;
            case'quarterly':
                include('getVariableOptions.php');
                break;
            case'yearly':
                include('getVariableOptions.php');
        }


    }