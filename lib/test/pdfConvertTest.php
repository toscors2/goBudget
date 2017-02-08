

<?php
    function processFile() {
        $endpoint = "https://api.zamzar.com/v1/jobs";
        $apiKey = "d0579db43e1cd7937c02a3f5e63253550acc7aaa";
        $sourceFilePath = "../../convertFiles/original/boa-122216-012317.pdf";
        $targetFormat = "txt";

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

        echo "Response:\n---------\n";
        return $response;
    }

    function checkJob($jobID) {
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

        echo "Job:\n----\n";
        return $job;
    }

    function downloadFile() {
        $fileID = 17269127;
        $localFilename = '../../convertFiles/converted/boa-122216-012317.txt';
        $endpoint = "https://sandbox.zamzar.com/v1/files/$fileID/content";
        $apiKey = "d0579db43e1cd7937c02a3f5e63253550acc7aaa";

        $ch = curl_init(); // Init curl
        curl_setopt($ch, CURLOPT_URL, $endpoint); // API endpoint
        curl_setopt($ch, CURLOPT_USERPWD, $apiKey . ":"); // Set the API key as the basic auth username
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

        $fh = fopen($localFilename, "wb");
        curl_setopt($ch, CURLOPT_FILE, $fh);

        $body = curl_exec($ch);
        curl_close($ch);

        echo "File downloaded\n";
    }

//    $convertFile = processFile();
//
//    $jobID = $convertFile['id'];
//
//    $convertJob = checkJob(675277);
//
//print_r($convertJob);

downloadFile();



