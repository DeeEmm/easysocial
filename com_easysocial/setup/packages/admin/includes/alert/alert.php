<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

class SocialAlert
{
	static public $instance = null;

	public $registry = [];

	public function __construct()
	{
		return $this;
	}

	public static function factory()
	{
		return new self();
	}

	public static function getInstance()
	{
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function getRegistry($element)
	{
		if (empty($this->registry[$element])) {
			$this->registry[$element] = new SocialAlertRegistry($element);
		}

		return $this->registry[$element];
	}

	/**
	 * Installs the core rules
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function installCoreRules()
	{
		$file = SOCIAL_ADMIN_DEFAULTS . '/alerts.json';

		// Convert the contents to an object
		$alerts = ES::makeObject($file);

		if ($alerts) {
			foreach ($alerts as $element => $rules) {
				$registry = $this->getRegistry($element);

				foreach ($rules as $item) {
					$registry->register( $item->key , $item->value->email , $item->value->system , array( 'core' => $item->value->core ) );
				}
			}
		}

		return true;
	}

	public function installAppRules()
	{
		$appsModel = ES::model( 'apps' );
		$userApps = $appsModel->getApps();

		foreach( $userApps as $app )
		{
			$app->installAlerts();
		}

		return true;
	}

	public function getUserSettings( $uid )
	{
		$model = ES::model( 'alert' );

		$coreRules		= $model->getCoreUserSettings( $uid );
		$appsRules		= $model->getAppsUserSettings( $uid );
		$fieldsRules	= $model->getFieldUserSettings( $uid );

		$result 		= array_merge( $coreRules, $appsRules, $fieldsRules );

		$rules = array();
		$alerts = array();

		foreach( $result as $row )
		{
			$system = true;

			$rule 	= ES::table( 'Alert' );
			$rule->bind( $row );

			$rule->loadLanguage();

			$title		= JText::_( 'COM_EASYSOCIAL_PROFILE_NOTIFICATION_TITLE_' . strtoupper( $row->element ) );

			$element	= $row->element;

			if( $rule->extension )
			{
				$extension	= $rule->getExtension();

				$title		= JText::_( $extension . 'PROFILE_NOTIFICATION_TITLE_' . strtoupper( $row->element ) );
				$element	= $row->element . '-' . $rule->extension;

				$system = false;
			}

			if( $rule->app )
			{
				$title 		= JText::_( 'APP_' . strtoupper( $row->element ) . '_PROFILE_NOTIFICATION_TITLE' );
				$element	= $row->element . '-app';

				$system = false;
			}

			if( $rule->field )
			{
				$title		= JText::_( 'PLG_FIELDS_' . strtoupper( $row->element ) . '_PROFILE_NOTIFICATION_TITLE' );
				$element	= $row->element . '-field';

				$system = false;
			}

			$key = $system ? 'system' : 'others';

			if( !isset( $rules[ $key ][ $element ] ) )
			{
				$rules[ $key ][ $element ]	= array();
			}

			if( !isset( $rules[ $key ][ $element ][ 'data' ] ) )
			{
				$rules[ $key ][ $element ][ 'data' ]	= array();
			}

			$rules[ $key ][ $element ][ 'data' ][]	= $rule;
			$rules[ $key ][ $element ][ 'title' ]	= $title;

		}

		return $rules;
	}
}

class SocialAlertRegistry
{
	public $element		= null;
	public $rules		= array();
	public $users		= array();

	public function __construct( $element = null, $rulename = null )
	{
		if( !is_null( $element ) )
		{
			$this->element = $element;
		}

		if( !is_null( $rulename ) )
		{
			$this->loadRule( $rulename );
		}

		return $this;
	}

	/**
	 * Register the master rule or to update a rule
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function register( $rule, $email = 0, $system = 0, $options = array() )
	{
		// If no type set, then return false
		if( empty( $this->element ) )
		{
			return false;
		}

		$table = ES::table( 'alert' );
		$loaded = $table->load(array('element' => $this->element, 'rule' => $rule));

		if( !$loaded )
		{
			$table->element		= $this->element;
			$table->rule 		= $rule;
			$table->created 	= ES::date()->toSql();
		}

		$table->email 	= $email;
		$table->system 	= $system;

		$table->core	= isset( $options['core'] ) ? $options['core'] : 0;
		$table->app		= isset( $options['app'] ) ? $options['app'] : 0;

		$result = $table->store();

		if (!$result) {
			return false;
		}

		// Save this table to the rules
		$this->rules[$rule] = $table;

		return true;
	}

	/**
	 * Register the rule from master rule to user rule
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function registerUser( $user_id = null )
	{
		// If no element set, then return false
		if (empty($this->element)) {
			return false;
		}

		if( is_null( $user_id ) )
		{
			$user_id = ES::user()->id;
		}

		// Load all the rules in this element
		$this->loadRules();

		foreach( $this->rules as $rule )
		{
			$state = $rule->registerUser( $user_id );

			if( !$state )
			{
				return false;
			}
		}

		return true;
	}

	public function loadRules()
	{
		$model 	= ES::model( 'Alert' );

		$rules = $model->getRules( $this->element );

		// Reassign the rules with the rule namespace as key
		foreach( $rules as $rule )
		{
			if( empty( $this->rules[$rule->rule] ) )
			{
				$this->rules[$rule->rule] = $rule;
			}
		}

		return $this;
	}

	public function loadRule($rulename)
	{
		if (empty($this->rules[$rulename])) {
			$table = ES::table('Alert');

			$rule = $table->load(array('element' => $this->element, 'rule' => $rulename, 'published' => SOCIAL_STATE_PUBLISHED));

			if (!$rule) {
				return false;
			}

			$this->rules[$rulename] = $table;
		}

		return $this;
	}

	public function getRule($rulename)
	{
		if (!$this->loadRule($rulename)) {
			return false;
		}

		return $this->rules[$rulename];
	}
}
