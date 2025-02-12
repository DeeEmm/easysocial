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

class SocialGdprPage extends SocialGdprAbstract
{
	public $type = 'page';

	/**
	 * Process user page downloads in accordance to GDPR rules
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function execute(SocialGdprSection &$section)
	{
		$this->tab = $section->createTab($this);

		$pages = $this->getPages();

		// Nothing else to process, finalize it now.
		if (!$pages) {
			$this->tab->finalize();
			return;
		}

		foreach ($pages as $page) {

			$item = $this->getTemplate($page->id, $this->type);

			$item->created = $page->created;
			$item->title = $page->getTitle();
			$item->intro = $this->getIntro($page);
			$item->view = true;
			$item->content = $this->getContent($page);

			$this->tab->addItem($item);
		}
	}

	/**
	 * Display the intro content on the first page
	 *
	 * @since  2.2
	 * @access public
	 */
	public function getIntro($page)
	{
		$date = ES::date($page->created);
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
	public function getContent($page)
	{
		$page = ES::cluster($page->cluster_type, $page->id);
		$date = ES::date($page->created);

		ob_start();
		?>
		<div class="gdpr-item__desc">
			<?php echo $page->getDescription();?>
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
	 * Retrieves user pages that needs to be processed
	 *
	 * @since	2.2
	 * @access	public
	 */
	public function getPages()
	{
		$ids = $this->tab->getProcessedIds();

		$options = array();
		$options['userid'] = $this->user->id;
		$options['exclusion'] = $ids;
		$options['limit'] = $this->getLimit();

		$model = ES::model('pages');
		$results = $model->getPagesGDPR($options);

		$pages = array();

		if ($results) {
			foreach ($results as $item) {
				$page = ES::page($item);
				$pages[] = $page;
			}
		}

		return $pages;
	}
}
