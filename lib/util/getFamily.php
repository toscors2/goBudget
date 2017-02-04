<?php
    include('../cfg/connect.php');

    $typeQry = "SELECT familyName, familyNick FROM budget.family";

    $typeSQL = $conn->prepare($typeQry);
    $typeSQL->execute();
    $typeSQL->store_result();
    $typeSQL->bind_result($famName, $famNick);

    while($typeSQL->fetch()) {
            echo "<option class='family' value='$famNick'>" . $famName . "</option>";
    }