<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2014 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );

class SocialJavascriptChain
{
	public $__chain = array();

	public function __set($property, $value)
	{
		$this->__chain[] = array(
			'type'     => 'set',
			'property' => $property,
			'value'    => $value
		);

		return $this;
	}

	public function __get($property)
	{
		$this->__chain[] = array(
			'type'     => 'get',
			'property' => $property
		);
		return $this;
	}

	public function __call($method, $args)
	{
		$this->__chain[] = array(
			'type'     => 'call',
			'method'   => $method,
			'args'     => $args
		);

		return $this;
	}
}
