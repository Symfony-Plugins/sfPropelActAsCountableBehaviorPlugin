<?php
/*
 * This file is part of the sfPropelActAsCountableBehavior package.
 * 
 * (c) 2008 Xavier Lacot <xavier@lacot.org>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
if (in_array('sfCounter', sfConfig::get('sf_enabled_modules', array())))
{
  $r = sfRouting::getInstance();
  $r->prependRoute('sf_counter', '/sfCounter/increment/*', array('module' => 'sfCounter', 'action' => 'incrementCounter'));
}

sfPropelBehavior::registerHooks('sfPropelActAsCountableBehavior', array (
 ':delete:post' => array ('sfPropelActAsCountableBehavior', 'postDelete'),
));

sfPropelBehavior::registerMethods('sfPropelActAsCountableBehavior', array (
  array (
    'sfPropelActAsCountableBehavior',
    'decrementCounter'
  ),
  array (
    'sfPropelActAsCountableBehavior',
    'forceCounter'
  ),
  array (
    'sfPropelActAsCountableBehavior',
    'getCounter'
  ),
  array (
    'sfPropelActAsCountableBehavior',
    'incrementCounter'
  ),
  array (
    'sfPropelActAsCountableBehavior',
    'resetCounter'
  ),
  array (
    'sfPropelActAsCountableBehavior',
    'saveCounter'
  ),
));