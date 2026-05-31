<?php
$conn = new mysqli('localhost', 'root', '', 'matangboss_db');
$conn->query("ALTER TABLE users ADD COLUMN no_whatsapp VARCHAR(20) DEFAULT NULL AFTER foto_profil");
$conn->query("ALTER TABLE users ADD COLUMN alamat TEXT DEFAULT NULL AFTER no_whatsapp");
echo "Column added";
?>
