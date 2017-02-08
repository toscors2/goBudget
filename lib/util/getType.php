<?php
    include('../cfg/connect.php');

    $typeQry = "SELECT typeName, typeNick FROM budget.qeTypes";

    $typeSQL = $conn->prepare($typeQry);
    $typeSQL->execute();
    $typeSQL->store_result();
    $typeSQL->bind_result($typeName, $typeNick);

    while($typeSQL->fetch()) {
            echo "<option class='type' value='$typeName'>" . $typeNick . "</option>";
    }