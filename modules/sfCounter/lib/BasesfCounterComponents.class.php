<?php
/**
 * sfPropelActAsCountableBehaviorPlugin base actions.
 * 
 * @package    plugins
 * @subpackage countable 
 * @author     Xavier Lacot <xavier@lacot.org>
 * @link       http://trac.symfony-project.com/trac/wiki/sfPropelActAsCountableBehaviorPlugin
 */
class BasesfCounterComponents extends sfComponents
{
  public function executeCounter()
  {
    $object = $this->object;
    $this->counter = $object->getCounter();

    if ($object instanceof sfOutputEscaperObjectDecorator)
    {
      $object_class = get_class($object->getRawValue());
    }
    else
    {
      $object_class = get_class($object);
    }

    $this->token = sfPropelActAsCountableToolkit::addTokenToSession($object_class, 
                                                                    $object->getPrimaryKey());
  }
}