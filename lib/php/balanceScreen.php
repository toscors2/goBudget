<?php
    /**
     * Created by PhpStorm.
     * User: root
     * Date: 1/31/17
     * Time: 8:38 AM
     */

    session_start();

    include('../class/Options.php');

    $info = new Options();

    $balances = $info->getTenderBalance();

?>

<div id='balanceDiv'>
    <ul>
        <li><a id='balanceLnk' href='#balance'>Balance</a></li>
        <li><a id='reconcileLnk' href='#reconcile'>Reconcile</a></li>
        <li><a id='uploadLnk' href='#upload'>Upload</a></li>

    </ul>
    <div id='balance' class='tabContent'><?php foreach ($balances as $tender) {echo $tender;} ?></div>
    <div id='reconcile' class='tabContent'>
        <ul>
            <li><a id='reconHomeLnk' href='#reconHome'>Home</a></li>
            <li><a id='reconFoundLnk' href='#reconFound'>Found</a></li>
            <li><a id='reconNotFoundLnk' href='#reconNotFound'>NotFound</a></li>
        </ul>
        <div id='reconHome' class='tabContent'>Getting Info</div>
        <div id='reconFound' class='tabContent'>Getting Info</div>
        <div id='reconNotFound' class='tabContent'>Getting Info</div>
    </div>
    <div id='upload' class='tabContent'>
            <form id='processFileName' name='fileNameForm' method='post' action=''>
                <label><input id='fileName' type='text' name='fileName'/></label>
                <input type='submit' name='submitFileName' value='Submit File Name'/>
            </form>
    </div>
</div>

<script>$('#balanceDiv, #reconcile').tabs();</script>

