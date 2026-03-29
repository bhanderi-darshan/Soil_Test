<?php

if(isset($_GET['value'])){
    $data = $_GET['value'];

    echo "Received: " . $data;

    file_put_contents("data.txt", $data . "\n", FILE_APPEND);

}else{
    echo "No data received";
}

?>