<?php

    session_start();

    include('../cfg/connect.php');

    isset($_POST['transID']) && !empty($_POST['transID']) ? $transID = $_POST['transID']
        : $transID = $_SESSION['transID'];

    $getAmount = $conn->prepare("SELECT amount FROM budget.quickEntry WHERE transID = ?");
    $getAmount->bind_param("s", $transID);
    $getAmount->execute();
    $getAmount->store_result();
    $getAmount->bind_result($totalAmount);
    $getAmount->fetch();

    echo "<div id='$transID' class='processInfo fullWidth relative' style='height:calc(100% - 50px); overflow:auto;top: 50px;'>
    <div id='lineItemFormDiv' class=' halfWidth black whiteTxt relative' style='overflow:auto;'>
        <form id='lineItemForm' data-id='$transID' class='lineItemForm' name='lineItemForm' method='post'
              action=''>
            <label><input id='iSource' type='text' class='iSource lineItemFormInput' data-id='$transID'
                          data-name='iSource' placeholder='To/From' value='' /></label>
            <label><input id='iNumber' type='text' class='iNumber lineItemFormInput reset' data-id='$transID'
                          data-name='iNumber' placeholder='Item Num' /></label>
            <label><input id='iName' type='text' class='iName lineItemFormInput reset' data-id='$transID'
                          data-name='iName' placeholder='Item Name' required /></label>
            <label><input id='iCategory' type='text' class='iCategory lineItemFormInput reset'
                          data-id='$transID' data-name='iCategory' placeholder='Item Category' /></label>
            <label><input id='iPrice' type='text' class='lineItemFormInput reset' data-id='$transID'
                          data-name='itemPrice' placeholder='Item Price' required /></label>
            <label><input id='iSize' type='text' class='lineItemFormInput reset' data-id='$transID'
                          data-name='itemSize' placeholder='Item Size' /></label>
            <label><input id='iPack' type='text' class='lineItemFormInput reset' data-id='$transID'
                          data-name='itemPack' placeholder='Item Pack' /></label>
            <label><input id='iQty' type='text' class='lineItemFormInput qty' data-id='$transID'
                          data-name='itemQty' value='1' /></label>
            <label><input id='transID' type='hidden' data-id='$transID' data-name='transID' value='$transID' /></label>
            <input type='submit' data-id='$transID' class='lineItemFormInput' name='submitLineItemForm'
            id='submitLineItemForm' value='Add Item'/>
        </form>
    </div>
    <div id='leftToProcessDiv' class='halfWidth'
         style='height: 50%; position:absolute; background-color: lightblue; border: 1px solid black; box-shadow:inset 1px black; top:0; left:50%; overflow:auto;'>
        <p style='width:100%; text-align:center; font-size:xx-small'>Transaction #" . $transID . " for $" . $totalAmount . "</p>
        <div id='itemBoxItems'></div>
        <div id='boxItemTotal' class='fullWidth' style='height:30px; position:relative; float:right; text-align:right; border-top: 1px solid blue;'></div>
    </div>
    
    <div id='lineItemFormBtns' class='fullWidth' style=''>
        <div class='quarterHeight'>
            <button id='delete' data-transid='" . $transID . "' class='lineItemBtn' value='delete'
                    name='delete'>Delete
            </button>
        </div>
        <div class='quarterHeight'>
            <button id='close' data-transid='" . $transID . "' class='lineItemBtn' value='close'
                    name='process'>Save & Close
            </button>
        </div>
        <div class='quarterHeight'>
            <button id='process' data-transid='" . $transID . "' class='lineItemBtn' value='process'
                    name='close'>Finished
            </button>
        </div>
    </div>
</div>";