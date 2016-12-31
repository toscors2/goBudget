<?php

    session_start();

    include('../cfg/connect.php');

    isset($_POST['lineID']) ? $lineID = $_POST['lineID'] : $lineID = null;

    $data = [];

    $getLine =
        $conn->prepare("SELECT transID, iName, iNumber, iPrice, iQty, iCategory, iPack, iSize, iSource 
                        FROM budget.lineItems WHERE lineID = ?");
    $getLine->bind_param("s", $lineID);
    $getLine->execute();
    $getLine->store_result();
    $getLine->bind_result($transID, $iName, $iNumber, $iPrice, $iQty, $iCategory, $iPack, $iSize, $iSource);
    $getLine->fetch();

    $updateForm = "<form id='updateItemForm' data-id='$lineID' class='lineItemForm' name='updateItemForm' method='post'
              action=''>
            <label><input id='iSource' type='text' class='iSource lineItemFormInput' data-id='$lineID'
                          name='iSource' placeholder='To/From' value='$iSource' /></label>
            <label><input id='iNumber' type='text' class='iNumber lineItemFormInput reset' data-id='$lineID'
                          name='iNumber' placeholder='Item Num' value='$iNumber' /></label>
            <label><input id='iName' type='text' class='iName lineItemFormInput reset' data-id='$lineID'
                          name='iName' placeholder='Item Name' value='$iName' required /></label>
            <label><input id='iCategory' type='text' class='iCategory lineItemFormInput reset'
                          data-id='$lineID' name='iCategory' placeholder='Item Category' value='$iCategory' /></label>
            <label><input id='iPrice' type='text' class='lineItemFormInput reset' data-id='$lineID'
                          name='iPrice' placeholder='Item Price' value='$iPrice' required /></label>
            <label><input id='iSize' type='text' class='lineItemFormInput reset' data-id='$lineID'
                          name='iSize' placeholder='Item Size' value='$iSize' /></label>
            <label><input id='iPack' type='text' class='lineItemFormInput reset' data-id='$lineID'
                          name='iPack' placeholder='Item Pack' value='$iPack' /></label>
            <label><input id='iQty' type='text' class='lineItemFormInput qty' data-id='$lineID'
                          name='iQty' value='$iQty' /></label>
            <label><input id='lineID' name='lineID' type='hidden' data-id='$lineID' data-name='lineID' value='$lineID' /></label>
            </form>
            <button id='updateLineItem' data-type='update' class='editLineItem'>Update Item</button>
            <button id='deleteLineItem' data-type='delete' class='editLineItem'>Delete Item</button>";

    $data['form'] = $updateForm;
    $_SESSION['transID'] = $transID;

    echo json_encode($data);