<?php
$prefix = '_velogistics_';
$commercial  = get_post_meta( get_the_ID(), $prefix.'is_commercial', true );
$load_capacity = get_post_meta( get_the_ID(), $prefix.'load_capacity', true );
$nr_of_wheels = get_post_meta( get_the_ID(), $prefix.'nr_of_wheels', true );
$seats_for_children = get_post_meta( get_the_ID(), $prefix.'seats_for_children', true );
$box_width = get_post_meta( get_the_ID(), $prefix.'box_width', true );
$box_height = get_post_meta( get_the_ID(), $prefix.'box_height', true );
$box_length = get_post_meta( get_the_ID(), $prefix.'box_length', true );
$bike_width = get_post_meta( get_the_ID(), $prefix.'bike_width', true );
$bike_height = get_post_meta( get_the_ID(), $prefix.'bike_height', true );
$bike_length = get_post_meta( get_the_ID(), $prefix.'bike_length', true );
$item_type = get_post_meta( get_the_ID(), $prefix.'item_type', true );
$features = get_post_meta( get_the_ID(), $prefix.'features', true );
if(!is_array($features)){
    $features = array();
}
$feature_labels = $template_args["metadata_options"]["features"];
$item_type_labels = $template_args["metadata_options"]["itemType"];
function find_label($id, $arr) {
    foreach ($arr as $item) {
        if ($item->id === $id) {
            return $item->name;
        }
    }
    return null;
 }
?>
<dl class="velogistics-cargobike-metadata">
<dt class="item-type">Item Type:</dt> <dd><?php echo sanitize_text_field(find_label($item_type, $item_type_labels)) ?><dd>
<dt class="item-type">Features:</dt> <dd><ul><?php 
foreach($features as $feature){
    $label = find_label($feature, $feature_labels);
    echo "<li>".$label."</li>";
}
?></ul><dd>
<dt class="is-commercial">Commercial:</dt> <dd><?php echo $commercial?'yes':'no' ?><dd>
<dt class="load-capacity">Load capacity:</dt> <dd><?php echo sanitize_text_field($load_capacity) ?> kg<dd>
<dt class="nr-of-wheels">Number of Wheels:</dt> <dd><?php echo sanitize_text_field($nr_of_wheels) ?> <dd>
<dt class="seats_for_children">Seats for Children:</dt> <dd><?php echo sanitize_text_field($seats_for_children) ?> <dd>
<dt class="bike-dimensions">Bike dimensions:</dt><dd><?php echo sanitize_text_field($bike_length) ?> x <?php echo sanitize_text_field($bike_width) ?> x <?php echo sanitize_text_field($bike_height) ?> cm<dd>
<dt class="box-dimensions">Box dimensions:</dt><dd><?php echo sanitize_text_field($box_length) ?> x <?php echo sanitize_text_field($box_width) ?> x <?php echo sanitize_text_field($box_height) ?> cm<dd>
</dl>