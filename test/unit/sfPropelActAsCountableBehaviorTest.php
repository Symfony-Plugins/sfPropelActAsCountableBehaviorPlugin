<?php
// test variables definition
define('TEST_CLASS', 'Post');

// initializes testing framework
$sf_root_dir = realpath(dirname(__FILE__).'/../../../../');
$apps_dir = glob($sf_root_dir.'/apps/*', GLOB_ONLYDIR);
$app = substr($apps_dir[0],
              strrpos($apps_dir[0], DIRECTORY_SEPARATOR) + 1,
              strlen($apps_dir[0]));
if (!$app)
{
  throw new Exception('No app has been detected in this project');
}

require_once($sf_root_dir.'/test/bootstrap/functional.php');
require_once($sf_symfony_lib_dir.'/vendor/lime/lime.php');

if (!defined('TEST_CLASS') || !class_exists(TEST_CLASS))
{
  // Don't run tests
  return;
}

// initialize database manager
$databaseManager = new sfDatabaseManager();
$databaseManager->initialize();
$con = Propel::getConnection();

// clean the database
sfCounterPeer::doDeleteAll();
call_user_func(array(_create_object()->getPeer(), 'doDeleteAll'));

// create a new test browser
$browser = new sfTestBrowser();
$browser->initialize();

// start tests
$t = new lime_test(8, new lime_output_color());


// these tests check for the counter consistency
$t->diag('counter consistency');

$object = _create_object();
$object->save();
$t->ok($object->getCounter() == 0, 'a new object has a null counter.');

$object->incrementCounter();
$t->ok($object->getCounter() == 1, 'after one increment, the counters value is 1.');

$rand = rand(1, 100);
$i = 0;

while ($i < $rand)
{
  $object->incrementCounter();
  $i++;
}

$t->ok($object->getCounter() == 1 + $rand, 'each call to getCounter() increments the counter.');

$object2 = _create_object();
$object2->save();
$object2->incrementCounter();
$object2->incrementCounter();

$t->ok(($object->getCounter() == 1 + $rand) && ($object2->getCounter() == 2), 'separate objects have separate counters.');

$object->delete();
$t->ok(sfCounterPeer::doCount(new Criteria) == 1, 'When an object is deleted, its counter is also deleted.');

$object3 = _create_object();
$object3->save();
$object3->incrementCounter();
$object3->incrementCounter();
$object3->incrementCounter();
$object3->incrementCounter();
$object3->incrementCounter();
$object3->incrementCounter();
$object3->incrementCounter();
$object3->incrementCounter();
$object3->incrementCounter();
$object3->incrementCounter();
$object3->incrementCounter();

$most_counted = sfCounterPeer::getMostCounted();
$t->ok($most_counted[0]->getCounter() > $most_counted[1]->getCounter(), 'sfCounterPeer::getMostCounted() returns objects from the most counted to the less one.');

$object3->incrementCounter();
$most_counted[0]->incrementCounter();
$most_counted[0]->incrementCounter();

$t->ok(sfCounterPeer::doCount(new Criteria) == 2, 'sfCounterPeer::getMostCounted() does not duplicate the counters, even while preloading them into the objects.');

// several instances of the same countable object have separate counters 
// lifecycles, once the counter has been selected from the DB.
$t->ok(($object3->getCounter() == 12) && ($most_counted[0]->getCounter() == 13), 'several instances of the same object have separate counter\'s lifes.');


// test object creation
function _create_object()
{
  $classname = TEST_CLASS;

  if (!class_exists($classname))
  {
    throw new Exception(sprintf('Unknow class "%s"', $classname));
  }

  return new $classname();
}