<?php
    /**
     * Created by PhpStorm.
     * User: root
     * Date: 1/31/17
     * Time: 12:14 PM
     */

    session_start();

    include("../class/Options.php");

    $options = new Options();

    isset($_POST['frequency']) ? $frequency = $_POST['frequency'] : $frequency = null;

    if($frequency != null) {

        switch($frequency) {

            case 'monthly':
                $html = $options->getMonthlyOptions();
                break;
            case 'weekly':
                $html = $options->getWeeklyOptions();
                break;
            case 'biWeekly':
                $html = $options->getWeeklyOptions();
                break;
            case 'daily':
                $html = $options->getWeeklyOptions();
                break;
            case'quarterly':
                $html = $options->getVariableOptions();
                break;
            case'yearly':
                $html = $options->getVariableOptions();
                break;
            default:
                $html = null;
        }

        if($html != null) {
            foreach($html as $option) {
                echo $option;
            }
        }

    }