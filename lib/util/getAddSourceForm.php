<?php
    /**
     * Created by PhpStorm.
     * User: root
     * Date: 1/31/17
     * Time: 9:08 AM
     */
?>

<form id='addTransForm' name='addTransForm' method='post' action=''>

    <div class='recurLine'>
        <div class='recurLabelDiv'><label for='recurSource' class='recurLabel'>To/From: </label></div>
        <div class='recurInputDiv'><input id='recurSource' class='recurInput center reset' type='text' name='source'
                                          placeHolder='To/From' /></div>
    </div>
    <div class='recurLine'>
        <div class='recurLabelDiv'><label for='recurName' class='recurLabel'>Trans Name: </label></div>
        <div class='recurInputDiv'><input id='recurName' class='recurInput center reset' type='text' name='name'
                                          placeHolder='i.e. WATER BILL' /></div>
    </div>
    <div class='recurLine'>
        <div class='recurLabelDiv'><label for='recurType' class='recurLabel'>Trans Type: </label></div>
        <div class='recurInputDiv'><select id='recurType' class='recurInput center'
                                           name='type'><?php require_once('getType.php'); ?></select>
        </div>
    </div>
    <div class='recurLine'>
        <div class='recurLabelDiv'><label for='recurCategory' class='recurLabel'>Recurring Category:</label></div>
        <div class='recurInputDiv'><select id='recurCategory' class='recurInput center'
                                           name='category'><?php require_once('getCategory.php'); ?></select>
        </div>
    </div>
    <div class='recurLine'>
        <div class='recurLabelDiv'><label for='recurStart' class='recurLabel'>Start Date: </label></div>
        <div class='recurInputDiv'><input id='recurStart' class='recurInput center reset' type='text' name='startDate'
                                          placeHolder='mm/dd/year' /></div>
    </div>
    <div class='recurLine'>
        <div class='frequencyHeader'>Frequency</div>
        <div>
            <div id='frequencySelect' class='frequencyInputDiv'><label><select id='frequency' class='frequencySelect'
                                                                               name='frequency'>
                        <option value='monthly'>Monthly</option>
                        <option value='weekly'>Weekly</option>
                        <option value='biWeekly'>Bi-Weekly</option>
                        <option value='quarterly'>Quarterly</option>
                        <option value='yearly'>Yearly</option>
                        <option value='daily'>Daily</option>
                    </select></label>
            </div>
            <div id='dueOnSelect' class='frequencyInputDiv'>
                <label><select id='dueOn' class='frequencySelect' name='dueOn'></select></label>
            </div>
        </div>
    </div>


</form>

<div id='addXctrl'>
    <div id='resetX' class='Xctrl center'>Reset Form</div>
    <div id='addX' class='Xctrl center'>Add Trans</div>
    <div id='cancelX' class='Xctrl center'>Cancel</div>
</div>
