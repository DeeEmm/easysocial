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

require_once __DIR__ . '/comment.php';

class SocialGdprGroup extends SocialGdprAbstract
{
	public $type = 'group';
	public $tab = null;

	/**
	 * Main function to process user comment data for GDPR download.
	 *
	 * @since 2.1
	 * @access public
	 */
	public function execute(SocialGdprSection &$section)
	{
		$this->tab = $section->createTab($this);
		$groups = $this->getGroups();

		if (!$groups) {
			return $this->tab->finalize();
		}

		foreach ($groups as $group) {
			$item = $this->getTemplate($group->id, $this->type);

			$item->view = true;
			$item->title = $group->getTitle();
			$item->created = $group->created;
			$item->intro = $this->getIntro($group);
			$item->content = $this->getContent($group);

			$this->tab->addItem($item);
		}
	}

	/**
	 * Display the intro content on the first page 
	 *
	 * @since  2.2
	 * @access public
	 */
	public function getIntro($group)
	{
		$date = ES::date($group->created);
		ob_start();
		?>
		<div class="gdpr-item__meta">
			<?php echo $date->format($this->getDateFormat());?>
		</div>
		<?php
		$contents = ob_get_contents();
		ob_end_clean();

		return $contents;		
	}

	/**
	 * Display the content on the sub page 
	 *
	 * @since  2.2
	 * @access public
	 */
	public function getContent($group)
	{
		$group = ES::cluster($group->cluster_type, $group->id);
		$date = ES::date($group->created);

		ob_start();
		?>
		<div class="gdpr-item__desc">
			<?php echo $group->getDescription();?>
		</div>
		<div class="gdpr-item__meta">
			<?php echo $date->format($this->getDateFormat());?>
		</div>
		<?php
		$contents = ob_get_contents();
		ob_end_clean();

		return $contents;
	}

	public function getGroups()
	{
		$ids = $this->tab->getProcessedIds();

		$model = ES::model('groups');
		$data = $model->getGroupsGDPR($this->user->id, $ids);

		return $data;
	}
}