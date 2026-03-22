<?php
$baglanti = new mysqli("localhost","root","","stok_sistemi");

if($baglanti->connect_error){
    die("Bağlantı hatası");
}
?>