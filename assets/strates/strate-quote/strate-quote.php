<?php
$args = isset($args) && is_array($args) ? $args : [];
?>

<section <?= options("strate strate-quote", $args) ?>>
  <?php component::quote($args); ?>
</section>