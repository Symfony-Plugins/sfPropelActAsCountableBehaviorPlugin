<?php

/**
 * Subclass for performing query and update operations on the 'sf_counter' table.
 *
 * 
 *
 * @package plugins.sfPropelActAsCountableBehaviorPlugin.lib.model
 */ 
class sfCounterPeer extends BasesfCounterPeer
{
  /**
   * Returns the counters. The possible options are :
   *  * limit : maximal number of counters to be returned
   *  * order : asc or desc, based on the counter field.
   * 
   * @param      Criteria  $c
   * @param      array  $options
   */
  public static function getCounters(Criteria $c = null, $options = array())
  {
    if ($c == null)
    {
      $c = new Criteria();
    }

    if (isset($options['limit']))
    {
      $c->setLimit($options['limit']);
    }

    if (isset($options['order']))
    {
      if ($options['order'] == 'asc')
      {
        $c->addAscendingOrderByColumn(sfCounterPeer::COUNTER);
      }
      else
      {
        $c->addDescendingOrderByColumn(sfCounterPeer::COUNTER);
      }
    }

    return sfCounterPeer::doSelect($c);
  }

  /**
   * returns the most counted object, sorted from the most counted to the less
   * one. The possible options are :
   *  * limit : maximal number of counters to be returned
   *  * order : asc or desc, based on the counter field.
   * 
   * @param      Criteria  $c
   * @param      array  $options
   */
  public static function getMostCounted(Criteria $c = null, $options = array())
  {
    if ($c == null)
    {
      $c = new Criteria();
    }

    if (!$c->getLimit())
    {
      $c->setLimit(sfConfig::get('app_sfPropelActAsCountableBehaviorPlugin_limit', 10));
    }

    $c->addDescendingOrderByColumn(sfCounterPeer::COUNTER);
    $counters = self::getCounters($c, $options);
    $model_counters = array();
    $model_counts = array();
    $objects = array();
    $result = array();

    foreach ($counters as $counter)
    {
      if (!isset($model_counters[$counter->getCountableModel()]))
      {
        $model_counters[$counter->getCountableModel()] = array();
      }

      $model_counters[$counter->getCountableModel()][] = $counter->getCountableId();
      $model_counts[$counter->getCountableModel()][$counter->getCountableId()] = $counter->getCounter();
    }

    foreach ($model_counters as $model => $countables)
    {
      $peer = get_class(call_user_func(array(new $model, 'getPeer')));
      $countable_objects = call_user_func(array($peer, 'retrieveByPKs'), $countables);

      foreach ($countable_objects as $object)
      {
        $object->forceCounter($model_counts[$model][$object->getId()]);
        $objects[$object->getCounter()][] = $object;
      }
    }

    krsort($objects);

    foreach ($objects as $count => $counted)
    {
      foreach ($counted as $object)
      {
        $result[] = $object;
      }
    }

    return $result;
  }
}