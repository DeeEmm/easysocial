<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2018 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

jimport('joomla.filesystem.file');

class SocialAssets extends EasySocial
{
	private $headers = array();

	public static function factory()
	{
		return new self();
	}

	public function addHeader($key, $value=null)
	{
		$header	= "/*<![CDATA[*/ " . (isset($value)) ? "$key" : "var $key = '$value';" . "/*]]>*/ ";

		$document = JFactory::getDocument();
		$document->addScriptDeclaration( $header );

		return $this;
	}

	/**
	 * Attaches any assets type on the header object
	 *
	 * @since	2.1.0
	 * @access	public
	 */
	public function attach($name, $type = 'images', $location = 'media')
	{
		static $medias = array();

		// Already loaded, we don't want to load it again.
		if (isset($medias[$location][$name])) {
			return $medias[$location][$name];
		}

		$path = $this->uri($location, $type . '/' . $name);

		$document = JFactory::getDocument();

		if ($type == 'styles') {
			$document->addStyleSheet($path);
		}

		if ($type == 'scripts') {
			$document->addScript($path);
		}

		$medias[$location][$name]	= true;

		return $medias[$location][$name];
	}

	public function locations($uri=false)
	{
		static $locations = array();

		$type = ($uri) ? 'uri' : 'path';

		if (isset($locations[$type])) {

			return $locations[$type];
		}

		$config = ES::config();
		$URI = ($uri) ? '_URI' : '';
		$DS  = '/';

		$locations[$type] = array(
			'site'				=> constant("SOCIAL_SITE_THEMES" . $URI) . $DS . strtolower($config->get('theme.site')),
			'site_base'			=> constant("SOCIAL_SITE_THEMES" . $URI) . $DS . strtolower($config->get('theme.site_base')),
			'site_override'     => constant("SOCIAL_JOOMLA_SITE_TEMPLATES" . $URI) . $DS . self::getJoomlaTemplate('site') . $DS . "html" . $DS . SOCIAL_COMPONENT_NAME,
			'admin'				=> constant("SOCIAL_ADMIN_THEMES" . $URI) . $DS . strtolower($config->get('theme.admin')),
			'admin_base'		=> constant("SOCIAL_ADMIN_THEMES" . $URI) . $DS . strtolower($config->get('theme.admin_base')),
			'admin_override'    => constant("SOCIAL_JOOMLA_ADMIN_TEMPLATES" . $URI) . $DS . self::getJoomlaTemplate('admin') . $DS . "html" . $DS . SOCIAL_COMPONENT_NAME,
			'module'            => constant("SOCIAL_JOOMLA_MODULES" . $URI),
			'module_override'   => constant("SOCIAL_JOOMLA_SITE_TEMPLATES" . $URI) . $DS . self::getJoomlaTemplate('site') . $DS . "html",
			'media'				=> constant("SOCIAL_MEDIA" . $URI),
			'foundry'			=> constant("SOCIAL_FOUNDRY" . $URI),
			'root'			    => constant("SOCIAL_JOOMLA" . $URI)
		);

		return $locations[$type];
	}

	public function path($location, $type='')
	{
		$locations = $this->locations();

		if (isset($locations[$location])) {
			$path = $locations[$location];
		} else {
			$path = '';
		}

		if ($type!=='') {
			$path .= '/' . $type;
		}

		return $path;
	}

	public function uri($location, $type='')
	{
		$locations = $this->locations(true);

		if (isset($locations[$location])) {
			$path = $locations[$location];
		} else {
			$path = '';
		}

		if ($type!=='') {
			$path .= '/' . $type;
		}

		return $path;
	}

	public function fileUri($location, $type='')
	{
		return "file://" . $this->path($location, $type);
	}

	public function relative($dest, $root='', $dir_sep='/')
	{
		$root = explode($dir_sep, $root);
		$dest = explode($dir_sep, $dest);
		$path = '.';
		$fix = '';

		$diff = 0;
		for ($i = -1; ++$i < max(($rC = count($root)), ($dC = count($dest)));)
		{
			if(isset($root[$i]) and isset($dest[$i]))
			{
				if($diff)
				{
					$path .= $dir_sep. '..';
					$fix .= $dir_sep. $dest[$i];
					continue;
				}

				if($root[$i] != $dest[$i])
				{
					$diff = 1;
					$path .= $dir_sep. '..';
					$fix .= $dir_sep. $dest[$i];
					continue;
				}
			}
			elseif(!isset($root[$i]) and isset($dest[$i]))
			{
				for($j = $i-1; ++$j < $dC;)
				{
					$fix .= $dir_sep. $dest[$j];
				}
				break;
			}
			elseif(isset($root[$i]) and !isset($dest[$i]))
			{
				for($j = $i-1; ++$j < $rC;)
				{
					$fix = $dir_sep. '..'. $fix;
				}
				break;
			}
		}

		return $path . $fix;
	}

	public function relativeUri($dest, $root)
	{
		$dest = new JURI($dest);
		$dest = $dest->getPath();

		$root = new JURI($root);
		$root = $root->getPath();

		return $this->relative($dest, $root);
	}

	/**
	 * Convert path to URI
	 *
	 * Convert /var/public_html/components/theme/simplistic/styles/blabla.less
	 * to http://mysite.com/components/theme/simplistic/styles/blabla.less
	 *
	 * @param	string	$path
	 *
	 * @return	string	Full path URI
	 */
	public function toUri( $path )
	{
		jimport('joomla.filesystem.path');
		$path = JPath::clean($path);

		if( strpos($path, SOCIAL_JOOMLA) === 0 )
		{
			$result = substr_replace($path, '', 0, strlen(SOCIAL_JOOMLA));
			$result = str_ireplace(DIRECTORY_SEPARATOR, '/', $result);
			$result = ltrim( $result, '/');
		}
		else
		{
			$parts = explode(DIRECTORY_SEPARATOR, $path);
			foreach ($parts as $i => $part) {
				if( $part == 'components' ) {
					break;
				}
				unset($parts[$i]);
			}

			$result = implode('/', $parts);
		}

		$result = SOCIAL_JOOMLA_URI . '/' . $result;
		return $result;
	}

	/**
	 * Set/reset current Joomla template.
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function setTemplate($template)
	{
		$app = JFactory::getApplication();

		$app->setTemplate($template);

		return self::getJoomlaTemplate('site', true);
	}

	/**
	 * Retrieves the current joomla template being used
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public static function getJoomlaTemplate($client = 'site', $reset = false)
	{
		static $template = array();

		if (!array_key_exists($client, $template) || $reset) {

			$app = JFactory::getApplication();

			// Try to load the template from joomla cache since some 3rd party plugins can change the templates on the fly. #449
			if ($client == 'site' && !ES::isFromAdmin()) {
				$template[$client] = $app->getTemplate();
			} else {

				$clientId = ($client == 'site') ? 0 : 1;

				$db = ES::db();

				$query	= 'SELECT template FROM `#__template_styles` AS s'
						. ' LEFT JOIN `#__extensions` AS e ON e.type = `template` AND e.element=s.template AND e.client_id=s.client_id'
						. ' WHERE s.client_id = ' . $db->quote($clientId) . ' AND home = 1';

				$db->setQuery($query);

				$result = $db->loadResult();

				// Fallback template
				if (!$result) {
					$result = ($client == 'site') ? 'beez_20' : 'bluestork';
				}

				$template[$client] = $result;
			}
		}

		return $template[$client];
	}
}
