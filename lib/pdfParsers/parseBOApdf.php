<?php
    /**
     * Created by PhpStorm.
     * User: root
     * Date: 2/8/17
     * Time: 4:08 PM
     */

    include('../cfg/connect.php');

    define('BR', "</br>");

    /**
     * @param $conn mysqli
     * @param $info array
     * @param $sections array
     */
    function writeDB($conn, $info, $sections) {

        foreach($sections as $label) {
            if(isset($info[$label])) {
                foreach($info[$label] as $key => $tag) {
                    $tag = json_decode(json_encode($tag));

                    if(strlen($tag->date) == 8) {
                        echo "date: " . $tag->date . " in the Amount of: " . $tag->amount . " for " .
                             $tag->description .
                             BR . BR;

                        $date = new DateTime($tag->date);
                        $transDate = $date->format("Y-m-d");

                        echo $transDate . BR;

                        $insert =
                            $conn->prepare("INSERT INTO budget.recon (reconDate, reconDesc, reconAmount) VALUES (?, ?, ?)");
                        $insert->bind_param("sss", $transDate, $tag->description, $tag->amount);
                        $insert->execute();

                        if($insert) {
                            echo "Completed" . BR . BR;
                        } else {
                            echo "Failed" . BR . BR;
                        }

                    }

                }
            }
        }

    }

    $filename = 'converted/boa-122216-012317.rtf';

    $depositInfo = $withdrawalInfo = $otherInfo = $serviceInfo = [];
    $depDescInfo = $witDescInfo = $othDescInfo = $svcDescInfo = [];
    $descText = $descriptions = $descRaw = $descClean = [];
    $start = $dates = $amounts = $info = [];
    $dateCount = $amountCount = [];

    $fileContents = file_get_contents($filename);

    $sections = ['deposit', 'withdrawal', 'other', 'service'];

    $secondPageStart = strpos($fileContents, 'Page 2 of');

    $secondPage = substr($fileContents, $secondPageStart);

    $dateRegex = '/[0-9]{2}\/[0-9]{2}\/[0-9]{2}/';
    $amountRegex = '/[+|-|\$]{0,2}?[0-9]{0,3},?[0-9]{1,3}\.[0-9]{2}/';
    $descriptionRegex = '/fs18\s\\\cf1.+/';

    $regexArray = ['date' => $dateRegex, 'amount' => $amountRegex, 'description' => $descriptionRegex];

    $skipOvers = ['ST PETERSBURG FL', 'RECURRING'];

    foreach($sections as $key => $value) {
        switch($value) {
            case 'deposit':
                $find = 'Deposits';
                break;
            case 'withdrawal':
                $find = 'Withdrawals';
                break;
            case 'other':
                $find = 'Other subtractions';
                break;
            case 'service':
                $find = 'Service';
                break;
            default:
                $find = null;
                break;
        }

        if($find != null) {
            $start[$value] = strpos($secondPage, $find);
        }

    }

    $length =
        ['deposit' => $start['withdrawal'] - $start['deposit'], 'withdrawal' => $start['other'] - $start['withdrawal'],
         'other'   => $start['service'] - $start['other']];

    $serviceText = substr($secondPage, $start['service']);

    $text =
        ['deposit'    => substr($secondPage, $start['deposit'], $length['deposit']),
         'withdrawal' => substr($secondPage, $start['withdrawal'], $length['withdrawal']),
         'other'      => substr($secondPage, $start['other'], $length['other']),
         'service'    => substr($serviceText, strpos($serviceText, 'Date') + 5)];

    foreach($text as $label => $content) {
        $descText[$label] = substr($text[$label], strpos($text[$label], 'Description'));
    }

    foreach($descText as $label => $content) {
        $descCount[$label] = preg_match_all($descriptionRegex, $content, $descRaw[$label]);
    }

    foreach($descRaw as $label => $array) {

        foreach($array as $key => $contentArray) {

            foreach($contentArray as $contentKey => $content) {
                $skipOver = false;

                $newContent = trim(substr($content, 9));

                for($i = 0; $i < count($skipOvers); $i++) {
                    if($newContent == $skipOvers[$i]) {
                        $skipOver = true;
                    }
                }

                if(preg_match($amountRegex, $newContent) || preg_match($dateRegex, $newContent)) {
                    $skipOver = true;
                }

                if(strlen($newContent) < 20 && $newContent != 'Counter Credit') {
                    $skipOver = true;
                }

                if(!$skipOver) {
                    $descClean[$label][] = $newContent;
                }
            }

        }

    }

    foreach($sections as $key => $value) {
        $dateCount[$value] = preg_match_all($dateRegex, $text[$value], $dates[$value]);
        $amountCount[$value] = preg_match_all($amountRegex, $text[$value], $amounts[$value]);
        $dates[$value][] = 'total';

        for($i = 0; $i < $amountCount[$value]; $i++) {
            isset($dates[$value][0][$i]) ? $date = $dates[$value][0][$i] : $date = null;
            $amount = $amounts[$value][0][$i];
            isset ($descClean[$value][$i]) ? $description = $descClean[$value][$i] : $description = null;
            $info[$value][$i] = ['date' => $date, 'amount' => $amount, 'description' => $description];
        }
    }

    foreach($sections as $section) {
            echo $section . BR;
        if(isset($info[$section])) {
            foreach($info[$section] as $key => $data) {
                echo $data['date'] . ": " . $data['amount'] . ": " . $data['description'] . BR;
            }
        } else {
echo "No Items For This Section" . BR;
        }
            echo BR . BR;


    }

    writeDB($conn, $info, $sections);






