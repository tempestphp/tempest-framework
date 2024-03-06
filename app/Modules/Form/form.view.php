<?php
/** @var \Tempest\View\GenericView $this */

use App\Modules\Form\FormController;
use function Tempest\uri;

$this->extends('Views/base.php', title: 'Form');
?>

<?php if($this->hasErrors()) { ?>
ERROR!
<?php } ?>

<form action="<?= uri([FormController::class, 'store']) ?>" method="post">
    <label for="name">Name:</label>
    <input type="text" name="name" id="name">
    <button type="submit">Save</button>
    <?php foreach ($this->getErrorsFor('name') as $error) {?>
        <div>
            <?= $error->message() ?>
        </div>
    <?php } ?>
</form>