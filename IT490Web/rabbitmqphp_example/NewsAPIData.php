<?php
$results = shell_exec('GET "Here Goes http link for api, delet double quotes"');
$arrayCode = json_decode($results);
var_dump($arrayCode);
?>
