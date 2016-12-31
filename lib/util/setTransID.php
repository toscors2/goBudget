<?php

    session_start();

    isset($_POST['transID']) ? $_SESSION['transID'] = $_POST['transID'] : $_SESSION['transID'] = null;