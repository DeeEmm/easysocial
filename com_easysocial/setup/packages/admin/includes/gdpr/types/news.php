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

class SocialGdprNews extends SocialGdprAbstract
{
	public $type = 'news';
	private $tab = null;

	/**
	 * Process video downloads in accordance to GDPR rules
	 *
	 * @since	2.1.11
	 * @access	public
	 */
	public function execute(SocialGdprSection &$section)
	{
		$this->tab = $section->createTab($this);

		$news = $this->getNews();

		// Nothing else to process, finalize it now.
		if (!$news) {
			$this->tab->finalize();

			return;
		}

		foreach ($news as $newsItem) {
			$item = $this->getTemplate($newsItem->id, $this->type);

			$item->created = $newsItem->created;

			$item->title = $newsItem->title;
			$item->intro = $this->getIntro($newsItem);
			$item->view = true;
			$item->content = $this->getContent($newsItem);

			$this->tab->addItem($item);
		}
	}

	public function getIntro($news)
	{
		$date = ES::date($news->created);
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

	/*
	 * Method to get the content for the sub page
	 *
	 */
	public function getContent($news)
	{
		$date = ES::date($news->created);
		ob_start();
		?>
		<div class="gdpr-item__desc">
			<?php echo $news->getContent(); ?>
		</div>
		<div class="gdpr-item__meta">
			<?php echo $date->format($this->getDateFormat());?>
		</div>
		<?php
		$contents = ob_get_contents();
		ob_end_clean();

		return $contents;
	}

	/**
	 * Retrieves videos that needs to be processed
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function getNews()
	{
		$ids = $this->tab->getProcessedIds();

		$options = array();
		$options['exclude'] = $ids;
		$options['limit'] = $this->getLimit();

		// Get a list of videos that needs to be exported / downloaded
		$model = ES::model('ClusterNews');
		$items = $model->getNewsGDPR($this->user->id, $options);

		return $items;
	}
}
