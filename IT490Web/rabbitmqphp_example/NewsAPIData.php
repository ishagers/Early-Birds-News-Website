<?php

// The API endpoint with your API key
$url = "https://newsapi.org/v2/top-headlines?country=us&category=business&apiKey=898d8c1625884af1a9774e9662cb980d";

// Initialize a cURL session
$curl = curl_init($url);

// Set options for the cURL session
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
]);

// Execute the cURL session and get the response
$response = curl_exec($curl);

// Close the cURL session
curl_close($curl);

// Decode the JSON response
$responseData = json_decode($response);

// Dump the decoded JSON to see the structure
var_dump($responseData);

?>

