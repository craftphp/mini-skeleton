Xin chào, đây là trang chủ của ứng dụng sử dụng Craft-mini skeleton!

<?= $message ? '<div class="flash">' . $message . '</div>' : '' ?>

<p>Mã hash(default) thử nghiệm: <?= $testHash ?></p>
<p>Mã hash(bcrypt) thử nghiệm: <?= $testHash2 ?></p>
<p>Mã hash(argon2i) thử nghiệm: <?= $testHash3 ?></p>
<p>Xác minh hash thành công: <?= $testVerify ? 'Đúng' : 'Sai' ?></p>