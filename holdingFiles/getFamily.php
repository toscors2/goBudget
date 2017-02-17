<?php
    include('../cfg/connect.php');

    
    $familySQL = $conn->prepare("SELECT familyName, familyNick FROM budget.family");
    $familySQL->execute();
    $familySQL->store_result();
    $familySQL->bind_result($famName, $famNick);

    while($familySQL->fetch()) {
            echo "<option class='family' value='$famNick'>" . $famName . "</option>";
    }