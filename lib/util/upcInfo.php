<?php

    $url = "https://api.upcitemdb.com/prod/trial/lookup?upc=4000050315";

    $json = file_get_contents($url);

    $item = json_decode($json);

    var_dump($item);
