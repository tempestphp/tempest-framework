<?php declare(strict_types=1);

use Tests\Tempest\Fixtures\Modules\Home\HomeView;

/** @var HomeView $this */

$this->extendsPath = 'Views/base.php';
?>

Hello, <?= $this->name ?>
