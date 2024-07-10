<?php

use function Tempest\uri;
use Tests\Tempest\Fixtures\Modules\Form\FormController;

/** @var \Tempest\View\GenericView $this */

?>

<x-base title="Form">
<!--    --><?php //if ($this->hasErrors()) { ?>
<!--        ERROR!-->
<!--    --><?php //} ?>

    <x-form action="<?= uri([FormController::class, 'store']) ?>">
        <x-input name="name" label="Name" type="text"></x-input>
        <x-input name="number" label="Number" type="number"></x-input>

        <button type="submit">Save</button>
    </x-form>
</x-base>