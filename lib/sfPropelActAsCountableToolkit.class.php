<?php
/*
 * This file is part of the sfPropelActAsCountableBehavior package.
 * 
 * (c) 2008 Xavier Lacot <xavier@lacot.org>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfPropelActAsCountableBehavior toolkit class
 * 
 * @author Xavier Lacot
 */
class sfPropelActAsCountableToolkit
{
  /**
   * Add a token to available ones in the user session, and returns generated it
   *
   * @param  string  $object_model
   * @param  int     $object_id
   * @return string
   */
  public static function addTokenToSession($object_model, $object_id)
  {
    $session = sfContext::getInstance()->getUser();
    $token = self::generateToken($object_model, $object_id);
    $tokens = $session->getAttribute('tokens', array(), 'sf_countables');
    $tokens = array($token => array($object_model, $object_id)) + $tokens;
    $tokens = array_slice($tokens, 0, sfConfig::get('app_sfPropelActAsCountableBehaviorPlugin_max_tokens', 10));
    $session->setAttribute('tokens', $tokens, 'sf_countables');
    return $token;
  }

  /**
   * Generates token representing a countable object from its model and its id
   * 
   * @param  string  $object_model
   * @param  int     $object_id
   * @return string
   */
  public static function generateToken($object_model, $object_id)
  {
    return md5(sprintf('%s-%s-%s', 
                       $object_model, 
                       $object_id, 
                       sfConfig::get('app_sfPropelActAsCountableBehaviorPlugin_salt', 
                                     'c0unt4bl3')));
  }

  /**
   * Returns true if the passed model name is countable
   * 
   * @author     Xavier Lacot
   * @param      string  $object_name
   * @return     boolean
   */
  public static function isCountable($model)
  {
    if (is_object($model))
    {
      $model = get_class($model);
    }

    if (!is_string($model))
    {
      throw new Exception('The param passed to the method isCountable must be a string.');
    }

    if (!class_exists($model))
    {
      throw new Exception(sprintf('Unknown class %s', $model));
    }

    $base_class = sprintf('Base%s', $model);
    return !is_null(sfMixer::getCallable($base_class.':incrementCounter'));
  }

  /**
   * Retrieve a countable object
   * 
   * @param  string  $object_model
   * @param  int     $object_id
   */
  public static function retrieveCountableObject($object_model, $object_id)
  {
    try
    {
      $peer = sprintf('%sPeer', $object_model);

      if (!class_exists($peer))
      {
        throw new Exception(sprintf('Unable to load class %s', $peer));
      }

      $object = call_user_func(array($peer, 'retrieveByPk'), $object_id);

      if (is_null($object))
      {
        throw new Exception(sprintf('Unable to retrieve %s with primary key %s', 
                                    $object_model, 
                                    $object_id));
      }

      if (!sfPropelActAsCountableToolkit::isCountable($object))
      {
        throw new Exception(sprintf('Class %s does not have the countable behavior', 
                                    $object_model));
      }

      return $object;
    }
    catch (Exception $e)
    {
      return sfContext::getInstance()->getLogger()->log($e->getMessage());
    }
  }

  /**
   * Retrieve countable object instance from token
   * 
   * @param  string  $token
   * @return BaseObject
   */
  public static function retrieveFromToken($token)
  {
    $session = sfContext::getInstance()->getUser();
    $tokens = $session->getAttribute('tokens', array(), 'sf_countables');

    if (array_key_exists($token, $tokens) 
        && is_array($tokens[$token]) 
        && class_exists($tokens[$token][0]))
    {
      $object_model = $tokens[$token][0];
      $object_id    = $tokens[$token][1];
      return self::retrieveCountableObject($object_model, $object_id);
    }
    else
    {
      return null;
    }
  }
  
}