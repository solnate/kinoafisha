<?php
$return = array();
foreach(['data' => 'test'] as $field=>$val){
    $return[$field] = "'".htmlspecialchars(trim($val))."'";
}
print_r(['data' => 'test']);
print_r($return);