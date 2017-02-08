<?php
    /**
     * Created by PhpStorm.
     * User: root
     * Date: 1/31/17
     * Time: 8:38 AM
     */

    session_start();

    include("../cfg/connect.php");

    echo "<div id='balanceDiv'>
<ul>
<li><a id='balanceLnk' href='#balance'>Balance</a></li>
<li><a id='reconcileLnk' href='#reconcile'>Reconcile</a></li>

</ul>
<div id='balance' class='tabContent'>Loading Form</div>
<div id='reconcile' class='tabContent'>Feature Coming Soon</div>

</div>

<script>$('#balanceDiv').tabs();</script>";

