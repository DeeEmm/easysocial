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

class ThemesHelperForm extends EasySocialHelperAbstract
{
	/**
	 * Generates a hidden input to store the active tab
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function activeTab($active = '')
	{
		$theme = ES::themes();
		$theme->set('active', $active);
		$output = $theme->output('admin/html/form/active.tab');

		return $output;
	}

	/**
	 * Renders a colour picker input
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function colorpicker($name, $value = '', $revert = '')
	{
		static $script = null;
		$loadScript = false;

		if (is_null($script)) {
			$loadScript = true;
			$script = true;
		}

		ESCompat::renderColorPicker();

		$theme = ES::themes();
		$theme->set('loadScript', $loadScript);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('revert', $revert);

		$output = $theme->output('admin/html/form/colorpicker');

		return $output;
	}

	/**
	 * Renders a datepicker
	 *
	 * @since	4.0.10
	 * @access	public
	 */
	public function datepicker($name, $value = '', $options = [])
	{
		$id = ES::normalize($options, 'id', '');
		$placeholder = ES::normalize($options, 'placeholder', '');
		$format = ES::normalize($options, 'format', 'DD-MM-YYYY');
		$currentDate = ES::date()->format('Y-m-d H:i:s');

		$theme = ES::themes();
		$theme->set('id', $id);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('currentDate', $currentDate);
		$theme->set('placeholder', $placeholder);
		$theme->set('format', $format);

		$output = $theme->output('admin/html/form/datepicker');

		return $output;
	}

	/**
	 * Renders a dropdown
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function dropdown($name, $items, $selected = '', $attributes = '')
	{
		$theme = ES::themes();
		$theme->set('name', $name);
		$theme->set('items', $items);
		$theme->set('selected', $selected);
		$theme->set('attributes', $attributes);

		$output = $theme->output('admin/html/form/dropdown');

		return $output;
	}

	/*
	 * Renders a iconpicker settings
	 *
	 * @since	3.3
	 * @access	public
	 */
	public function iconpicker($name, $title, $value, $defaultIcon)
	{
		$theme = ES::themes();
		$theme->set('value', $value);
		$theme->set('defaultIcon', $defaultIcon);
		$theme->set('name', $name);
		$theme->set('title', $title);

		$contents = $theme->output('admin/html/form/iconpicker');

		return $contents;
	}

	/**
	 * Renders a honeypot hidden input
	 *
	 * @since	3.2.14
	 * @access	public
	 */
	public function honeypot($name = '', $attributes = '')
	{
		if (!$name) {
			$name = $this->config->get('registrations.honeypotkey');
		}

		$theme = ES::themes();
		$theme->set('name', $name);
		$theme->set('attributes', $attributes);

		$output = $theme->output('admin/html/form/honeypot');

		return $output;
	}

	/**
	 * Renders a hidden input
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function hidden($name, $value = '', $attributes = '')
	{
		$theme = ES::themes();
		$theme->set('attributes', $attributes);
		$theme->set('name', $name);
		$theme->set('value', $value);

		$output = $theme->output('admin/html/form/hidden');

		return $output;
	}

	/**
	 * Generates token for the form.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function token()
	{
		$token = ES::token();

		$theme = ES::themes();
		$theme->set('token', $token);

		$content = $theme->output('admin/html/form/token');

		return $content;
	}

	/**
	 * Allows caller to generically load up a form action which includes the generic data
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function action($controller, $task = '', $view = '')
	{
		$theme = ES::themes();

		$theme->set('controller', $controller);
		$theme->set('task', $task);
		$theme->set('view', $view);

		$output = $theme->output('admin/html/form/action');

		return $output;
	}

	/**
	 * Generates a location form
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function location($location, $options = '', $type = '')
	{
		$uid = uniqid();
		$classname = 'es-location-' . $uid;
		$selectorName = '';
		$selector  = '.' . $classname;

		if (isset($options['selectorName']) && $options['selectorName']) {
			$selectorName = $options['selectorName'];
			$selector = '[' . $selectorName . ']';
		}

		if (empty($location)) {
			$location = ES::table('Location');
		}

		$hasLocationClass = empty($location->latitude) && empty($location->longitude) ? 'is-empty' : 'has-data';

		$theme = ES::themes();
		$theme->set('uid', $uid);
		$theme->set('classname', $classname);
		$theme->set('selector', $selector);
		$theme->set('selectorName', $selectorName);
		$theme->set('location', $location);
		$theme->set('hasLocationClass', $hasLocationClass);

		$namespace = 'site/helpers/form/location';
		if ($type) {
			$namespace .= '.' . $type;
		}

		return $theme->output($namespace);
	}

	/**
	 * Generates the item id
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function itemid($itemid = null)
	{
		// Check for the current itemid in the request
		if (is_null($itemid)) {
			$itemid = $this->input->get('Itemid' , 0, 'int');
		}

		if (!$itemid) {
			return;
		}

		$theme = ES::themes();

		$theme->set( 'itemid'	, $itemid );

		$content = $theme->output('admin/html/form/itemid');

		return $content;
	}


	/**
	 * Renders a WYSIWYG editor that is configured in Joomla
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function editor( $name , $value = '' , $id = '' , $editor = '' )
	{
		// Get the editor
		$editor = ES::getEditor('tinymce'); 

		$theme = ES::themes();

		$theme->set( 'editor'	, $editor );
		$theme->set( 'name'		, $name );
		$theme->set( 'content'	, $value );
		$content 	= $theme->output('admin/html/form/editor');

		return $content;
	}

	/**
	 * Renders the popover html contents
	 *
	 * @since	3.0
	 * @access	public
	 */
	public function label($label, $columns = 3, $help = true, $desc = '')
	{
		if (!$desc) {
			$desc = $label . '_DESC';
			$desc = JText::_($desc);
		}

		$label = JText::_($label);

		$theme = ES::themes();
		$theme->set('columns', $columns);
		$theme->set('help', $help);
		$theme->set('label', $label);
		$theme->set('desc', $desc);

		return $theme->output('site/helpers/form/label');
	}

	/**
	 * Renders a user group select list
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function usergroups( $name , $selected = '' )
	{
		$model = ES::model('Users');
		$groups = $model->getUserGroups();

		$theme = ES::themes();

		$theme->set( 'name', $name );
		$theme->set( 'selected', $selected );
		$theme->set( 'groups', $groups );

		$output = $theme->output('admin/html/form/usergroups');

		return $output;
	}

	/**
	 * Renders a calendar input
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function calendar($name, $value = '', $id = '', $attributes = '', $time = false, $format = 'DD-MM-YYYY', $language = false, $restrictMinDate = true, $fullWidth = false)
	{
		if (is_array($attributes)) {
			$attributes	= implode(' ', $attributes);
		}

		$theme = ES::themes();
		$uuid = uniqid();

		if (!$language) {
			$language = JFactory::getDocument()->getLanguage();
		}

		$theme->set('fullWidth', $fullWidth);
		$theme->set('language', $language);
		$theme->set('time', $time);
		$theme->set('uuid', $uuid);
		$theme->set('format', $format);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('id', $id);
		$theme->set('attributes', $attributes);
		$theme->set('restrictMinDate', $restrictMinDate);

		return $theme->output('admin/html/form/calendar');
	}

	/**
	 * Renders a select list for editors on the site
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function editors($name, $value = '', $id = '', $attributes = '', $inherit = true, $addOption = false)
	{
		if (is_array($attributes)) {
			$attributes	= implode(' ', $attributes);
		}

		$theme = ES::themes();

		// Get list of editors on the site first.
		$editors = self::getEditors();

		if ($addOption) {
			// Add a new option to allow them to choose if they don't want to use the editor #3450
			$newOption = new stdClass();
			$newOption->value = 'noeditor';
			$newOption->text = JText::_('COM_ES_VIDEOS_SETTINGS_NO_EDITOR');

			$editors[] = $newOption;
		}

		$theme->set('inherit', $inherit);
		$theme->set('editors', $editors);
		$theme->set('name', $name);
		$theme->set('value', $value);
		$theme->set('id', $id);
		$theme->set('attributes', $attributes);

		return $theme->output('admin/html/form/editors');
	}

	/**
	 * Renders a simple password input
	 *
	 * @since   2.2
	 * @access  public
	 */
	public function password($name, $id = null, $value = '', $options = array())
	{
		$class = 'o-form-control';
		$placeholder = '';
		$attributes = '';

		if (isset($options['attr']) && $options['attr']) {
			$attributes = $options['attr'];
		}

		if (isset($options['class']) && $options['class']) {
			$class = $options['class'];
		}

		if (isset($options['placeholder']) && $options['placeholder']) {
			$placeholder = JText::_($options['placeholder']);
		}

		$theme = ES::themes();
		$theme->set('attributes', $attributes);
		$theme->set('name', $name);
		$theme->set('id', $id);
		$theme->set('value', $value);
		$theme->set('class', $class);
		$theme->set('placeholder', $placeholder);

		return $theme->output('site/helpers/form/password');
	}

	/**
	 * Displays the text input
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function text($name, $id = null, $value = '', $options = [])
	{
		$class = 'o-form-control';
		$placeholder = '';
		$attributes = '';

		if (isset($options['attr']) && $options['attr']) {
			$attributes = $options['attr'];
		}

		if (isset($options['class']) && $options['class']) {
			$class = $options['class'];
		}

		if (isset($options['placeholder']) && $options['placeholder']) {
			$placeholder = JText::_($options['placeholder']);
		}

		$theme = ES::themes();
		$theme->set('attributes', $attributes);
		$theme->set('name', $name);
		$theme->set('id', $id);
		$theme->set('value', $value);
		$theme->set('class', $class);
		$theme->set('placeholder', $placeholder);

		return $theme->output('admin/html/form/text');
	}

	/**
	 * Displays the email input
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function email($name, $id = null, $value = '', $options = [])
	{
		$class = 'o-form-control';
		$placeholder = '';
		$attributes = '';

		if (isset($options['attr']) && $options['attr']) {
			$attributes = $options['attr'];
		}

		if (isset($options['class']) && $options['class']) {
			$class = $options['class'];
		}

		if (isset($options['placeholder']) && $options['placeholder']) {
			$placeholder = JText::_($options['placeholder']);
		}

		$theme = ES::themes();
		$theme->set('attributes', $attributes);
		$theme->set('name', $name);
		$theme->set('id', $id);
		$theme->set('value', $value);
		$theme->set('class', $class);
		$theme->set('placeholder', $placeholder);

		return $theme->output('admin/html/form/email');
	}

	/**
	 * Displays the textarea input
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function textarea($name, $id = null, $value = '', $options = [])
	{
		if (!$id) {
			$id = $name;
		}

		$placeholder = ES::normalize($options, 'placeholder', '');
		$attributes = ES::normalize($options, 'attributes', '');

		$class = ES::normalize($options, 'class', '');
		$class .= ' o-form-control';

		$theme = ES::themes();
		$theme->set('name', $name);
		$theme->set('id', $id);
		$theme->set('value', $value);
		$theme->set('class', $class);
		$theme->set('placeholder', $placeholder);
		$theme->set('attributes', $attributes);

		return $theme->output('admin/html/form/textarea');
	}


	/**
	 * Retrieve list of editors from the site
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function getEditors()
	{
		$db 	= ES::db();
		$sql 	= $db->sql();

		$sql->select( '#__extensions' );
		$sql->column( 'element' , 'value' );
		$sql->column( 'name' , 'text' );
		$sql->where( 'folder' , 'editors' );
		$sql->where( 'type' , 'plugin' );
		$sql->where( 'enabled' , SOCIAL_STATE_PUBLISHED );

		$db->setQuery( $sql );
		$editors 	= $db->loadObjectList();

		// Load the language file of each editors
		$lang 	= JFactory::getLanguage();

		foreach( $editors as &$editor )
		{
			$lang->load( $editor->text . '.sys' , JPATH_ADMINISTRATOR , null , false , false );

			$editor->text 	= JText::_( $editor->text );
		}

		return $editors;
	}

	/**
	 * Floating label with input form
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function floatinglabel($label, $name, $type = 'text', $value = '', $id = '', $static = false, $inputAttributes = '', $hasInfo = false)
	{
		// This currently only supports textbox and password
		$supported = array('text', 'password', 'email');

		if (!in_array($type, $supported)) {
			return "";
		}

		$info = JText::_($label . '_INFO');
		$label = JText::_($label);

		if (!$id) {
			$id = 'es-' . str_ireplace(array('.'), '', $name);
		}

		$inputClass = 'o-float-label__input ';
		$inputClass .= $static ? 'is-static ' : '';

		$theme = ES::themes();
		$theme->set('inputAttributes', $inputAttributes);
		$theme->set('static', $static);
		$theme->set('inputClass', $inputClass);
		$theme->set('type', $type);
		$theme->set('value', $value);
		$theme->set('label', $label);
		$theme->set('name', $name);
		$theme->set('id', $id);
		$theme->set('hasInfo', $hasInfo);
		$theme->set('info', $info);

		$output = $theme->output('site/helpers/form/' . __FUNCTION__);

		return $output;
	}

	/**
	 * Displays a dropdown list for profile type selection
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function profiles($name, $id = '', $selected = null, $attributes = array())
	{
		// If the id is empty, we'll re-use the name as the id.
		$id = !$id ? $name : $id;

		// Get the list of profiles on the site
		$model = ES::model('Profiles');
		$profiles = $model->getProfiles();

		$multiple = isset($attributes['multiple']) ? $attributes['multiple'] : false;

		// Determines if we should add a default profile into the dropdown
		$default = false;
		if (isset($attributes['default']) && $attributes['default']) {
			$default = true;
			unset($attributes['default']);
		}

		$attributes	= ES::makeArray($attributes);
		$attributes	= implode(' ', $attributes);

		$theme = ES::themes();
		$theme->set('default', $default);
		$theme->set('multiple', $multiple);
		$theme->set('name', $name);
		$theme->set('attributes', $attributes);
		$theme->set('profiles', $profiles);
		$theme->set('id', $id);
		$theme->set('selected', $selected);

		$output = $theme->output('admin/html/form/profiles');

		return $output;
	}

	/**
	 * Displays a pull down select list to select a profile type
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function groupcategories($name, $id = '', $selected = null, $attributes = array())
	{
		// If the id is empty, we'll re-use the name as the id.
		$id = !$id ? $name : $id;

		// Get the list of group categories
		$model = ES::model('GroupCategories');
		$categories = $model->getCategories();

		$multiple = isset($attributes['multiple']) ? $attributes['multiple'] : false;

		$attributes	= ES::makeArray( $attributes );
		$attributes	= implode( ' ' , $attributes );

		$theme = ES::themes();
		$theme->set('multiple', $multiple);
		$theme->set('name', $name );
		$theme->set('attributes', $attributes );
		$theme->set('categories', $categories);
		$theme->set('id', $id );
		$theme->set('selected', $selected);

		$output = $theme->output('admin/html/form/group.categories');

		return $output;
	}

	/**
	 * Displays a pull down select list to select a profile type
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function eventcategories($name, $id = '', $selected = null, $attributes = array())
	{
		// If the id is empty, we'll re-use the name as the id.
		$id = !$id ? $name : $id;

		// Get the list of group categories
		$model = ES::model('EventCategories');
		$categories = $model->getCategories();

		$multiple = isset($attributes['multiple']) ? $attributes['multiple'] : false;

		$attributes	= ES::makeArray( $attributes );
		$attributes	= implode( ' ' , $attributes );

		$theme = ES::themes();
		$theme->set('multiple', $multiple);
		$theme->set('name', $name );
		$theme->set('attributes', $attributes );
		$theme->set('categories', $categories);
		$theme->set('id', $id );
		$theme->set('selected', $selected);

		$output = $theme->output('admin/html/form/event.categories');

		return $output;
	}

	/**
	 * Renders pagination settings
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function pagination($name, $id = '')
	{
		$options = array(
						array('value' => '5', 'text' => 'COM_EASYSOCIAL_STREAM_SETTINGS_5_ITEMS'),
						array('value' => '10', 'text' => 'COM_EASYSOCIAL_STREAM_SETTINGS_10_ITEMS'),
						array('value' => '15', 'text' => 'COM_EASYSOCIAL_STREAM_SETTINGS_15_ITEMS'),
						array('value' => '20', 'text' => 'COM_EASYSOCIAL_STREAM_SETTINGS_20_ITEMS'),
						array('value' => '25', 'text' => 'COM_EASYSOCIAL_STREAM_SETTINGS_25_ITEMS'),
						array('value' => '30', 'text' => 'COM_EASYSOCIAL_STREAM_SETTINGS_30_ITEMS'),
						array('value' => '35', 'text' => 'COM_EASYSOCIAL_STREAM_SETTINGS_35_ITEMS'),
						array('value' => '40', 'text' => 'COM_EASYSOCIAL_STREAM_SETTINGS_40_ITEMS'),
						array('value' => '45', 'text' => 'COM_EASYSOCIAL_STREAM_SETTINGS_45_ITEMS'),
						array('value' => '50', 'text' => 'COM_EASYSOCIAL_STREAM_SETTINGS_50_ITEMS')
					);

		$selected = $this->config->get($name);

		$theme = ES::themes();
		$output = $theme->html('grid.selectlist', $name, $selected, $options);

		return $output;
	}

	/**
	 * Displays a pull down select list to select a profile type
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function pagecategories($name, $id = '', $selected = null, $attributes = array())
	{
		// If the id is empty, we'll re-use the name as the id.
		$id = !$id ? $name : $id;

		// Get the list of page categories
		$model = ES::model('PageCategories');
		$categories = $model->getCategories();

		$multiple = isset($attributes['multiple']) ? $attributes['multiple'] : false;

		$attributes	= ES::makeArray( $attributes);
		$attributes	= implode(' ', $attributes);

		$theme = ES::themes();
		$theme->set('multiple', $multiple);
		$theme->set('name', $name );
		$theme->set('attributes', $attributes );
		$theme->set('categories', $categories);
		$theme->set('id', $id );
		$theme->set('selected', $selected);

		$output = $theme->output('admin/html/form/page.categories');

		return $output;
	}

	/**
	 * Displays a list of menu forms
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function menus($name, $selected, $menus = array())
	{
		require_once realpath(JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php');

		$items = MenusHelper::getMenuLinks();

		// Build the groups arrays.
		foreach ($items as $menu) {
			// Initialize the group.
			$menus[$menu->menutype] = [];

			// Build the options array.
			foreach ($menu->links as $link) {
				$menus[$menu->menutype][] = JHtml::_('select.option', $link->value, $link->text);
			}
		}

		$id = str_ireplace('.', '_', $name);

		$theme = ES::themes();
		$theme->set('id', $id);
		$theme->set('name', $name);
		$theme->set('menus', $menus);
		$theme->set('selected', $selected);
		$output = $theme->output('admin/html/form/menus');

		return $output;
	}

	/**
	 * Generates a on / off switch
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function toggler($name, $enabled = false, $id = '', $attributes = '')
	{
		if (is_array($attributes)) {
			$attributes = implode(' ', $attributes);
		}

		if (!$id) {
			$id = $name;
		}

		$theme = ES::themes();
		$theme->set('id', $id);
		$theme->set('name', $name);
		$theme->set('enabled', $enabled);
		$theme->set('attributes', $attributes);

		$output = $theme->output('site/helpers/form/toggler');

		return $output;
	}

	/**
	 * Generates a popdown selection
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function popdown($name, $selected = '', $options = array(), $direction = 'right', $attributes = array(), $buttonSize = 'small')
	{
		$selectedHtml = '';

		if (!$selected) {
			$selectedHtml = $options[0];

			if (is_object($selectedHtml)) {
				$selectedHtml = $selectedHtml->selected;
			}

		} else {

			foreach ($options as $option) {
				if ($option->value != $selected) {
					continue;
				}

				$selectedHtml = $option->selected;
			}
		}

		$attributes = implode(' ', $attributes);

		$btnSize = '';

		if ($buttonSize == 'small') {
			$btnSize = 'btn-sm';
		}

		$theme = ES::themes();
		$theme->set('btnSize', $btnSize);
		$theme->set('selected', $selected);
		$theme->set('selectedHtml', $selectedHtml);
		$theme->set('options', $options);
		$theme->set('name', $name);
		$theme->set('direction', $direction);
		$theme->set('attributes', $attributes);

		$output = $theme->output('site/helpers/form/popdown');

		return $output;
	}

	/**
	 * Generates a popdown option
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function popdownOption($value, $title, $description, $icon = '', $attributes = array(), $url = 'javascript:void(0);', $pageTitle = '', $options = [])
	{
		$title = JText::_($title);
		$description = JText::_($description);

		if ($attributes && is_array($attributes)) {
			$attributes = implode(' ', $attributes);
		}

		if (!$attributes) {
			$attributes = '';
		}

		if ($pageTitle) {
			$attributes .= 'custom-title="' . $pageTitle . '"';
		}

		// This option determines if the popbox dropdown should have the default padding in the dropdown of the popbox
		$increasedPadding = ES::normalize($options, 'increasedPadding', false);

		$theme = ES::themes();
		$theme->set('increasedPadding', $increasedPadding);
		$theme->set('value', $value);
		$theme->set('title', $title);
		$theme->set('description', $description);
		$theme->set('icon', $icon);
		$theme->set('selected', false);
		$theme->set('attributes', $attributes);
		$theme->set('url', $url);
		$theme->set('pageTitle', $pageTitle);

		$data = new stdClass();
		$data->html = $theme->output('site/helpers/form/popdown.option');
		$data->value = $value;
		$data->icon = $icon;

		// To get the selected html output
		$theme->set('selected', true);
		$data->selected = $theme->output('site/helpers/form/popdown.option');

		return $data;
	}

	/**
	 * Allows caller to select the actor for action on the site
	 * For now this only applicable for Page
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function postAs($values = array())
	{
		if (!$values) {
			return;
		}

		$session = JFactory::getSession();
		$viewAs = $session->get('easysocial.viewas', 'user', SOCIAL_SESSION_NAMESPACE);
		$viewAs = $this->input->get('viewas', $viewAs, 'string');

		$items = array();

		$items['page'] = ES::page($values['page']);
		$items['user'] = ES::user($values['user']);
		$actor = $items[$viewAs];

		$theme = ES::themes();

		$theme->set('default', 'page');
		$theme->set('items', $items);
		$theme->set('actor', $actor);
		$theme->set('clusterId', $values['page']);

		$output = $theme->output('site/helpers/form/post.as');

		return $output;
	}

	/**
	 * Renders a form title
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function title($text, $heading = 'h2')
	{
		$theme = ES::themes();

		$text = JText::_($text);
		$theme->set('heading', $heading);
		$theme->set('text', $text);
		$output = $theme->output('site/helpers/form/title');

		return $output;
	}

	/**
	 * Renders a workflow dropdown
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function workflows($name, $type = SOCIAL_TYPE_USER, $selected = '')
	{
		$model = ES::model('Workflows');
		$workflows = $model->getWorkflowByType($type);

		$id = str_ireplace('.', '_', $name);

		$theme = ES::themes();
		$theme->set('id', $id);
		$theme->set('name', $name);
		$theme->set('workflows', $workflows);
		$theme->set('selected', $selected);

		$output = $theme->output('admin/html/form/workflows');

		return $output;
	}

	/**
	 * Displays dropdown list for the Facebook scope permissions
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function scopes($name, $id = '', $selected = null, $attributes = array())
	{
		// If the id is empty, we'll re-use the name as the id.
		$id = !$id ? $name : $id;

		// Get the list of Facebook scope permission
		$scopes = array('email', 'publish_actions', 'user_birthday', 'user_location');
		$scopes = array_combine($scopes, $scopes);

		$theme = ES::themes();
		$theme->set('name', $name);
		$theme->set('scopes', $scopes);
		$theme->set('id', $id);
		$theme->set('selected', $selected);

		$output = $theme->output('admin/html/form/scopes');

		return $output;
	}

	/**
	 * Display the header apps arrangement form
	 *
	 * @since	3.0.0
	 * @access	public
	 */
	public function headerApps($name, $selected, $type = 'user', $attributes = array(), $id = null)
	{
		$appsModel = ES::model('Apps');

		if ($type == 'user') {
			$options = array('type' => SOCIAL_APPS_TYPE_APPS, 'installable' => true, 'group' => $type, 'state' => SOCIAL_STATE_PUBLISHED, 'view' => 'profile', 'includedefault' => true);
			$availableApps = $appsModel->getApps($options, true);
		} else {
			$method = 'get' . ucfirst($type) . 'Apps';
			$availableApps = $appsModel->$method(0, true, $id);
		}

		// These are core apps that is not included in model->getUserApps
		if ($type == 'user') {
			$coreApps = array('friends', 'photos', 'video', 'audio', 'followers', 'pages', 'events', 'groups', 'polls', 'marketplaces');
		} else {

			// The ordering start with members menu
			if ($type == 'event') {
				$memberApps = 'guests';
			} else if ($type == 'page') {
				$memberApps = 'followers';
			} else {
				$memberApps = 'members';
			}

			// Next append the next menu sequence for core apps
			$coreApps = array($memberApps, 'photos', 'video', 'audio');

			// Add events menu for page and group
			if ($type == 'page' || $type == 'group') {

				$clusterApps = array('events', 'marketplaces');
				foreach ($clusterApps as $cApp) {
					$app = ES::table('App');
					$app->load(array('type' => SOCIAL_APPS_TYPE_APPS, 'group' => $type, 'element' => $cApp));

					if ($app->id && $app->state == SOCIAL_STATE_PUBLISHED) {
						$coreApps[] = $cApp;
					}
				}
			}
		}

		$apps = array();

		foreach ($coreApps as $core) {
			$enabled = $this->config->get($core . '.enabled');

			if (!$enabled && !in_array($core, array('guests', 'followers', 'members'))) {
				continue;
			}

			// Re-map photos as albums
			if ($core == 'photos') {
				$core = 'albums';
			}

			// Fix the plural
			if ($core == 'video' || $core == 'audio') {
				$core = $core . 's';
			}

			$rawTitle = 'COM_ES_' . strtoupper($core);

			$obj = new stdClass();
			$obj->title = JText::_($rawTitle);
			$obj->rawTitle = $rawTitle;
			$obj->element = $core;
			$obj->isMore = false;

			$apps[$core] = $obj;
		}

		if ($availableApps) {
			foreach ($availableApps as $app) {
				$obj = new stdClass();
				$obj->title = $app->getAppTitle();
				$obj->rawTitle = $app->getAppTitle(true);
				$obj->element = $app->element;
				$obj->isMore = false;

				$apps[$app->element] = $obj;
			}
		}

		// Process the stored value
		if ($selected) {
			$selectedApp = json_decode($selected);
			$result = array();

			foreach ($selectedApp as $element) {
				if (isset($apps[$element])) {
					$result[] = $apps[$element];
					unset($apps[$element]);
				}

				if ($element == 'es-more-section') {
					$obj = new stdClass();
					$obj->isMore = true;

					$result[] = $obj;
				}
			}

			$apps = array_merge($result, $apps);
		} else {
			$total = count($apps);

			if ($total < 5) {
				$obj = new stdClass();
				$obj->isMore = true;

				$apps[] = $obj;
			}
		}

		$theme = ES::themes();
		$theme->set('apps', $apps);
		$theme->set('name', $name);
		$theme->set('selected', $selected);

		$output = $theme->output('admin/html/form/header.apps');

		return $output;
	}

	/**
	 * Renders a help block for a form
	 *
	 * @since	3.3.0
	 * @access	public
	 */
	public function help($text, $includeNotePrefix = true)
	{
		$text = JText::_($text);

		$theme = ES::themes();
		$theme->set('text', $text);
		$theme->set('includeNotePrefix', $includeNotePrefix);

		return $theme->output('site/helpers/form/help');
	}
}
