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

abstract class SocialExplorerHooks extends EasySocial
{
	private $storagePath = null;

	public function __construct($uid, $type)
	{
		parent::__construct();

		// Build the path to the storage path
		$container = $this->config->get('files.storage.container');
		$this->storagePath = JPATH_ROOT . $container . '/' . $this->config->get('files.storage.' . strtolower($type) . '.container');

		// Set the current uid and type
		$this->uid = $uid;
		$this->type = $type;
	}

	/**
	 * Retrieves a list of folders
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getFolders()
	{
		$model = ES::model('FileCollections');
		$folders = $model->getCollections($this->uid, $this->type);

		$defaultFolders = $this->getDefaultFolder();

		if (!is_array($folders)) {
			$folders = array($folders);
		}

		// Merge the data
		$folders = array_merge(array($defaultFolders), $folders);

		// Format the result
		$result = $this->format($folders);

		return $result;
	}

	/**
	 * Retrieves the default folder
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getDefaultFolder()
	{
		$obj = ES::table('FileCollection');
		$obj->id = 0;
		$obj->title = JText::_('COM_EASYSOCIAL_EXPLORER_DEFAULT_FOLDER');
		$obj->uid = $this->uid;
		$obj->type = $this->type;

		return $obj;
	}

	/**
	 * Creates a new collection
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function addFolder($title = null)
	{
		// Determines if the user has access to create folders
		if (!$this->hasWriteAccess()) {
			return ES::response(JText::_('COM_EASYSOCIAL_EXPLORER_NO_ACCESS_TO_CREATE_FOLDER'));
		}

		$title = is_null($title) ? $this->input->get('name', '', 'string') : $title;

		if (!$title) {
			return ES::response(JText::_('COM_EASYSOCIAL_EXPLORER_INVALID_FOLDER_NAME_PROVIDED'));
		}

		$collection = ES::table('FileCollection');
		$collection->title = $title;
		$collection->owner_id = $this->uid;
		$collection->owner_type = $this->type;
		$collection->user_id = ES::user()->id;
		$collection->desc = '';
		$collection->store();

		$result = $this->format(array($collection));

		return $result[0];
	}

	/**
	 * Retrieves a list of files from a specific collection.
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function getFiles($collectionId = null)
	{
		$model = ES::model('Files');
		$options = array();

		$options['collection_id'] = $this->input->get('id', 0, 'int');

		if (!is_null($collectionId)) {
			$options['collection_id'] = $collectionId;
		}

		$files = $model->getFiles($this->uid, $this->type, $options);

		// Format the result
		$result = $this->format($files);

		return $result;
	}

	/**
	 * Allows caller to upload files
	 *
	 * @since	1.2
	 * @access	public
	 */
	public function addFile($title = null)
	{
		if (!$this->hasWriteAccess()) {
			return ES::response(JText::_('COM_EASYSOCIAL_EXPLORER_NO_ACCESS_TO_UPLOAD'));
		}

		// Ensure that the storage path really exists on the site
		ES::makeFolder($this->storagePath);

		// Get the maximum size allowed from the child
		$max = $this->getMaxSize();

		// Define uploader options
		$options = array('name' => 'file', 'maxsize' => $max);

		// Get uploaded file from $_FILE
		$file = ES::uploader($options)->getFile();

		// If there was an error getting uploaded file, stop.
		if ($file instanceof SocialResponse) {
			return $file;
		}

		// Get filename
		$name = $file['name'];

		// Get the folder to store this item to.
		$collectionId = $this->input->get('id', 0, 'int');

		$table = ES::table( 'File' );
		$table->name = $name;
		$table->collection_id = $collectionId;
		$table->hits = 0;
		$table->hash = md5( 'tmp' );
		$table->uid = $this->uid;
		$table->type = $this->type;
		$table->created = JFactory::getDate()->toSql();
		$table->user_id = ES::user()->id;
		$table->size = filesize( $file[ 'tmp_name' ] );
		$table->mime  = $file[ 'type' ];
		$table->state = SOCIAL_STATE_PUBLISHED;
		$table->storage = SOCIAL_STORAGE_JOOMLA;

		// Try to store the data on the database.
		$table->store();

		// Now we need to really upload the file.
		$state = $table->storeWithFile($file);

		// Format the data now
		$result	= $this->format(array($table));

		return $result[0];
	}

	/**
	 * Formats the coollection data
	 *
	 * @since	2.0
	 * @access	public
	 */
	protected function format($data)
	{
		// Ensure that it's an array
		$data = ES::makeArray($data);

		if (!$data) {
			return array();
		}

		$result = array();

		foreach ($data as $item) {

			if ($item instanceof SocialTableFileCollection) {
				$result[] = $this->formatFolder($item);
			}

			if ($item instanceof SocialTableFile) {
				$result[] = $this->formatFile($item);
			}
		}

		return $result;
	}

	private function getIcon( SocialTableFile $row )
	{
		$mime = $row->mime;

		// TODO: Expand this?
		if (strpos($mime, 'image')===0) {
			return 'fa fa-image';
		}

		return 'fa fa-file';
	}

	/**
	 * Formats the output to be returned to the caller
	 *
	 * @since	2.0
	 * @access	public
	 */
	private function formatFile(SocialTableFile $row)
	{
		$file = new stdClass();
		$file->id = $row->id;
		$file->name = $row->name;
		$file->folder = $row->collection_id;
		$file->canDelete = $this->hasDeleteAccess($row);
		$file->data = (object) array(
			'hits' => $row->hits,
			'hash' => $row->hash,
			'uid' => $row->uid,
			'type' => $row->type,
			'created' => $row->created,
			'user_id' => $row->user_id,
			'size' => $row->size,
			'mime' => $row->mime,
			'state' => $row->state,
			'storage' => $row->storage,
			'icon' => $this->getIcon($row),
			'previewUri' => $row->getPreviewURI()
		);
		$file->settings	= array();

		$theme = ES::themes();
		$theme->set('file', $file);
		$file->html = $theme->output('site/explorer/file');

		// Uploaded preview
		$theme = ES::themes();
		$theme->set('file', $file);
		$file->preview = $theme->output('site/explorer/uploader/preview');
		return $file;
	}

	private function formatFolder(SocialTableFileCollection $folder)
	{
		// Get a list of files from a specific collection
		$files = $this->getFiles($folder->id);

		$collection = new stdClass();
		$collection->id = $folder->id;
		$collection->name = $folder->title;
		$collection->count = $folder->getTotalFiles();
		$collection->data = $files;
		$collection->settings = array();
		$collection->map = array();
		$collection->deleteable = $this->hasDeleteFolderAccess($folder);

		if ($files) {
			foreach ($files as $file) {
				$collection->map[] = $file->id;
			}
		}

		return $collection;
	}
}
