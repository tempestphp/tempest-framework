<?php
/** @var GenericView $this */

use App\Modules\Form\FormController;
use function Tempest\uri;
use Tempest\View\GenericView;

$this->extends('Views/base.php', title: 'Form');
?>

<?php if($this->hasErrors()) { ?>
ERROR!
<?php } ?>

<form action="<?= uri([FormController::class, 'store']) ?>" method="post">
    <div>
        <label for="name">Name:</label>
        <input type="text" name="name" id="name" value="<?= $this->original('name') ?>">
        <?php foreach ($this->getErrorsFor('name') as $error) {?>
            <div>
                <?= $error->message() ?>
            </div>
        <?php } ?>
    </div>

    <div>
        <label for="number">Number:</label>
        <input type="number" name="number" id="number" value="<?= $this->original('number', 0) ?>">
        <?php foreach ($this->getErrorsFor('number') as $error) {?>
            <div>
                <?= $error->message() ?>
            </div>
        <?php } ?>
    </div>

    <button type="submit">Save</button>

</form>