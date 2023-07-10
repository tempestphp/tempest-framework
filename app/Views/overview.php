<?php
/** @var \Tempest\View\GenericView $this */
$this->extends = 'Views/index.php';
?>

Hello <?= $this->name ?? 'World' ?>!