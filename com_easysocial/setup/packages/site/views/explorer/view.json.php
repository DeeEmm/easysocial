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

ES::import( 'site:/views/views' );

class EasySocialViewExplorer extends EasySocialSiteView
{

    /**
     * Responsible to return data for file explorer
     *
     * @since   1.3
     * @access  public
     * @param   string
     * @return
     */
    public function hook($exception = false, $result = array())
    {
        if ($exception->type != SOCIAL_MSG_SUCCESS) {
            return $this->ajax->reject($exception);
        }

        // Get the hook that's used
        $hook = $this->input->get('hook', '', 'cmd');

        if ($hook == 'removeFolder') {
            $id = $this->input->get('id', 0, 'int');

            return $this->ajax->resolve($id, $result);
        }

        echo json_encode($result);
        exit;
    }

	/**
	 * Displays the delete folder confirmation
	 *
	 * @since	1.2
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function confirmDeleteFolder()
	{
		$ajax 	= ES::ajax();

		$id = $this->input->get('id', 0, 'int');

		$folder = ES::table( 'FileCollection' );
		$folder->load( $id );

		$theme 	= ES::themes();
		$theme->set( 'folder' , $folder );
		$contents 	= $theme->output( 'site/explorer/dialog.delete.folder' );

		return $ajax->resolve( $contents );
	}

	/**
	 * Displays the delete file confirmation
	 *
	 * @since	1.2
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function confirmDeleteFile()
	{
		$ajax 	= ES::ajax();

		$id = $this->input->get('id', 0, 'int');
		$file 	= ES::table( 'File' );
		$file->load( $id );

		$theme 	= ES::themes();
		$theme->set( 'file' , $file );
		$contents 	= $theme->output( 'site/explorer/dialog.delete.file' );

		return $ajax->resolve( $contents );
	}

	/**
	 * Renders the file browser
	 *
	 * @since	1.2
	 * @access	public
	 * @param	string
	 * @return
	 */
	public function browser()
	{
		$ajax 	= ES::ajax();

		$uid  	= $this->input->get('uid', 0, 'int');
		$type 	= $this->input->get('type', '', 'cmd');
		$url  	= $this->input->get('url', '', 'default');

		// Load up the explorer library
		$explorer	= ES::explorer( $uid, $type );

		// We need to determine if the user is allowed to access
		if( !$explorer->hook( 'hasReadAccess' ) )
		{
			return $ajax->reject();
		}

		$allowUpload	= $explorer->hook( 'allowUpload' );
		$maxSize 		= $explorer->hook( 'getMaxSize' );
		$html 			= $explorer->render( $url , array( 'allowUpload' => $allowUpload , 'uploadLimit' => $maxSize ) );

		return $ajax->resolve( $html );
	}
}
