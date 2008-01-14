<ul>
  <?php foreach ($objects as $object): ?>
    <li>
      <?php
      echo format_number_choice('[0]Never viewed|[1]Viewed one time|(1,+Inf]Viewed %1% times', 
                                array('%1%' => $object->getCounter()), 
                                $object->getCounter())
        ?>
      <?php echo get_class($object).' #'.$object->getPrimaryKey(); ?>
    </li>
  <?php endforeach; ?>
</ul>