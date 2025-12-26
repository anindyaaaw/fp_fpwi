<?php
// GANTI DENGAN DATA CLOUDINARY KAMU
define('CLOUDINARY_CLOUD_NAME', 'dgdsvgswe'); 
define('CLOUDINARY_UPLOAD_PRESET', 'thriftswap_preset'); 

function uploadToCloudinary($file_path) {
    $cloud_name = CLOUDINARY_CLOUD_NAME;
    $upload_preset = CLOUDINARY_UPLOAD_PRESET;
    
    $url = "https://api.cloudinary.com/v1_1/$cloud_name/image/upload";
    
    $data = [
        'file' => new CURLFile($file_path),
        'upload_preset' => $upload_preset
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        $result = json_decode($response, true);
        return $result['secure_url'];
    } else {
        return false;
    }
}
?>