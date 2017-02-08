<?php
    /**
     * Created by PhpStorm.
     * User: root
     * Date: 1/31/17
     * Time: 8:38 AM
     */

    session_start();

    include("../cfg/connect.php");

    echo "<div id='recurring'>
<ul>
<li><a id='newTransLnk' href='#newTrans'>New</a></li>
<li><a id='addTransLnk' href='#addTrans'>Add</a></li>
<li><a id='payTransLnk' href='#payTrans'>Pay</a></li>
<li><a id='editTransLnk' href='#editTrans'>Edit</a></li>
</ul>
<div id='addTrans' class='tabContent'>Loading Form</div>
<div id='payTrans' class='tabContent'>Feature Coming Soon</div>
<div id='newTrans' class='tabContent'>Loading TransActions</div>
<div id='editTrans' class='tabContent'>Feature Coming Soon</div>
</div>

<script>$('#recurring').tabs();</script>";

