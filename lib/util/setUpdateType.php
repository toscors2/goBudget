<?php

    session_start();

    isset($_POST['type']) ? $_SESSION['updateBalance'][] = $_POST['type'] : $_SESSION['updateBalance'][] = null;