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
defined('_JEXEC') or die('Unauthorized Access');

ES::import('admin:/includes/maintenance/dependencies');

class SocialMaintenanceScriptSetCommentsBoundary extends SocialMaintenanceScript
{
	public static $title = 'Set comments boundary';

	public static $description = 'Initialise the comments boundary (lft rgt) value properly';

	public function main()
	{
		$this->db = ES::db();

		$result = $this->getCommentsByParent(0);

		$key = null;

		$node = 1;

		foreach ($result as $row)
		{
			$newkey = $row->element . $row->uid;

			if ($key !== $newkey)
			{
				$key = $newkey;

				$node = 1;
			}

			$row->lft = $node++;
			$row->rgt = $node++;
			$row->depth = 0;

			$this->save($row);
		}

		foreach ($result as $row)
		{
			$this->setChilds($row);
		}

		return true;
	}

	private function setChilds($row)
	{
		$childs = $this->getCommentsByParent($row->id);

		if (!empty($childs))
		{
			$total = count($childs);

			$this->setParentChildCount($row->id, $total);

			$node = $row->lft;

			$length = $total * 2;

			$this->pushBoundary($row->element, $row->uid, $node, $length);

			$depth = $row->depth + 1;

			foreach ($childs as $child)
			{
				$child->lft = ++$node;
				$child->rgt = ++$node;
				$child->depth = $depth;

				$this->save($child);
			}

			foreach ($childs as $child)
			{
				$this->setChilds($child);
			}
		}
	}

	private function getCommentsByParent($parentid)
	{
		$sql = $this->db->sql();

		$sql->select('#__social_comments')
			->column('id')
			->column('element')
			->column('uid')
			->where('parent', $parentid)
			->order('element')
			->order('uid')
			->order('created')
			->order('id');

		$this->db->setQuery($sql);

		return $this->db->loadObjectList();
	}

	private function pushBoundary($element, $uid, $node, $length)
	{
		$query = "UPDATE `#__social_comments` SET `lft` = `lft` + {$length} WHERE `element` = '{$element}' AND `uid` = '${uid}' AND `lft` > {$node}";

		$this->query($query);

		$query = "UPDATE `#__social_comments` SET `rgt` = `rgt` + {$length} WHERE `element` = '{$element}' AND `uid` = '${uid}' AND `rgt` > {$node}";

		$this->query($query);
	}

	private function setParentChildCount($id, $count)
	{
		$query = "UPDATE `#__social_comments` SET `child` = {$count} WHERE `id` = {$id}";
		$this->query($query);
	}

	private function save($row)
	{
		$query = "UPDATE `#__social_comments` SET `lft` = {$row->lft}, `rgt` = {$row->rgt}, `depth` = {$row->depth} WHERE `id` = {$row->id}";
		$this->query($query);
	}

	private function query($query)
	{
		$sql = $this->db->sql();
		$sql->raw($query);
		$this->db->setQuery($sql);
		$this->db->query();
	}
}
