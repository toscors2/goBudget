<?php
    include('../cfg/connect.php');


    $typeSQL = $conn->prepare("SELECT typeName, typeNick FROM budget.qeTypes");
    $typeSQL->execute();
    $typeSQL->store_result();
    $typeSQL->bind_result($typeName, $typeNick);

    while($typeSQL->fetch()) {
            echo "<option class='type' value='$typeName'>" . $typeNick . "</option>";
    }