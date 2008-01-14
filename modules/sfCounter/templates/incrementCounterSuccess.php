<?php use_helper('I18N') ?>
<?php use_helper('Javascript') ?>
<?php
echo format_number_choice('[0]Never viewed|[1]Viewed one time|(1,+Inf]Viewed %1% times', 
                          array('%1%' => $counter), 
                          $counter)
?>