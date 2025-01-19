<?php
    $apiKey = isset($_SERVER['HTTP_APIKEY']) ? $_SERVER['HTTP_APIKEY'] : null;
    $expected_api_key = 'gfsfsfssssttetetetryryrrgfvcbbcbcbcbcbcouurrrtrtr64646557';

    $ip_address=$_SERVER['REMOTE_ADDR']; //ip used
    $system_name=gethostname();//computer used

    $documentStoragePath="http://localhost/crescent-uni-api/api/uploaded-files/picture";
?>