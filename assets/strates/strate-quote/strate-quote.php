<?php
$args = starter_args($args ?? null);
?>

<section <?= options("strate strate-quote", $args) ?>>
  <?php component::quote($args); ?>
</section>
