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

class SocialGdprFeed extends SocialGdprAbstract
{
	public $type = 'feed';
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

		$feeds = $this->getFeeds();

		if (!$feeds) {
			return $this->tab->finalize();
		}

		foreach ($feeds as $feed) {
			$item = $this->getTemplate($feed->idx, $this->type);
			$item->title = ESJString::ucfirst($feed->title);
			$item->intro = $this->getIntro($feed);
			$item->created = $feed->created;

			$this->tab->addItem($item);
		}
	}

	public function getFeeds()
	{
		$ids = $this->tab->getProcessedIds();

		$model = ES::model('rss');
		$data = $model->getFeedsGDPR($this->user->id, $ids, $this->getLimit());

		return $data;
	}

	public function getIntro($feed)
	{
		$date = ES::date($feed->created);

		ob_start();
		?>
		<div class="gdpr-item__desc">
			<a href="<?php echo $feed->url; ?>" target="_blank"><?php echo $feed->url; ?></a>
		</div>
		<div class="gdpr-item__meta">
			<?php echo $date->format($this->getDateFormat()); ?>
		</div>
		<?php

		$contents = ob_get_contents();
		ob_end_clean();

		return $contents;
	}
}
