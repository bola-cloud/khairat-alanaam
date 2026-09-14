<?php
$base64Image = base64_encode(file_get_contents('public/uploaded_files/product_image/6aa822f84007b1789403896.png'));
file_put_contents('sync_image.php', '<?php
$base64 = "' . $base64Image . '";
$dir = __DIR__ . "/public/uploaded_files/product_image";
if (!file_exists($dir)) {
    mkdir($dir, 0777, true);
}
file_put_contents($dir . "/6aa822f84007b1789403896.png", base64_decode($base64));
file_put_contents(__DIR__ . "/public/assets/images/placeholder.png", base64_decode($base64));
echo "Images synced successfully on production!\n";
');
echo "sync_image.php generated!\n";
