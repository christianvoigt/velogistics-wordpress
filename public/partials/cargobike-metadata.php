<?php
$prefix = '_velogistics_';
$nr_of_wheels  = get_post_meta( get_the_ID(), $prefix.'nr_of_wheels', true );
$can_transport_children  = get_post_meta( get_the_ID(), $prefix.'can_transport_children', true );
$max_transport_weight = get_post_meta( get_the_ID(), $prefix.'max_transport_weight', true );
?>
<div class="velogistics-cargobike-metadata">
<div class="nr-of-wheels"><span class="label">Number of wheels:</span> <?php echo absint($nr_of_wheels) ?><div>
<div class="can-transport-children"><span class="label">Can transport children:</span> <?php echo $can_transport_children?'yes':'no' ?><div>
<div class="max-transport-weight"><span class="label">Maximum transport weight:</span> <?php echo sanitize_text_field($max_transport_weight) ?> kg<div>
<div>