<?php declare(strict_types=1);
use Tempest\View\GenericView;

/** @var GenericView $this */ ?>

parent <?= $this->prop ?> <?= $this->include('Views/include-child.php') ?>