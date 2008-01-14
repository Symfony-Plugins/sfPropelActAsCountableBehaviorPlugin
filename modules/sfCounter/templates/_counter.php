<?php use_helper('I18N') ?>
<?php use_helper('Javascript') ?>
<span id="sf_countable_<?php echo $token ?>">
  <?php
  echo format_number_choice('[0]Never viewed|[1]Viewed one time|(1,+Inf]Viewed %1% times', 
                            array('%1%' => $counter), 
                            $counter)
  ?>
</span>
<?php if (!$sf_request->getCookie($token) == $token): ?>
  <?php
  echo javascript_tag(
    remote_function(array(
      'update' => 'sf_countable_'.$token,
      'url'    => '@sf_counter?sf_countable_token='.$token,
    ))
  );
  ?>
<?php endif; ?>