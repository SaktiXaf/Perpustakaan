<?php
$password = 'sakti123';
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Password: $password\n";
echo "Hash: $hash\n";
echo "Verifikasi: " . (password_verify($password, '$2y$10$8kGL8xzwk0LgJ0jvqkZyVudxJT.xUOuYN46A9p4L4pqEHSoVJPyri') ? 'true' : 'false') . "\n";
?>
