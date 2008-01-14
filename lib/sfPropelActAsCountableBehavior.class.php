<?php
/*
 * This file is part of the sfPropelActAsCountableBehavior package.
 * 
 * (c) 2008 Xavier Lacot <xavier@lacot.org>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*
 * This behavior permits to attach a counter to Propel objects. Countable 
 * objects must have a primary key
 * 
 * @author   Xavier Lacot <xavier@lacot.org>
 * @see      http://www.symfony-project.com/trac/wiki/sfPropelActAsCountableBehaviorPlugin
 */

class sfPropelActAsCountableBehavior
{
  /**
   * Decrements the counter
   * 
   * @param      BaseObject  $object
   */
  public function decrementCounter(BaseObject $object)
  {
    $counter = $object->getCounter();

    if ($counter > 0 )
    {
      $object->_counter->setCounter($counter - 1);
      $object->saveCounter();
    }
  }

  /**
   * Forces the counter to a certain value, but doesn't save it.
   * @see sfPropelActAsCountableBehavior::saveCounter(BaseObject $object)
   * 
   * @param      BaseObject  $object
   * @param      integer     $value
   */
  public function forceCounter(BaseObject $object, $value)
  {
    if (!is_int($value) || ($value < 0))
    {
      throw new Exception('A counter can only be a non-negative integer.');
    }

    if ((isset($object->_counter)) && ($object->_counter !== null))
    {
      $object->_counter->setCounter($value);
    }
    else
    {
      $object->_forced = true;
      $counter = new sfCounter;
      $counter->setCountableModel(get_class($object));
      $counter->setCountableId($object->getPrimaryKey());
      $counter->setCounter($value);
      $object->_counter = $counter;
    }
  }

  /**
   * Returns the current value of the counter. If it is the first time that this
   * instance of the objects works with its counter, the counter will be
   * retrieved from the database. Else, it will use its cached value.
   * 
   * @param      BaseObject  $object
   * @return     integer
   */
  public function getCounter(BaseObject $object)
  {
    if ($object->isNew() === true)
    {
      throw new Exception('Counters can only be attached to already saved objects.');
    }

    if ((!isset($object->_counter)) || ($object->_counter === null))
    {
      $c = new Criteria();
      $c->add(sfCounterPeer::COUNTABLE_ID, $object->getPrimaryKey());
      $c->add(sfCounterPeer::COUNTABLE_MODEL, get_class($object));
      $counter = sfCounterPeer::doSelectOne($c);

      if (is_null($counter))
      {
        $counter = new sfCounter;
        $counter->setCountableModel(get_class($object));
        $counter->setCountableId($object->getPrimaryKey());
        $counter->setCounter(0);
      }

      if (isset($object->_forced) && $object->_forced)
      {
        $counter->setCounter($object->_counter->getCounter());
      }

      $object->_counter = $counter;
      $object->_forced = false;
    }

    return $object->_counter->getCounter();
  }

  /**
   * Increments the counter
   * 
   * @param      BaseObject  $object
   */
  public function incrementCounter(BaseObject $object)
  {
    $counter = $object->getCounter();
    $object->_counter->setCounter($counter + 1);
    $object->saveCounter();
  }

  /**
   * Post delete hook : when a countable object is deleted, also deletes the
   * associated counter
   * 
   * @param      BaseObject  $object
   */
  public function postDelete(BaseObject $object)
  {
    $c = new Criteria();
    $c->add(sfCounterPeer::COUNTABLE_MODEL, get_class($object));
    $c->add(sfCounterPeer::COUNTABLE_ID, $object->getPrimaryKey());
    sfCounterPeer::doDelete($c);
  }

  /**
   * Resets the counter
   * 
   * @param      BaseObject  $object
   */
  public function resetCounter(BaseObject $object)
  {
    $counter = $object->getCounter();
    $object->_counter->setCounter(0);
    $object->saveCounter();
  }

  /**
   * Saves the counter into the database.
   * 
   * @param      BaseObject  $object
   */
  public function saveCounter(BaseObject $object)
  {
    if (isset($object->_counter) && isset($object->_forced) && $object->_forced)
    {
      $c = new Criteria();
      $c->add(sfCounterPeer::COUNTABLE_ID, $object->getPrimaryKey());
      $c->add(sfCounterPeer::COUNTABLE_MODEL, get_class($object));
      $counter = sfCounterPeer::doSelectOne($c);

      if (is_null($counter))
      {
        $counter = $object->_counter;
      }
      else
      {
        $counter->setCounter($object->_counter->getCounter());
      }

      $object->_counter = $counter;
      $object->_forced = false;
    }

    $object->_counter->save();
  }
}