<?php
/** @var \Tempest\View\GenericView $this */
$this->extendsPath = 'Views/index.php';
?>

Hello <?= $this->name ?? 'World' ?>!