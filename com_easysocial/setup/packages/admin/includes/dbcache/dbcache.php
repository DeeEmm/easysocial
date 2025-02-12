<?php
/**
* @package      EasySocial
* @copyright    Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/dbcache/dependencies');

class SocialDbcache
{
    public $adapter;

    public $key;

    public function factory($key, $options = array())
    {
        $instance = new self($key, $options);

        return $instance;
    }

    public function __construct($key, $options = array())
    {
        $this->init($key, $options);
    }

    public function init($key, $options = array())
    {
        $this->key = $key;

        $options['key'] = $key;

        $file = dirname(__FILE__) . '/tables/' . $key . '.php';

        if (!JFile::exists($file)) {
            $this->adapter = new SocialDbcacheTable($options);
            return false;
        }

        require_once($file);

        $classname = 'SocialDbcacheTable' . ucfirst($key);

        if (!class_exists($classname)) {
            $this->adapter = new SocialDbcacheTable($options);
            return false;
        }

        $this->adapter = new $classname($options);

        return true;
    }

    public function __call($name, $args)
    {
        if (!is_callable(array($this->adapter, $name))) {
            return false;
        }

        return call_user_func_array(array($this->adapter, $name), $args);
    }
}
