<?php /** @var \Tempest\View\GenericView $this */?>
<html lang="en">
<head>
    <title><?= $this->title ?? 'Home' ?></title>
</head>
<body>
<?= $this->slot ?? '' ?>
</body>
</html>