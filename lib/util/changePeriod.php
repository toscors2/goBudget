<?php
    session_start();
    
    isset($_POST['period']) ? $period = $_POST['period'] : $period = null;
    
    if ($period != null) {
        switch ($period) {
            case 'wtd':
                $html = "<div>
<button id='thisWeek' value='thisWeek' class='changePeriod'>This Week</button>
<button id='lastWeek' value='lastWeek' class='changePeriod'>Last Week</button>
</div>";
                break;
            case 'mtd':
                $html = "<div>
<button id='thisMonth' value='thisMonth' class='changePeriod'>This Month</button>
<button id='lastMonth' value='lastMonth' class='changePeriod'>Last Month</button>
</div>";
                break;
            case 'qtd':
                $html = "<div>
<button id='thisQtr' value='thisQtr' class='changePeriod'>This Quarter</button>
<button id='lastQtr' value='lastQtr' class='changePeriod'>Last Quarter</button>
</div>";
                break;
            case 'ytd':
                $html = "<div>
<button id='thisYear' value='thisYear' class='changePeriod'>This Year</button>
<button id='lastYear' value='lastYear' class='changePeriod'>Last Year</button>
</div>";
                break;
            default:
                break;
        }
    }

    $data['html'] = $html;

    echo json_encode($data);