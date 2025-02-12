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

require_once( SOCIAL_LIB . '/template/template.php' );

class SocialScript extends SocialTemplate
{
	public $extension = 'js';

	public $scriptTag = false;
	public $openingTag = '<script>';
	public $closingTag = '</script>';

	public $CDATA = false;
	public $safeExecution = false;

	public $header = '';
	public $footer = '';

	public static function factory()
	{
		return new self();
	}

	/**
	 * Attaches files to the header.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string	The path to the javascript file
	 * @return
	 */
	public function attach( $path = null )
	{
		// Import joomla's filesystem library.
		jimport( 'joomla.filesystem.file' );

		// Keep original file value
		if( !is_null($path) )
		{
			$_file		= $this->file;
			$this->file = ES::resolve($path . '.' . $this->extension);

			if (!$this->file) {
				$this->file = ES::resolve($path);
			}
		}

		// Keep current value
		$_scriptTag = $this->scriptTag;
		$_CDATA     = $this->CDATA;

		// Reset to false
		$this->scriptTag	= false;
		$this->CDATA		= false;

		$output		= $this->parse();

		ES::document()->addInlineScript($output);

		// Restore current value
		$this->scriptTag = $_scriptTag;
		$this->CDATA     = $_CDATA;

		// Restore original file value
		if (!is_null($path))
		{
			$this->file = $_file;
		}
	}

	/**
	 * Parses a script file.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function parse($vars = null)
	{
		// Pass to the parent to process the theme file
		$vars = parent::parse($vars);
		$script	= $this->header . $vars . $this->footer;

		// Do not reveal root folder path.
		$file = str_ireplace(SOCIAL_JOOMLA, '', $this->file);

		// Replace \ with / to avoid javascript syntax errors.
		$file = str_ireplace('\\', '/', $file);

		$cdata = $this->CDATA;
		$scriptTag = $this->scriptTag;
		$safeExecution = $this->safeExecution;

ob_start();
include(SOCIAL_MEDIA . '/scripts/template.php');
$contents = ob_get_contents();
ob_end_clean();

		return $contents;
	}

	/**
	 * Allows inclusion of scripts within another script
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function output( $file = null , $vars = null )
	{
		$template 	= $this->getTemplate( $file );

		// Ensure that the script file exists
		if( !JFile::exists( $template->script ) )
		{
			return;
		}

		$this->file 	= $template->script;

		$output 		= $this->parse();

		return $output;
	}
}
