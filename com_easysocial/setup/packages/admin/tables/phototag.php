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

ES::import('admin:/tables/table');

class SocialTablePhotoTag extends SocialTable
{
	/**
	 * The unique id for this record.
	 * @var int
	 */
	public $id = null;

	/**
	 * The unique type id for this record.
	 * @var int
	 */
	public $photo_id = null;

	/**
	 * The unique type id for this record.
	 * @var int
	 */
	public $uid = null;

	/**
	 * The unique type string for this record.
	 * @var string
	 */
	public $type = null;

	/**
	 * Optional label for this tag
	 * @var string
	 */
	public $label = null;

	/**
	 * The unique type id for this record.
	 * @var int
	 */
	public $top = null;

	/**
	 * The unique type id for this record.
	 * @var int
	 */
	public $left = null;

	/**
	 * The unique type id for this record.
	 * @var int
	 */
	public $width = null;

	/**
	 * The unique type id for this record.
	 * @var int
	 */
	public $height = null;

	/**
	 * The unique type id for this record.
	 * @var int
	 */
	public $created_by = null;

	/**
	 * The unique type id for this record.
	 * @var int
	 */
	public $created = null;

	/**
	 * Class Constructor
	 *
	 * @since   1.0
	 * @param   JDatabase
	 */
	public function __construct($db)
	{
		parent::__construct('#__social_photos_tag', 'id', $db);
	}

	/**
	 * Override parent's store behavior
	 *
	 * @since   1.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function store($updateNulls = false)
	{
		$isNew = $this->id ? false : true;

		$state = parent::store();

		return $state;
	}

	/**
	 * Retrieves the label
	 *
	 * @since   1.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function getLabel()
	{
		if (empty($this->uid) && $this->type == 'label') {
			return JText::_($this->label);
		}

		if (!empty($this->uid) && $this->type == 'person') {
			$user   = ES::user($this->uid);

			return $user->getName();
		}
	}

	public function getPosition()
	{
		$top = (float) $this->top;
		$left = (float) $this->left;
		$width = (float) $this->width;
		$height = (float) $this->height;

		// Convert comma to decimal point. #5117
		$top = str_replace(',', '.', $top);
		$left = str_replace(',', '.', $left);
		$width = str_replace(',', '.', $width);
		$height = str_replace(',', '.', $height);

		$position = $top    . "," .
					$left   . "," .
					$width  . "," .
					$height;

		return $position;
	}

	public function getCSSPosition()
	{
		$top = (float) $this->top * 100;
		$left = (float) $this->left * 100;
		$width = (float) $this->width * 100;
		$height = (float) $this->height * 100;

		// Convert comma to decimal point. #5117
		$top = str_replace(',', '.', $top);
		$left = str_replace(',', '.', $left);
		$width = str_replace(',', '.', $width);
		$height = str_replace(',', '.', $height);

		$position = "top: "    . $top . "%;" .
					"left: "   . $left . "%;" .
					"width: "  . $width . "%;" .
					"height: " . $height . "%;";

		return $position;
	}

	/**
	 * Override parent's delete behavior
	 *
	 * @since   1.0
	 * @access  public
	 * @param   string
	 * @return
	 */
	public function delete($pk = null)
	{
		$state = parent::delete();

		// @points: photos.untag
		// Deduct points from the author for untagging an item
		$points = ES::points();
		$points->assign('photos.untag' , 'com_easysocial' , $this->created_by);

		return $state;
	}

	/**
	 * Determines if the user is allowed to remove the tag.
	 *
	 * @return boolean  True if user is allowed to remove tag.
	 */
	public function deleteable($id = null)
	{
		$user = ES::user($id);

		// Allow site admin to remove this tag
		if ($user->isSiteAdmin()) {
			return true;
		}

		// Allow the creator to remove this tag
		if ($this->created_by == $user->id) {
			return true;
		}

		// Allow user who is being tagged to remove themselves
		if ($this->uid && $this->uid == $user->id) {
			return true;
		}

		return false;
	}
}

