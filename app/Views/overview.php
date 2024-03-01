<?php declare(strict_types=1);
use Tempest\View\GenericView;

/** @var GenericView $this */
$this->extendsPath = 'Views/index.php';
?>

Hello <?= $this->name ?? 'World' ?>!