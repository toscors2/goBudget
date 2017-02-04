<?php
    include('../cfg/connect.php');

    $typeQry = "SELECT catID, catName FROM budget.iCategories";

    $typeSQL = $conn->prepare($typeQry);
    $typeSQL->execute();
    $typeSQL->store_result();
    $typeSQL->bind_result($catID, $catName);

    while($typeSQL->fetch()) {
            echo "<option class='iCategory' value='$catID'>" . $catName . "</option>";
    }