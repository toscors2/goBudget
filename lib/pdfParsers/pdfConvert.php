<?php

//    $fileName = 'tipReport021217';

    define("BR", "</br>");

    function processFile($fileName) {
        $endpoint = "https://api.zamzar.com/v1/jobs";
        $apiKey = "d0579db43e1cd7937c02a3f5e63253550acc7aaa";
        $sourceFilePath = "original/" . $fileName . ".pdf";
        $targetFormat = "rtf";

        // Since PHP 5.5+ CURLFile is the preferred method for uploading files
        if(function_exists('curl_file_create')) {
            $sourceFile = curl_file_create($sourceFilePath);
        } else {
            $sourceFile = '@' . realpath($sourceFilePath);
        }

        $postData = [
            "source_file"   => $sourceFile,
            "target_format" => $targetFormat
        ];

        $ch = curl_init(); // Init curl
        curl_setopt($ch, CURLOPT_URL, $endpoint); // API endpoint
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        //    curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false); // Enable the @ prefix for uploading files
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return response as a string
        curl_setopt($ch, CURLOPT_USERPWD, $apiKey . ":"); // Set the API key as the basic auth username
        $body = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($body, true);

//        echo "Response:\n---------\n";

        return $response;
    }

    function getJob($jobID) {
//        $jobID = 675259;
        $endpoint = "https://api.zamzar.com/v1/jobs/$jobID";
        $apiKey = "d0579db43e1cd7937c02a3f5e63253550acc7aaa";

        $ch = curl_init(); // Init curl
        curl_setopt($ch, CURLOPT_URL, $endpoint); // API endpoint
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return response as a string
        curl_setopt($ch, CURLOPT_USERPWD, $apiKey . ":"); // Set the API key as the basic auth username
        $body = curl_exec($ch);
        curl_close($ch);

        $job = json_decode($body, true);

//        echo "Job:\n----\n";

        return $job;
    }

    function downloadFile($fileID, $fileName) {
//        $fileID = 17269127;
        $localFilename = "converted/" . $fileName . ".rtf";
        $endpoint = "https://api.zamzar.com/v1/files/$fileID/content";
        $apiKey = "d0579db43e1cd7937c02a3f5e63253550acc7aaa";

        $ch = curl_init(); // Init curl
        curl_setopt($ch, CURLOPT_URL, $endpoint); // API endpoint
        curl_setopt($ch, CURLOPT_USERPWD, $apiKey . ":"); // Set the API key as the basic auth username
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $fh = fopen($localFilename, "wb");
        curl_setopt($ch, CURLOPT_FILE, $fh);

        $body = curl_exec($ch);
        curl_close($ch);

        echo "File downloaded\n";
    }

    function checkFileStatus($convertJob) {

        echo $convertJob['status'];

        $convertJob['status'] != 'successful' ? $status = false : $status = true;

        if(!$status) {
            $test = checkFileStatus($convertJob);
            $fileID = 'status = false';
        } else {
            $fileID = $convertJob['target_files'][0]['id'];

        }

        return $fileID;
    }

    $data = [];

    isset($_POST['action']) ? $action = $_POST['action'] : $action = null;
    isset($_POST['fileName']) ? $fileName = $_POST['fileName'] : $fileName = null;
    isset($_POST['jobID']) ? $jobID = $_POST['jobID'] : $jobID = null;
    isset($_POST['fileID']) ? $fileID = $_POST['fileID'] : $fileID = null;

    switch($action) {
        case 'process':
            $convertFile = processFile($fileName);
            $data['fileName'] = $fileName;
            $data['id'] = $convertFile['id'];
            echo json_encode($data);
            break;
        case 'getJob':
            $convertJob = getJob($jobID);

            while ($convertJob['status'] != 'successful') {
//                echo 'getting status' . BR;
                $convertJob = getJob($jobID);
            }

            if (isset($convertJob['target_files'][0]['id'])) {
                $data['fileID'] = $convertJob['target_files'][0]['id'];
                $data['fileName'] = $fileName;

            } else {
                $data['fileID'] = 'error';

            }
            echo json_encode($data);

            break;
        case 'getFile':
            downloadFile($fileID, $fileName);
            break;
        default:
            echo "error in processing file";
            break;
    }














