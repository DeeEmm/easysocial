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

class SocialReports extends EasySocial
{
	public $extension = '';
	public $type = '';
	public $uid = '';

	// The reported item's title
	public $itemTitle = '';

	// The title?
	public $title = '';
	public $text = '';
	public $description = '';
	public $url = '';
	public $icon = false;
	public $actorid = 0;
	public $options = [];

	public function __construct($options = [])
	{
		parent::__construct();

		$this->options = $this->normalizeOptions($options);
	}

	public function normalizeOptions($options)
	{
		if ($options) {
			foreach ($options as $key => $value) {
				$this->$key = JText::_($value);
			}
		}

		// Set a default text if API wasn't provided with a custom text.
		if (!isset($options['text'])) {
			$this->text = JText::_('COM_EASYSOCIAL_REPORTS_REPORT_ITEM');
		}

		// Normalize url
		if (!isset($options['url'])) {
			$this->url = ES::getURI(true);
		}

		// Normalize title
		if (!isset($options['title'])) {
			$this->title = JText::_($this->text);
		}

		// Normalize the author user id
		// set default id as 0 if do not have any author user id pass to here
		if (!isset($options['actorid'])) {
			$this->actorid = 0;
		}
	}

	/**
	 * Factory method to create a new report instance
	 *
	 * @since	1.4
	 * @access	public
	 * @param	string
	 * @return
	 */
	public static function factory($options = array())
	{
		return new self($options);
	}

	/**
	 * Generates the html form for reports
	 *
	 * @since	1.4
	 * @access	public
	 */
	public function html()
	{
		// When guests reporting is disabled, we shouldn't show the form at all
		if (!$this->config->get('reports.guests') && $this->my->guest) {
			return;
		}

		// @access: reports.submit
		// Check if user is allowed to create reports
		if (!$this->access->allowed('reports.submit')) {
			return;
		}

		// Load up the reports model
		$model = ES::model('Reports');

		$options = array('created_by' => $this->my->id);
		$usage = $model->getCount($options);

		// Check if the current user exceeded the reports limit
		if ($this->access->exceeded('reports.limit', $usage)) {
			return;
		}

		// Load up the themes
		$theme = ES::themes();

		$theme->set('url', $this->url);
		$theme->set('extension', $this->extension);
		$theme->set('type', $this->type);
		$theme->set('uid', $this->uid);
		$theme->set('itemTitle', $this->itemTitle);
		$theme->set('title', $this->title);
		$theme->set('text', $this->text);
		$theme->set('description', $this->description);
		$theme->set('icon', $this->icon);
		$theme->set('actorid', $this->actorid);

		$contents = $theme->output('site/reports/default.link');

		return $contents;
	}

	/**
	 * Determines if the current viewer can use the report
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function canReport()
	{
		if (!$this->config->get('reports.enabled')) {
			return false;
		}

		if (!$this->my->id && !$this->config->get('reports.guests')) {
			return false;
		}

		// Check if the access is allowed
		$access = $this->my->getAccess();

		if (!$access->allowed('reports.submit')) {
			return false;
		}

		// Ensure that the user does not exceed the reported count
		$usage = $this->getTotalReportsCreated($this->my->id);

		// Check if the current user exceeded the reports limit
		if ($access->exceeded('reports.limit', $usage)) {
			return false;
		}

		return true;
	}

	/**
	 * Generates a link for report submission
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function form($type, $uid, $options = [], $extension = 'com_easysocial')
	{
		if (!$this->canReport()) {
			return;
		}

		// Normalize the dialog options
		$dialogTitle = $this->normalize($options, 'dialogTitle', JText::_('COM_EASYSOCIAL_REPORTS_REPORT_ITEM'));
		$dialogContent = $this->normalize($options, 'dialogContent', '');

		// Normalize the object
		$objectTitle = $this->normalize($options, 'title', '');
		$objectPermalink = $this->normalize($options, 'permalink', ES::getURI(true));

		// Normalize form type
		$linkType = $this->normalize($options, 'type', 'button');

		// Get the text of the link (in case caller wants to display the text)
		$linkText = $this->normalize($options, 'text', JText::_('COM_EASYSOCIAL_REPORTS_REPORT_ITEM'));
		$showIcon = $this->normalize($options, 'showIcon', true);
		$actorid = $this->normalize($options, 'actorid', 0);

		$theme = ES::themes();

		$theme->set('objectTitle', $objectTitle);
		$theme->set('objectPermalink', $objectPermalink);
		$theme->set('extension', $extension);

		// Dialog options
		$theme->set('dialogTitle', $dialogTitle);
		$theme->set('dialogContent', $dialogContent);

		// Report properties
		$theme->set('type', $type);
		$theme->set('uid', $uid);
		$theme->set('extension', $extension);
		$theme->set('linkText', $linkText);
		$theme->set('showIcon', $showIcon);
		$theme->set('actorid', $actorid);

		$namespace = 'site/reports/' . $linkType;
		$contents = $theme->output($namespace);

		return $contents;
	}

	/**
	 * Get the total number of reports made by user
	 *
	 * @since	2.0
	 * @access	public
	 */
	public function getTotalReportsCreated($userId = null)
	{
		static $items = array();

		$user = ES::user($userId);

		if (!isset($items[$user->id])) {
			$model = ES::model('Reports');
			$items[$user->id] = $model->getCount(array('created_by' => $user->id));
		}

		return $items[$user->id];
	}

	/**
	 * Deprecated, use @form instead
	 *
	 * @deprecated	2.0
	 */
	public function getForm($extension , $type , $uid , $itemTitle , $text , $title = '' , $description = '' ,  $url = '', $icon = false, $actorid = 0)
	{
		// Determine if the user is a guest
		$my = ES::user();
		$config = ES::config();

		if (!$my->id) {
			if (!$config->get('reports.guests' , false)) {
				return;
			}
		} else {
			// Check if user is allowed to report.
			$access = ES::access();

			// @access: reports.submit
			// Check if user is allowed to create reports
			if (!$access->allowed('reports.submit')) {
				return;
			}

			$model = ES::model('Reports');
			$usage = $model->getCount(array('created_by' => $my->id));

			// Check if the current user exceeded the reports limit
			if ($access->exceeded('reports.limit' , $usage)) {
				return;
			}
		}

		$theme = ES::themes();

		// Set a default text if API wasn't provided with a custom text.
		if (empty($text)) {
			$text = JText::_('COM_EASYSOCIAL_REPORTS_REPORT_ITEM');
		}

		// If url is not provided, use the current URL.
		if (empty($url)) {
			$url = ES::getURI(true);
		}

		// If title is not supplied, we use the text
		if (empty($title)) {
			$title 	= $text;
		}

		$theme->set('url' , $url);
		$theme->set('extension' , $extension);
		$theme->set('itemTitle' , $itemTitle);
		$theme->set('title' , $title);
		$theme->set('text' , $text);
		$theme->set('type' , $type);
		$theme->set('uid' , $uid);
		$theme->set('description' , $description);
		$theme->set('icon', $icon);
		$theme->set('actorid', $actorid);

		$contents = $theme->output('site/reports/default.link');

		return $contents;
	}
}
