<?php
    /**
     * Created by PhpStorm.
     * User: root
     * Date: 2/16/17
     * Time: 12:52 PM
     */

    require('../class/Options.php');

    $options = new Options();


    $tender = $options->getTenderOptions();

    $type = $options->getTypeOptions();

    $family = $options->getFamilyOptions();

    $data = ['tender'=>$tender, 'type'=>$type, 'family'=>$family];

    echo json_encode($data);