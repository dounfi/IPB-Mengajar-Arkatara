<?php

$host = "sql205.infinityfree.com";

$user = "if0_41802627";

$pass = "ipbmengajarmid1";

$db = "if0_41802627_db_ipbmengajar";



try {

    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {

    die(json_encode(["error" => "Koneksi database gagal: " . $e->getMessage()]));

}

?>