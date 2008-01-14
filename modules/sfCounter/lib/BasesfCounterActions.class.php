<?php
/**
 * sfPropelActAsCountableBehaviorPlugin base actions.
 * 
 * @package    plugins
 * @subpackage countable 
 * @author     Xavier Lacot <xavier@lacot.org>
 * @link       http://trac.symfony-project.com/trac/wiki/sfPropelActAsCountableBehaviorPlugin
 */
class BasesfCounterActions extends sfActions
{
  /**
   * Saves a comment, for an authentified user
   */
  public function executeIncrementCounter()
  {
    $token = $this->getRequestParameter('sf_countable_token');
    $object = sfPropelActAsCountableToolkit::retrieveFromToken($token);

    if ($object)
    {
      $context = sfContext::getInstance();

      if (!$context->getRequest()->getCookie($token) == $token)
      {
        $object->incrementCounter();
        $context->getResponse()->setCookie($token, $token);
      }

      $this->counter = $object->getCounter();
    }
    else
    {
      $this->counter = 0;
    }
  }

  /**
   * 
   */
  public function executeMostViewed()
  {
    $this->objects = sfCounterPeer::getMostCounted();
  }
}