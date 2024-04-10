<?php

use Tempest\Console\Components\QuestionComponent;

/** @var QuestionComponent $this */
?>

<question> <?= $this->question ?> </question>
<?php foreach ($this->options as $key => $option) { ?><?php echo $this->isSelected($key) ? '[x]<question> ' : '[ ] ' ?><?= $option ?><?php echo $this->isSelected($key) ? ' </question>' : ' ' ?>

<?php } ?>
