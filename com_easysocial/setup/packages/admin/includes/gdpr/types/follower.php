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

class SocialGdprFollower extends SocialGdprAbstract
{
	public $type = 'follower';

	public function execute(SocialGdprSection &$section)
	{
		$this->tab = $section->createTab($this);

		$items = $this->getItems();

		if (!$items) {
			$this->tab->finalize();
			return;
		}

		foreach ($items as $follower) {
			$item = $this->getTemplate($follower->id, $this->type);
			
			$item->title = $follower->user->getName();
			$item->created = $follower->created;
			$item->intro = $this->getIntro($follower);

			$this->tab->addItem($item);
		}
	}

	public function getIntro($follower)
	{
		$date = ES::date($follower->created);

		ob_start();
		?>
		<div class="gdpr-item__meta">
			<?php echo $date->format($this->getDateFormat()); ?>
		</div>
		<?php

		$contents = ob_get_contents();
		ob_end_clean();

		return $contents;
	}

	public function getItems()
	{
		$ids = $this->tab->getProcessedIds();

		$model = ES::model('subscriptions');
		$options = array();
		$options['limit'] = $this->getLimit();
		$options['exclusion'] = $ids;

		$data = $model->getFollowerGDPR($this->user->id, $options);
		$following = array();

		if (!$data) {
			return $data;
		}

		foreach ($data as $row) {
			$user = ES::user($row->uid);
			$row->user = $user;

			$following[] = $row;
		}
		return $data;
	}
}
