<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined('_JEXEC') or die('Unauthorized Access');

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class SocialMarketplace extends EasySocial
{
	public $id = 0;
	public $title = null;
	public $description = null;
	public $price = null;
	public $stock = null;
	public $currency = null;
	public $condition = null;
	public $user_id = null;
	public $uid = null;
	public $type = null;
	public $created = null;
	public $state = null;
	public $isnew = null;
	public $featured = null;
	public $category_id = null;
	public $album_id = null;
	public $params = '';
	public $hits = null;
	public $longitude = null;
	public $latitude = null;
	public $address = null;
	public $post_as = null;

	public $table = null;
	public $fields = [];

	protected $error = '';

	static $instances = [];

	public function __construct($params = [])
	{
		$this->config = ES::config();
		$this->my = ES::user();

		// Create the user parameters object
		$this->_params = ES::registry();

		// Initialize user's property locally.
		$this->initParams($params);

		$this->table = ES::table('Marketplace');
		$this->table->bind($this);
	}

	/**
	 * Core function to initialise this class.
	 *
	 * @since   1.3
	 * @access  public
	 */
	public static function factory($ids = null, $reload = null)
	{
		$items = self::loadItems($ids, $reload);

		return $items;
	}

	/**
	 * Loads a given id or an array of id's.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public static function loadItems($ids = null, $reload = null, $debug = false)
	{
		if (is_object($ids)) {
			$obj = new self;
			$obj->bind($ids);

			self::$instances[$ids->id] = $obj;

			return self::$instances[$ids->id];
		}

		// Determine if the argument is an array.
		$argumentIsArray = is_array($ids);

		// Ensure that id's are always an array
		if (!is_array($ids)) {
			$ids = [$ids];
		}

		// Reset the index of ids so we don't load multiple times from the same user.
		$ids = array_values($ids);

		if (empty($ids)) {
			return false;
		}

		// Get the metadata of all items
		$model = ES::model('Marketplaces');
		$items = $model->getMeta($ids);

		if (!$items) {
			return false;
		}

		// Format the return data
		$result = [];

		foreach ($items as $item) {
			if ($item === false) {
				continue;
			}

			// Create an object
			$obj = new SocialMarketplace($item);

			self::$instances[$item->id] = $obj;

			$result[] = self::$instances[$item->id];
		}

		if (!$result) {
			return false;
		}

		if (!$argumentIsArray && count($result) == 1) {
			return $result[0];
		}

		return $result;
	}

	/**
	 * Initializes the provided properties into the existing object. Instead of
	 * trying to query to fetch more info about this item.
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function initParams(&$params)
	{
		// Get all properties of this object
		$properties = get_object_vars($this);

		// Bind parameters to the object
		foreach($properties as $key => $val) {
			if (isset($params->$key)) {
				$this->$key = $params->$key;
			}
		}

		// Bind params json object here
		$this->_params->loadString($this->params);
	}

	/**
	 * Magic method to access table's property
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function __get($property)
	{
		if (!property_exists($this, $property) && isset($this->table->$property)) {
			return $this->table->$property;
		}
	}

	/**
	 * Allow caller to bind data to the table
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function bind($data)
	{
		return $this->table->bind($data);
	}

	/**
	 * Retrieves the creation date of a item
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getCreatedDate()
	{
		$date = ES::date($this->table->created);

		return $date;
	}

	/**
	 * Retrieves the category of the item
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getCategory()
	{

		static $_cache = [];

		$idx = $this->table->category_id;

		if (!isset($_cache[$idx])) {
			$category = ES::table('MarketplaceCategory');
			$category->load($this->table->category_id);

			$_cache[$idx] = $category;
		}

		return $_cache[$idx];
	}

	/**
	 * Retrieves the title of the item
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getTitle()
	{
		return JText::_($this->table->title);
	}

	/**
	 * Retrieves the price of the item
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getPrice()
	{
		return $this->table->price;
	}

	/**
	 * Retrieves the stock of the item
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getStock()
	{
		return $this->table->stock;
	}

	/**
	 * Determine if the stock available
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function isStockAvailable()
	{
		return $this->table->stock > 0;
	}

	/**
	 * Retrieves the currency tied to this object
	 *
	 * @since	4.0.5
	 * @access	public
	 */
	public function getCurrency()
	{
		$currency = ES::currency($this->table->currency ? $this->table->currency : $this->config->get('marketplaces.currency'));

		return $currency;
	}

	/**
	 * Determine if stock details should be displayed
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function showStock()
	{
		return !is_null($this->stock);
	}

	/**
	 * Retrieves the price obj of the item
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getPriceCurrency()
	{
		$config = ES::config();

		$priceObj = new stdClass;
		$priceObj->price = $this->table->price;
		$priceObj->currency = $this->table->currency ? $this->table->currency : $config->get('marketplaces.currency');
		return $priceObj;
	}

	/**
	 * Retrieves the currency used
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getCurrencyUnit()
	{
		$currency = $this->getCurrency();

		return $currency->symbol;
	}

	/**
	 * Retrieves the price tag
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getPriceTag()
	{
		$price = $this->getPrice();

		if ($price == '0.00') {
			return JText::_('COM_ES_MARKETPLACE_LISTING_FREE');
		}

		$separator = $this->getCurrency()->separator;
		$price = number_format($price, 2, $separator, '');

		return $this->getCurrencyUnit() . ' ' . $price;
	}

	/**
	 * Retrieves the condition of the item
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getCondition()
	{
		return $this->table->condition;
	}

	/**
	 * Determines if this is being approved
	 *
	 * @since	4.0
	 * @access	public
	 */
	final public function isBeingApproved()
	{
		if ($this->state == SOCIAL_STATE_PUBLISHED && ($this->table->state == SOCIAL_MARKETPLACE_PENDING || $this->table->state == SOCIAL_MARKETPLACE_DRAFT)) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if this is being approved
	 *
	 * @since	4.0
	 * @access	public
	 */
	final public function isBeingApprovedForUpdate()
	{
		if ($this->state == SOCIAL_STATE_PUBLISHED && $this->table->state == SOCIAL_MARKETPLACE_UPDATE_PENDING) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the item is published.
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function isPublished()
	{
		return $this->state == SOCIAL_STATE_PUBLISHED;
	}

	/**
	 * Determines if the item is unpublished.
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function isUnpublished()
	{
		return $this->state == SOCIAL_STATE_UNPUBLISHED;
	}

	/**
	 * Determines if the item is sold.
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function isSold()
	{
		return $this->state == SOCIAL_MARKETPLACE_SOLD;
	}

	/**
	 * Determines if this is new or not.
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function isNew()
	{
		$isNew = $this->id ? $this->isnew : true;
		return $isNew;
	}

	/**
	 * Determines if the cluster is pending
	 *
	 * @since	3.2.16
	 * @access	public
	 */
	final public function isPending()
	{
		return $this->state == SOCIAL_MARKETPLACE_PENDING || $this->state == SOCIAL_MARKETPLACE_UPDATE_PENDING;
	}

	/**
	 * Determines if the listing is under draft status.
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function isDraft()
	{
		// listings that being rejected and require user to review their group content before we can re-submit for approval.
		return $this->state == SOCIAL_MARKETPLACE_DRAFT;
	}

	/**
	 * Retrieve the creator of this item.
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function getCreator()
	{
		$user = ES::user($this->user_id);

		return $user;
	}

	/**
	 * Preprocess before storing data into the table object.
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function save($options = [])
	{
		// Determine if this record is a new user by identifying the id.
		$isNew = $this->isNew();

		// Detect if the cluster is being approved for approvals / reject. (This needs to be detected before we bind the data)
		$isBeingApproved = $this->isBeingApproved();

		// Detect if the cluster is being approved after updating. (This needs to be detected before we bind the data)
		$isBeingApprovedForUpdate = $this->isBeingApprovedForUpdate();

		// Request parent to store data.
		$this->table->bind($this);

		// Fixed for 'Fields don't have default value' error in Joomla 4
		if (!$this->table->address) {
			$this->table->address = '';
		}

		// If this listing belongs to Page, we need to set the post_as to 'page'
		// So that the author will always be the Page itself
		if ($this->table->type == SOCIAL_TYPE_PAGE && is_null($this->table->post_as)) {

			$page = ES::page($this->table->uid);

			// Determine if the listing is posted by admin
			if ($page->isAdmin($this->table->user_id, false)) {
				$this->table->post_as = SOCIAL_TYPE_PAGE;
			} else {
				$this->table->post_as = SOCIAL_TYPE_USER;
			}
		}

		// Try to store the item
		$state = $this->table->store();

		if ($isNew) {
			$this->id = $this->table->id;
		}

		if ($state && $isNew && $this->isPublished()) {
			$this->notify();
		}

		// Assign points to the user for updating the cluster
		if (!$isNew && !$isBeingApproved && ($this->isPublished() || $isBeingApprovedForUpdate)) {
			$userId = $this->my->id;

			// If the update is being approved by the admin, we can only assign the points to the item creator
			// as we do not store the user id that is performing the updates
			if ($isBeingApprovedForUpdate) {
				$userId = $this->getCreator()->id;
			}
		}

		// This needs to happen after the table is saved, otherwise new items does not have the id
		if (($isNew && !$this->isPending()) || $isBeingApproved) {

			// Assign points to the creator when a item is created
			ES::points()->assign('marketplace.add', 'com_easysocial', $this->getCreator()->id);

			// Add this action into access logs
			ES::access()->log('marketplaces.limit', $this->getCreator()->id, $this->id, SOCIAL_TYPE_MARKETPLACE);
		}

		if ($this->isPublished()) {

			// set the isnew flag to false after the listing being published.
			if ($this->table->isnew) {
				$this->table->isnew = 0;
				$state = $this->table->store();
			}

			if (!isset($options['isFromStory']) && $state) {
				// trigger marketplace listing smart search plugin for indexing.
				$this->syncIndex();
			}
		}

		return $state;
	}

	/**
	 * Sync's the user record with Joomla smart search
	 *
	 * @since	4.0.8
	 * @access	public
	 */
	public function syncIndex()
	{
		// Determines if this is a new account
		$isNew = $this->isNew();

		// Trigger our own finder plugin
		JPluginHelper::importPlugin('finder');

		ESDispatcher::trigger('onFinderAfterSave', array('easysocial.marketplaces', &$this->table, $isNew));
		ESDispatcher::trigger('onFinderChangeState', array('easysocial.marketplaces', $this->table->id, $this->table->state));
	}

	/**
	 * Notify user when the marketplace is created
	 *
	 * @since	4.0.8
	 * @access	public
	 */
	public function notify($scheduled = false)
	{
		$allowed = array(SOCIAL_TYPE_GROUP, SOCIAL_TYPE_PAGE);

		// We only process notification for cluster item
		if (!in_array($this->type, $allowed)) {
			return false;
		}

		$cluster = ES::cluster($this->type, $this->uid);

		if (!$cluster->id) {
			return false;
		}

		$options = array();
		$options['id'] = $this->id;
		$options['userId'] = $this->user_id;
		$options['title'] = $this->title;
		$options['description'] = $this->getDescription();
		$options['permalink'] = $this->getPermalink();
		$options['price'] = $this->getPriceTag();

		if ($scheduled) {
			$options['scheduled'] = $scheduled;
		}

		$cluster->notifyMembers('marketplace.create', $options);

		return true;
	}

	/**
	 * Binds the item custom fields.
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function bindCustomFields($data)
	{
		// Get the registration model.
		$model = ES::model('Fields');

		// Get the field id's that this profile is allowed to store data on.
		$fields = $model->getStorableFields($this->getWorkflow()->id , SOCIAL_TYPE_MARKETPLACES);

		// If there's nothing to process, just ignore.
		if (!$fields) {
			return false;
		}

		$availableFields = [];

		// Let's go through all the storable fields and store them.
		foreach ($fields as $fieldId) {
			$availableFields[$fieldId] = $fieldId;

			$key = SOCIAL_FIELDS_PREFIX . $fieldId;

			if (!isset($data[$key])) {
				continue;
			}

			$value = isset($data[$key]) ? $data[$key] : '';

			// Test if field really exists to avoid any unwanted input
			$field = ES::table('Field');

			// If field doesn't exist, just skip this.
			if (!$field->load($fieldId)) {
				continue;
			}

			// Let the table object handle the data storing
			$field->saveData($value, $this->id, 'marketplace');
		}

		// Store conditional fields in params so it can be use in other places
		if (isset($data['conditionalRequired']) && $data['conditionalRequired']) {
			$table = ES::table('Marketplace');
			$table->load(['id' => $this->id]);

			$params = ES::registry($table->params);

			$conditionalFields = ES::registry($data['conditionalRequired']);
			$storedConditionalFields = ES::registry($params->get('conditionalFields'));

			$storedConditionalFields->mergeObjects($conditionalFields->getRegistry());

			// Remove any unused fields
			$conditionalFieldsArray = $storedConditionalFields->toArray();
			$obj = new stdClass();

			foreach ($conditionalFieldsArray as $key => $value) {
				if (isset($availableFields[$key])) {
					$obj->$key = $value;
				}
			}

			$newConditionalFields = ES::registry($obj);
			$params->set('conditionalFields', $newConditionalFields->toString());

			$table->params = $params->toString();
			$table->store();
		}
	}

	/**
	 * Method to retrieve the workflow for this item
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getWorkflow()
	{
		$category = $this->getCategory();
		return $category->getWorkflow();
	}

	/**
	 * Retrieves the permalink of the item
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getAlias()
	{
		return $this->table->getAlias();
	}

	/**
	 * Retrieves the cluster that is associated with the item
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getCluster()
	{
		$cluster = null;

		if ($this->uid && $this->type && $this->type != SOCIAL_TYPE_USER) {
			$cluster = ES::cluster($this->type, $this->uid);
		}

		return $cluster;
	}

	/**
	 * Determines if this listing belongs to a cluster
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function isClusterListing()
	{
		if ($this->type != SOCIAL_TYPE_USER) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if this listing belongs to a group
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function isGroupListing()
	{
		return $this->type == SOCIAL_TYPE_GROUP;
	}

	/**
	 * Determines if this listing belongs to a group
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function isPageListing()
	{
		return $this->type == SOCIAL_TYPE_PAGE;
	}

	/**
	 * Determines if the user is the page/group owner
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function isClusterOwner()
	{
		if ($this->isGroupListing() && !$this->getGroup()->isOwner()) {
			return false;
		}

		if ($this->isPageListing() && !$this->getPage()->isOwner()) {
			return false;
		}

		return true;
	}


	/**
	 * Returns the group that this listing belongs to if it is a group listing.
	 *
	 * @since  4.0
	 * @access public
	 */
	public function getGroup()
	{
		if (!$this->isGroupListing()) {
			return false;
		}

		return ES::group($this->uid);
	}

	/**
	 * Return a page that this listing belongs to
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function getPage()
	{
		if (!$this->isPageListing()) {
			return false;
		}

		return ES::page($this->uid);
	}

	/**
	 * Retrieves the seller of the item
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getAuthor()
	{
		$author = ES::user($this->table->user_id);

		return $author;
	}

	/**
	 * Retrieves the permalink of the item
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getPermalink($xhtml = true, $uid = null, $utype = null, $from = false, $external = false, $sef = true, $adminSef = false)
	{
		return $this->table->getPermalink($xhtml, $uid, $utype, $from, $external, $sef, $adminSef);
	}

	/**
	 * Retrieve the likes count for a listing
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getLikesCount($verb = '', $streamId = '')
	{
		$likes = $this->getLikes($verb, $streamId);

		return $likes->getCount();
	}

	/**
	 * Retrieves the likes library for this listing
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getLikes($verb = '', $streamId = '')
	{
		if (!$verb) {
			$verb = 'create';
		}

		$options = [];

		if ($this->type == SOCIAL_TYPE_PAGE) {
			$options['clusterId'] = $this->uid;
		}

		$likes = ES::likes();
		$likes->get($this->table->id, SOCIAL_TYPE_MARKETPLACES, $verb, $this->type, $streamId, $options);

		return $likes;
	}

	/**
	 * Retrieves the comment library for this listing
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getComments($verb = '', $streamId = '')
	{
		if (!$verb) {
			$verb = 'create';
		}

		$options = [];
		$options['clusterId'] = $this->uid;
		$options['url'] = $this->getPermalink(true, null, null, false, false, false);

		$privacy = ES::user()->getPrivacy();
		if (!$privacy->validate('story.post.comment', $this->table->user_id, SOCIAL_TYPE_USER)) {
			$options['hideForm'] = true;
		}

		// Generate comments for the listing
		$comments = ES::comments($this->table->id, SOCIAL_TYPE_MARKETPLACES, $verb, $this->type, $options, $streamId);

		return $comments;
	}

	/**
	 * Retrieves the comments count
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getCommentsCount($verb = '', $streamId = '')
	{
		if (!$verb) {
			$verb = 'create';
		}

		$comments = $this->getComments($verb, $streamId);

		return $comments->getCount();
	}

	/**
	 * Retrieves the view all listings link
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getAllListingsLink($filter = '', $xhtml = false)
	{
		$options = [];

		if ($filter) {
			$options['filter'] = $filter;
		}

		if ($this->uid && $this->type) {
			$options['uid'] = $this->isClusterListing() ? $this->getCluster()->getAlias() : $this->getAuthor()->getAlias();
			$options['type'] = $this->type;
		}

		$url = FRoute::marketplaces($options, $xhtml);

		return $url;
	}

	/**
	 * Retrieves the external permalink of the listing
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getExternalPermalink($format = null)
	{
		return $this->table->getExternalPermalink($format);
	}

	/**
	 * Retrieves the reports library for this listing
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getReports()
	{
		// Generate the reports
		$options = [
			'title' => 'COM_ES_MARKETPLACES_REPORTS_DIALOG_TITLE',
			'description' => 'COM_ES_MARKETPLACES_REPORTS_DIALOG_DESC',
			'extension' => 'com_easysocial',
			'type' => SOCIAL_TYPE_MARKETPLACE,
			'uid' => $this->table->id,
			'itemTitle' => $this->getTitle()
		];

		$reports = ES::reports($options);

		return $reports;
	}

	/**
	 * Retrieves the related stream id for a particular verb
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getStreamId($verb)
	{
		return $this->getListingStreamId($this->table->id, $verb);
	}

	/**
	 * Determines if the photo should be associated with the stream item
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getListingStreamId($listingId, $verb)
	{
		static $_cache = [];

		$db = ES::db();
		$sql = $db->sql();

		$idx = $listingId . '.' . $verb;

		if (!isset($_cache[$idx])) {

			$sql->select('#__social_stream_item', 'a');
			$sql->column('a.uid');
			$sql->where('a.context_type', SOCIAL_TYPE_MARKETPLACES);
			$sql->where('a.context_id', $listingId);

			if ($verb == 'upload') {
				$sql->where('a.verb', 'share');
				$sql->where('a.verb', 'upload', '=', 'OR');
			} else if($verb == 'add') {
				$sql->where('a.verb', 'create');
			} else {
				$sql->where('a.verb', $verb);
			}

			$db->setQuery($sql);
			$_cache[$idx] = (int) $db->loadResult();
		}

		$uid = $_cache[$idx];

		if (!$uid) {
			return;
		}

		return $uid;
	}

	/**
	 * Render repost link
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function reposts($clusterId = 0, $clusterType = '')
	{
		$repost = ES::repost($this->table->id, SOCIAL_TYPE_MARKETPLACES, $this->table->type, $clusterId, $clusterType);

		return $repost;
	}

	/**
	 * Retrieves the privacy library for this listing
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getPrivacyButton()
	{
		$privacy = $this->getPrivacy();

		$streamId = $this->getListingStreamId($this->table->id, 'create');

		$button = $privacy->form($this->table->id, SOCIAL_TYPE_MARKETPLACES, $this->table->uid, 'marketplaces.view', false, $streamId);

		return $button;
	}

	/**
	 * Retrieves the privacy library associated to this listing
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getPrivacy()
	{
		static $privacy = null;

		if (is_null($privacy)) {
			$privacy = ES::privacy($this->id, SOCIAL_TYPE_MARKETPLACES);
		}

		return $privacy;
	}

	/**
	 * Render listing headers
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function renderHeaders()
	{
		$obj = new stdClass();

		$title = ES::string()->escape($this->getTitle());

		$obj->title = $title;
		$obj->description = $this->description;
		$obj->image = $this->getSinglePhoto('large');
		$obj->url = $this->getExternalPermalink();
		$obj->listing = $this;

		ES::meta()->setMetaObj($obj);
	}

	/**
	 * Determines if the listing is in pending processing mode
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function isPendingProcess()
	{
		return $this->table->state == SOCIAL_MARKETPLACE_PENDING;
	}

	/**
	 * Retrieves the description of the listing
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getDescription($showDefault = true)
	{
		// Load site's language file.
		ES::language()->loadSite();

		$desc = ESJString::trim($this->table->description);

		if (!$desc && $showDefault) {
			return;
		}

		$isRestApi = ES::input()->get('rest', false, 'bool');

		if ($isRestApi) {
			return ES::string()->normalizeRestContent($desc);
		}

		// Only process this if the description doesn't have those HTML tag
		if (strpos($desc, '<p>') === false && strpos($desc, '<br />') === false && strpos($desc, '<br>') === false) {
			$desc = nl2br($desc);
		}

		return $desc;
	}

	/**
	 * Rejects the listing
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function reject($reason = '', $email = false, $delete = false)
	{
		$config = ES::config();
		$my = ES::user();

		// If we need to send email to the user, we need to process this here.
		if ($email) {
			// Push arguments to template variables so users can use these arguments
			$params = [
				'title' => $this->getTitle(),
				'name' => $this->getCreator()->getName(),
				'reason' => $reason,
				'manageAlerts' => false
			];

			// Load front end language file.
			ES::language()->loadSite();

			// Get the email title.
			$title = JText::_('COM_ES_EMAILS_MARKETPLACE_REJECTED_EMAIL_TITLE');

			// Immediately send out emails
			$mailer = ES::mailer();

			// Get the email template.
			$mailTemplate = $mailer->getTemplate();

			// Set recipient
			$mailTemplate->setRecipient($this->getCreator()->getName(), $this->getCreator()->email);

			// Set title
			$mailTemplate->setTitle($title);

			// Set the contents
			$mailTemplate->setTemplate('site/marketplace/rejected', $params);

			// Set the priority. We need it to be sent out immediately since this is user registrations.
			$mailTemplate->setPriority(SOCIAL_MAILER_PRIORITY_IMMEDIATE);

			// Try to send out email now.
			$mailer->create($mailTemplate);
		}

		// If required, delete the page from the site.
		if ($delete) {
			$this->delete();

			// remove the access log for this action
			ES::access()->removeLog('marketplaces.limit', $this->getCreator()->id, $this->id, $this->cluster_type);

			return true;
		}

		// we need to log the reason so that the author can review again the cluster details.
		$this->state = SOCIAL_MARKETPLACE_DRAFT;
		$state = $this->save();

		if ($state) {
			// lets add the reject reason. TODO
			// $rejectTbl = ES::table('MarketplaceReject');
			// $rejectTbl->message = ($reason) ? $reason : JText::_('COM_ES_MARKETPLACES_EMPTY_REJECT_REASON');
			// $rejectTbl->cluster_id = $this->id;
			// $rejectTbl->created_by = $my->id;
			// $rejectTbl->created = ES::date()->toSql();

			// $rejectTbl->store();
		}

		return true;
	}

	/**
	 * Approves a listing's moderation.
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function approve($email = true)
	{
		$isNew = $this->isNew();

		$previousState = $this->state;

		// Update the group's state first.
		$this->state = SOCIAL_STATE_PUBLISHED;

		$state = $this->save();

		$dispatcher = ES::dispatcher();

		// Set the arguments
		$args = [&$this];

		// @trigger onGroupAfterApproved
		$dispatcher->trigger(SOCIAL_TYPE_MARKETPLACE, 'onAfterApproved', $args);
		$dispatcher->trigger(SOCIAL_TYPE_USER, 'onMarketplaceAfterApproved', $args);

		// Activity logging.s
		// If we need to send email to the user, we need to process this here.
		if ($email) {
			ES::language()->loadSite();
			$adminSef = false;

			if (ES::isFromAdmin()) {
				$adminSef = true;
			}

			// Push arguments to template variables so users can use these arguments
			$params = [
				'title' => $this->getTitle(),
				'name' => $this->getCreator()->getName(),
				'avatar' => $this->getSinglePhoto()
			];

			$params['listingUrl'] = $this->getPermalink(false, $this->getCreator()->id, 'user', '', $adminSef);

			// Get the email title.
			$title = JText::sprintf('COM_ES_EMAILS_LISTING_APPLICATION_APPROVED', $this->getTitle());

			$namespace = 'site/marketplace/approved';

			if ($previousState == SOCIAL_MARKETPLACE_UPDATE_PENDING) {
				$title = JText::sprintf('COM_ES_EMAILS_LISTING_UPDATED_APPROVED', $this->getTitle());
				$namespace = 'site/marketplace/update.approved';
			}

			// Send out email immediately
			$mailer = ES::mailer();

			// Get the email template
			$mailTemplate = $mailer->getTemplate();

			// Set the recipient
			$mailTemplate->setRecipient($this->getCreator()->getName(), $this->getCreator()->email);

			// Set the email title.
			$mailTemplate->setTitle($title);

			// Set the email content
			$mailTemplate->setTemplate($namespace, $params);

			// Set the priority.
			$mailTemplate->setPriority(SOCIAL_MAILER_PRIORITY_IMMEDIATE);

			$mailer->create($mailTemplate);
		}

		if ($isNew) {
			$stream = ES::table('StreamItem');
			$options = ['context_type' => SOCIAL_TYPE_MARKETPLACES, 'context_id' => $this->id];
			$exists = $stream->load($options);

			if (!$exists) {
				$this->createStream($this->user_id, 'create');
			}
		}

		// The cluster is updated
		if (!$isNew) {
			$this->createStream($this->getCreator()->id, 'update');
		}

		return true;
	}

	/**
	 * Determine if the provided field should be visible on the site
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function isFieldVisible(SocialTableField $field)
	{
		// Check for conditional field
		if (!$field->isConditional()) {
			return true;
		}

		// Get user params
		$conditionalFields = $this->getParam('conditionalFields');

		if (!$conditionalFields) {
			return true;
		}

		$conditionalFields = json_decode($conditionalFields, true);

		if (isset($conditionalFields[$field->id]) && $conditionalFields[$field->id]) {
			return true;
		}

		return false;
	}

	/**
	 * Retrieve filter permalink for markteplace listing
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function getFilterPermalink($options = [])
	{
		if (isset($options['cluster']) && $options['cluster']) {
			$cluster = $options['cluster'];
			$options['type'] = $cluster->getType();
			$options['uid'] = $cluster->getAlias();
		}

		unset($options['cluster']);

		$filterLink = ESR::marketplaces($options);

		return $filterLink;
	}

	public function canViewItem($userId = null)
	{
		$user = ES::user($userId);

		if ($this->isClusterListing()) {
			$cluster = $this->getCluster();

			if (!$cluster->canViewMarketplace()) {
				return false;
			}

			return true;
		}

		if ($this->isPending()) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the user is allowed to sell item
	 *
	 * @since	4.0
	 */
	public function allowSelling($skipItemIds = [])
	{
		// We don't allow guest to sell item
		if (!$this->my->id) {
			return false;
		}

		$access = $this->my->getAccess();

		if (!$access->allowed('marketplaces.sell')) {
			return false;
		}

		if ($this->hasExceededLimit($skipItemIds)) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if the user exceeded the limit to sell items
	 *
	 * @since	4.0
	 */
	public function hasExceededLimit($skipItemIds = [])
	{
		$access = $this->my->getAccess();

		if ($access->exceeded('marketplaces.daily', $this->my->getTotalItemSell(true, true, $skipItemIds))) {
			return true;
		}

		if ($access->exceeded('marketplaces.limit', $this->my->getTotalItemSell(false, false, $skipItemIds))) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the user can unfeature the listing
	 *
	 * @since	4.0
	 */
	public function canUnfeature()
	{
		if ($this->table->isUnfeatured()) {
			return false;
		}

		if ($this->my->isSiteAdmin()) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the user can feature the listing
	 *
	 * @since	4.0
	 */
	public function canFeature()
	{
		// If this listing is featured already, it should never be possible to feature it again
		if ($this->table->isFeatured()) {
			return false;
		}

		if ($this->my->isSiteAdmin()) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if this cluster is featured.
	 *
	 * @since   1.2
	 * @access  public
	 */
	public function isFeatured()
	{
		return (bool) $this->table->featured;
	}

	/**
	 * Determines if the user can delete the listing
	 *
	 * @since	4.0
	 */
	public function canDelete()
	{
		// Allow users to delete their own listing
		if ($this->my->id == $this->table->user_id) {
			return true;
		}

		// Allow site admin to delete the listing
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the user can mark item as sold
	 *
	 * @since	4.0
	 */
	public function canMarkAsSold()
	{
		if ($this->isSold()) {
			return false;
		}

		// Allow users to modify their own listing
		if ($this->my->id == $this->table->user_id) {
			return true;
		}

		// Allow site admin to modify the listing
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the user can mark item as available
	 *
	 * @since	4.0
	 */
	public function canMarkAvailable()
	{
		if (!$this->isSold()) {
			return false;
		}

		// Allow users to modify their own listing
		if ($this->my->id == $this->table->user_id) {
			return true;
		}

		// Allow site admin to modify the listing
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		return false;
	}



	/**
	 * Determines if the user can edit the listing
	 *
	 * @since	4.0
	 */
	public function canEdit()
	{
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		if ($this->table->user_id == $this->my->id) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the current user can access the marketplace section.
	 *
	 * @since	1.4
	 */
	public function canAccessMarketplace()
	{
		if (!$this->config->get('marketplaces.enabled')) {
			return false;
		}

		if ($this->my->isSiteAdmin()) {
			return true;
		}

		$user = ES::user($this->uid);
		if ($this->my->canView($user, 'marketplaces.view')) {
			return true;
		}

		return false;
	}

	/**
	 * Retrieves the page title for listing
	 *
	 * @since	4.0
	 */
	public function getListingPageTitle()
	{
		$title = JText::_('COM_ES_PAGE_TITLE_MARKETPLACES_FILTER_ALL');

		if ($this->uid) {
			$user = ES::user($this->uid);

			// Set the title to the user's listing page title
			$title = JText::sprintf('COM_ES_MARKETPLACE_USER_PAGE_TITLE', $user->getName());
		}

		return $title;
	}

	/**
	 * Determine whether hits should be incremented.
	 *
	 * @since	4.0.0
	 */
	public function hit()
	{
		return $this->table->hit();
	}

	public function getPageTitle($layout, $prefix = true)
	{
		$user = ES::user($this->uid);

		// Set page title
		$title = JText::_('COM_ES_PAGE_TITLE_MARKETPLACE');

		if ($layout == 'form' && !$this->table->id) {
			$title = JText::_('COM_ES_PAGE_TITLE_SELL_ITEM');
		}

		if ($layout == 'form' && $this->table->id) {
			$title = JText::_('COM_ES_PAGE_TITLE_EDITING_ITEM');
		}

		if ($prefix && !$user->guest) {
			$title = $user->getName() . ' - ' . $title;
		}

		if ($layout == 'item') {
			$title .= ' - ' . $this->table->get('title');
		}

		return $title;
	}

	public function setBreadcrumbs($layout)
	{
		// Set the breadcrumbs
		$this->document->breadcrumb($this->getPageTitle($layout));
	}

	/**
	 * Retrieves the permalink to edit a listing
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getEditLink($xhtml = true)
	{
		$options = ['layout' => 'edit'];

		if ($this->table->id) {
			$options['id'] = $this->table->id;
		}

		if ($this->uid && $this->type) {
			$cluster = ES::cluster($this->type, $this->uid);
			$options['uid'] = $cluster->getAlias();
			$options['type'] = $this->type;
		}

		$url = ESR::marketplaces($options, $xhtml);

		return $url;
	}

	/**
	 * Create a stream for any user action in marketplace
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function createStream($actorId = null, $verb = '', $options = [])
	{
		$stream = ES::stream();
		$tpl = $stream->getTemplate();
		$actor = ES::user($actorId);

		// Set the actor
		$tpl->setActor($actor->id, SOCIAL_TYPE_USER);

		$postActor = isset($options['postActor']) ? $options['postActor'] : null;

		if ($postActor) {
			$tpl->setPostAs($postActor);
		}

		// Set the context
		$tpl->setContext($this->id, SOCIAL_TYPE_MARKETPLACES);

		// Set the verb
		$tpl->setVerb($verb);

		// If this is created within a cluster, it should be mapped to the respective cluster
		if ($this->uid && $this->type && $this->type != SOCIAL_TYPE_USER) {
			$cluster = $this->getCluster();
			$tpl->setCluster($this->uid, $this->type, $cluster->type);
		}

		// since this is a cluster and user stream, we need to call setPublicStream
		// so that this stream will display in unity cluster as well
		// This stream should be visible to the public
		$tpl->setAccess('core.view');

		$stream->add($tpl);
	}

	/**
	 * Determine if the user is owner of this listing
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function isOwner()
	{
		if ($this->user_id == $this->my->id) {
			return true;
		}

		return false;
	}

	/**
	 * Generates the mini header for the marketplace layout
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getMiniHeader()
	{
		if ($this->type != SOCIAL_TYPE_USER) {
			return ES::template()->html('cover.' . $this->type, $this->getCluster(), 'marketplaces');
		}

		$user = ES::user($this->uid);

		// we hide the miniheader if the user is banned. so that other users wont be able to navigate
		if ($user->isBanned()) {
			return;
		}

		$theme = ES::themes();
		$theme->set('user', $user);

		return ES::template()->html('cover.user', $user, 'marketplaces');
	}

	/**
	 * Retrieve default photo
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getDefaultPhoto($relative = false)
	{
		static $default = null;

		if (!$default) {

			$default = $this->config->get('covers.default.marketplace.default');

			if (!ES::isCli()) {

				$app = JFactory::getApplication();

				$path = '/templates/' . $app->getTemplate() . '/html/com_easysocial/covers/marketplace/default.jpg';

				if (JFile::exists(JPATH_ROOT . $path)) {
					$default = $path;

				} else {

					// this setting only added 3.x onwards so we need to check this as well
					$overridePathFromSetting = '/images/easysocial_override/marketplace/cover/default.jpg';

					if (JFile::exists(JPATH_ROOT . $overridePathFromSetting)) {
						$default = $overridePathFromSetting;
					}
				}
			}

		}

		return $relative ? $default : ES::getUrl($default);
	}

	/**
	 * Retrieve single photo
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getSinglePhoto($size = 'thumbnail')
	{
		$photos = $this->getPhotos();

		if (!$photos) {
			return $this->getDefaultPhoto();
		}

		return $photos[0][$size];
	}

	/**
	 * Generates the title of the photo
	 *
	 * @since	4.0.3
	 * @access	public
	 */
	public function getPhotoAlias()
	{
		$alias = $this->getAlias();

		$alias = explode(':', $alias);

		return $alias[1];
	}

	/**
	 * Retrieve photos
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getPhotos($photoData = false)
	{
		if (!$this->album_id) {
			return [];
		}

		$album = ES::table('Album');
		$album->load($this->album_id);

		$model = ES::model('photos');
		$photos = $model->getPhotos(['album_id' => $this->album_id, 'limit' => false]);

		if (!$photos) {
			return false;
		}

		if ($photoData) {
			return $photos;
		}

		$photosUrls = [];

		foreach ($photos as $photo) {
			$sizes = [];
			$sizes['large'] = $photo->getSource('large');
			$sizes['thumbnail'] = $photo->getSource('thumbnail');
			$photosUrls[] = $sizes;
		}

		return $photosUrls;
	}

	/**
	 * Determines if the user is allowed to access action in the dropdown menu
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function canAccessActionMenu($userId = null)
	{
		if ($this->my->isSiteAdmin()) {
			return true;
		}

		if (ES::reports()->canReport()) {
			return true;
		}

		return false;
	}


	/**
	 * Allows caller to set the listing as a featured item.
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function setFeatured()
	{
		$this->table->featured = true;

		$state = $this->table->store();

		if ($state) {
			$this->createStream(null, 'featured');

			// @points: listing.featured
			ES::points()->assign('marketplace.featured', 'com_easysocial', $this->getAuthor()->id);
		}

		return $state;
	}

	/**
	 * Allows caller to remove the listing from being a featured item.
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function removeFeatured()
	{
		$this->table->featured = false;

		$state = $this->table->store();

		if ($state) {
			// @points: listing.unfeatured
			ES::points()->assign('marketplace.unfeatured', 'com_easysocial', $this->getAuthor()->id);
		}

		return $state;
	}

	/**
	 * Determines if the user can publish the listing
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function canPublish($userId = null)
	{
		$user = ES::user($userId);

		// Only site admins are allowed to unpublish a listing
		if (($user->isSiteAdmin() || $this->isOwner()) && $this->state == SOCIAL_STATE_UNPUBLISHED) {
			return true;
		}

		return false;
	}

	/**
	 * Determines if the user can unpublish the listing
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function canUnpublish($userId = null)
	{
		$user = ES::user($userId);

		// Only site admins are allowed to unpublish a listing
		if (($user->isSiteAdmin() || $this->isOwner()) && $this->state == SOCIAL_STATE_PUBLISHED) {
			return true;
		}

		return false;
	}

	/**
	 * Allows caller to publish this listing.
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function publish()
	{
		$this->table->state = SOCIAL_STATE_PUBLISHED;

		$state = $this->table->store();

		if ($state) {
			$this->state = SOCIAL_STATE_PUBLISHED;

			$indexer = ES::get('Indexer');

			// need to update from the indexed item as well
			$indexer->itemStateChange('easysocial.marketplaces', $this->id, $this->state);
		}

		return $state;
	}

	/**
	 * Allows caller to publish this listing.
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function markAsSold()
	{
		$this->table->state = SOCIAL_MARKETPLACE_SOLD;

		$state = $this->table->store();

		if ($state) {
			$this->state = SOCIAL_MARKETPLACE_SOLD;

			$indexer = ES::get('Indexer');

			// need to update from the indexed item as well
			$indexer->itemStateChange('easysocial.marketplaces', $this->id, $this->state);
		}

		return $state;
	}

	/**
	 * Allows caller to unpublish this listing.
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function unpublish()
	{
		$this->table->state = SOCIAL_STATE_UNPUBLISHED;

		$state = $this->table->store();

		if ($state) {
			$this->state = SOCIAL_STATE_UNPUBLISHED;

			$indexer = ES::get('Indexer');

			// need to update from the indexed item as well
			$indexer->itemStateChange('easysocial.marketplaces', $this->id, $this->state);
		}

		return $state;
	}

	/**
	 * Logics for deleting a listing
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function delete()
	{
		// Remove the comments related to this listing
		$comments = $this->getComments();
		$comments->delete();

		// Remove the likes related to this listing
		$likes = ES::likes();
		$likes->delete($this->id, SOCIAL_TYPE_MARKETPLACES, 'create', $this->type, null, true);

		// Assign points when a listing is deleted
		ES::points()->assign('marketplace.remove', 'com_easysocial', $this->getAuthor()->id);

		// Remove the stream items related to this listing
		$this->removeStream('featured');
		$this->removeStream('create');

		// Remove the search results
		JPluginHelper::importPlugin('finder');
		ESDispatcher::trigger('onFinderAfterDelete', ['easysocial.marketplaces', $this->table]);

		$this->deletePhotoAlbums();

		// Remove from the database
		$state = $this->table->delete();

		return $state;
	}

	/**
	 * Removes a stream item given the verb
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function removeStream($verb, $actorId = '')
	{
		$stream = ES::stream();
		$result = $stream->delete($this->table->id, SOCIAL_TYPE_MARKETPLACES, $actorId, $verb);

		return $result;
	}

	/**
	 * Allows caller to remove all photos albums.
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function deletePhotoAlbums($pk = null)
	{
		$db = ES::db();
		$sql = $db->sql();

		// Delete cluster albums
		$sql->clear();
		$sql->select('#__social_albums');
		$sql->where('id', $this->album_id);
		$db->setQuery($sql);

		$albums = $db->loadObjectList();

		if ($albums) {
			foreach ($albums as $row) {
				$album = ES::table('Album');
				$album->load($row->id);

				$album->delete();
			}
		}

		return true;
	}

	/**
	 * Determines if the listing has a photo
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function hasPhotos()
	{
		if (!$this->album_id) {
			return false;
		}

		$album = ES::table('Album');
		$album->load($this->album_id);

		if (!$album->id) {
			return false;
		}

		$model = ES::model('photos');
		$photos = $model->getPhotos(['album_id' => $this->album_id]);

		return $photos;
	}

	/**
	 * Converts a marketplace object into an array that can be exported
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function toExportData(SocialUser $viewer, $extended = false)
	{
		static $cache = [];

		$key = $this->id . $viewer->id . (int) $extended;

		if (isset($cache[$key])) {
			return $cache[$key];
		}

		$creator = $this->getAuthor();
		$originalCreator = $creator;

		if ($this->isClusterListing() && $this->table->post_as === 'page') {
			$creator = ES::cluster(SOCIAL_TYPE_PAGE, $this->table->uid);
		}

		$result = [
			'id' => $this->id,
			'title' => $this->getTitle(),
			'description' => $this->getDescription(),
			'price' => $this->getPrice(),
			'priceTag' => $this->getPriceTag(),
			'availability' => $this->getStock(),
			'condition' => $this->getCondition(),
			'currency' => $this->table->currency,
			'category' => $this->getCategory()->toExportData($viewer, $extended),
			'type' => $this->type,
			'permalink' => $this->getPermalink(false, null, null, false, true),
			'avatar' => $this->getSinglePhoto(),
			'steps' => [],
			'isOwner' => $this->isOwner($viewer->id),
			'editPermalink' => $this->getEditLink(),
			'address' => $this->address,
			'latitude' => $this->latitude,
			'longitude' => $this->longitude,
			'addressLink' => $this->getAddressLink(),
			'isSold' => $this->isSold()
		];

		$location = [
			'address' => $this->address,
			'latitude' => $this->latitude,
			'longitude' => $this->longitude,
			'state' => '',
			'city' => '',
			'country' => '',
			'zip' => ''
		];

		$result['location'] = $location;

		$photos = $this->getPhotos(true);
		$photosObj = [];

		foreach ($photos as $photo) {
			$photoObj = [];
			$photoObj['id'] = $photo->id;
			$photoObj['url'] = $photo->getSource('large');
			$photosObj[] = $photoObj;
		}

		$result['photos'] = $photosObj;
		$result['isFeatured'] = $this->isFeatured();

		// Construct permission access
		$permission = [
			'isOwner' => $this->isOwner(),
			'canFeature' => $this->canFeature(),
			'canUnpublish' => $this->canUnpublish()
		];

		$result['permission'] = $permission;

		// extended data. mostly used in single cluster page.
		if ($extended) {
			$result['author'] = $creator->toExportData($viewer);
			$result['originalAuthor'] = $originalCreator->toExportData($viewer);

			$streamId = $this->getStreamId('create');

			// Retrieve the comments library
			$comments = $this->getComments('create', $streamId);

			// Retrieve the likes library
			$likes = $this->getLikes('create', $streamId);

			$result['likes'] = $likes;
			$result['comments'] = $comments;
		}

		$params = $this->getParams();
		$result['params'] = $params->toObject();

		$result = (object) $result;

		if ($extended) {
			ES::language()->loadAdmin();

			$stepsModel = ES::model('Steps');
			$steps = $stepsModel->getSteps($this->getWorkflow()->id, SOCIAL_TYPE_MARKETPLACES, SOCIAL_EVENT_VIEW_DISPLAY);

			$library = ES::fields();
			$args = [&$this];

			foreach ($steps as &$step) {
				$stepData = new stdClass();
				$stepData->id = $step->id;
				$stepData->title = JText::_($step->title);
				$stepData->description = JText::_($step->description);

				$fieldsModel = ES::model('Fields');
				$fieldOptions = ['step_id' => $step->id, 'data' => true, 'dataId' => $this->id, 'dataType' => SOCIAL_TYPE_MARKETPLACE, 'visible' => SOCIAL_EVENT_VIEW_DISPLAY];
				$fields = $fieldsModel->getCustomFields($fieldOptions);

				$library->trigger('onGetValue', SOCIAL_FIELDS_GROUP_MARKETPLACE, $fields, $args);
				$validFields = [];

				foreach ($fields as $field) {
					$value = $field->value;

					if (empty($value)) {
						continue;
					}

					$data = new stdClass();
					$data->id = $field->id;
					$data->type = $field->element;
					$data->name = JText::_($field->title);
					$data->value = (string) $value;
					$data->rawValue = $value;
					$data->params = $field->getParams()->toObject();

					$validFields[] = $data;
				}

				$stepData->fields = $validFields;
				$step = $stepData;
			}

			$result->steps = $steps;
		}

		$cache[$key] = $result;

		return $cache[$key];
	}

	/**
	 * Retrieves the params for a listing.
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function getParams()
	{
		$params = ES::registry($this->params);

		return $params;
	}

	/**
	 * Retrieve single param key value
	 *
	 * @since  4.0
	 * @access public
	 */
	public function getParam($key)
	{
		return $this->getParams()->get($key);
	}

	/**
	 * Retrieves the hits for the listing
	 *
	 * @since	4.0.0
	 * @access	public
	 */
	public function getHits()
	{
		return $this->table->hits;
	}

	public function createAlbum()
	{
		// Check if the album exists for this listing
		$album = ES::table('Album');
		$album->load([
			'uid' => $this->id,
			'type' => SOCIAL_TYPE_MARKETPLACE
		]);

		if ($album->id) {
			return $album;
		}

		$album->uid = $this->id;
		$album->title = JText::sprintf('COM_ES_ALBUMS_MARKETPLACE_PHOTOS', $this->getTitle());
		$album->caption = 'COM_ES_ALBUMS_MARKETPLACE_PHOTOS_DESC';
		$album->user_id = $this->my->id;
		$album->type = SOCIAL_TYPE_MARKETPLACE;
		$album->core = 0;
		$album->assigned_date = ES::normalizeDateString('');

		$album->store();

		return $album;
	}

	/**
	 * Migrate temporary photos from the photos table
	 *
	 * @since	4.0.3
	 * @access	public
	 */
	public function savePhotos($photos)
	{
		$album = $this->createAlbum();

		$photosModel = ES::model('photos');

		// We need to calculate the total size used in each photo (including all the variants)
		$totalSize = 0;

		foreach ($photos as $photoPath) {
			$tmpPath = SOCIAL_MEDIA . '/' . $photoPath;
			$image = ES::image();
			$image->load($tmpPath);

			if (!$image->isValid()) {
				$this->setError(JText::_('PLG_FIELDS_AVATAR_ERROR_INVALID_FILE'));
				return false;
			}

			$fileSize = filesize($tmpPath);

			$photo = ES::table('Photo');
			$photo->uid = $this->id;
			$photo->type = SOCIAL_TYPE_MARKETPLACE;
			$photo->user_id = $this->my->id;
			$photo->album_id = $album->id;

			$photo->title = $photo->generateTitle();
			$photo->cleanupTitle();
			$photo->ordering = 0;
			$photo->state = SOCIAL_STATE_PUBLISHED;
			$photo->post_as = SOCIAL_TYPE_USER;

			// Set the creation date alias
			$photo->assigned_date = ES::date()->toMySQL();

			// Try to store the photo.
			$state = $photo->store();

			if (!$state) {
				throw ES::exception('COM_EASYSOCIAL_PHOTOS_UPLOAD_ERROR_STORING_DB');
			}

			// Update the ordering of the photos in the album
			$photosModel->pushPhotosOrdering($album->id, $photo->id);

			$storage = ES::call('Photos', 'getStoragePath', [$album->id, $photo->id]);

			$storageContainer = ES::cleanPath(ES::config()->get('photos.storage.container'));

			// Get the photos library
			$photoLib = ES::photos($image);

			$photoAlias = $this->getPhotoAlias() . '-' . $photosModel->getNextOrdering($album->id);
			$paths = $photoLib->create($storage, [], $photoAlias);

			// Remove the storage container from the storage path as we only want to store the relative storage path
			$relativeStoragePath = str_replace('/' . $storageContainer . '/', '/', $storage);

			// Create metadata about the photos
			if ($paths) {
				$optimizer = ES::imageoptimizer();

				foreach ($paths as $type => $fileName) {

					$meta = ES::table('PhotoMeta');
					$meta->photo_id = $photo->id;
					$meta->group = SOCIAL_PHOTOS_META_PATH;
					$meta->property = $type;
					$meta->value = $relativeStoragePath . '/' . $fileName;
					$meta->store();

					// Optimize the image
					$absolutePath = JPATH_ROOT . $storage . '/' . $fileName;
					$optimizer->optimize($absolutePath, $meta->id, SOCIAL_TYPE_PHOTO);

					// We need to store the photos dimension here
					list($width, $height, $imageType, $attr) = getimagesize(JPATH_ROOT . $storage . '/' . $fileName);

					// Set the photo dimensions
					$meta = ES::table('PhotoMeta');
					$meta->photo_id = $photo->id;
					$meta->group = SOCIAL_PHOTOS_META_WIDTH;
					$meta->property = $type;
					$meta->value = $width;
					$meta->store();

					// Set the photo height
					$meta = ES::table('PhotoMeta');
					$meta->photo_id = $photo->id;
					$meta->group = SOCIAL_PHOTOS_META_HEIGHT;
					$meta->property = $type;
					$meta->value = $height;
					$meta->store();
				}
			}

			// Set the total photo size
			$photo->total_size = $fileSize;
			$photo->store();
		}

		$this->album_id = $album->id;
		$this->save();

		ES::storage()->syncUsage($this->my->id);

		$tmpPath = dirname($tmpPath);
		JFolder::delete($tmpPath);
	}

	/**
	 * Normalize the method available in this object so other users know what node is this
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function getType()
	{
		return SOCIAL_TYPE_MARKETPLACE;
	}

	/**
	 * Allows caller to switch owners.
	 *
	 * @since   4.0.0
	 * @access  public
	 */
	public function switchOwner($newUserId)
	{
		$this->user_id = $newUserId;

		if ($this->type == SOCIAL_TYPE_USER) {
			$this->uid = $newUserId;
		}

		$this->table->bind($this);
		$this->table->store();
	}

	/**
	 * Returns a maps link based on the address
	 *
	 * @since  1.3
	 * @access public
	 */
	public function getAddressLink()
	{
		if (!empty($this->address)) {
			if ($this->config->get('location.provider') == 'osm') {
				return 'https://www.openstreetmap.org/search?query=' . urlencode($this->address);
			}

			return 'https://maps.google.com/?q=' . urlencode($this->address);
		}

		return 'javascript:void(0);';
	}

	/**
	 * Determines if the listing contains a location
	 *
	 * @since	4.0
	 * @access	public
	 */
	public function hasLocation()
	{
		if (!empty($this->address)) {
			return true;
		}

		return false;
	}

	/**
	 * Retrieves the post actor for listing
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function getListingCreator($obj)
	{
		if ($this->post_as == SOCIAL_TYPE_PAGE && !is_null($obj)) {
			return $obj;
		}

		return $this->getAuthor();
	}

	/**
	 * Retrieves the custom field value from this listing.
	 *
	 * @since   4.0
	 * @access  public
	 */
	public function getFieldValue($key)
	{
		static $processed = [];

		if (!isset($processed[$this->id])) {
			$processed[$this->id] = [];
		}

		if (!isset($processed[$this->id][$key])) {
			if (!isset($this->fields[$key])) {
				$result = ES::model('Fields')->getCustomFields(['group' => SOCIAL_TYPE_MARKETPLACE, 'workflow_id' => $this->getWorkflow()->id, 'data' => true , 'dataId' => $this->id , 'dataType' => SOCIAL_TYPE_MARKETPLACE, 'key' => $key]);

				$this->fields[$key] = isset($result[0]) ? $result[0] : false;
			}

			$field = $this->fields[$key];

			// Initialize a default property
			$processed[$this->id][$key] = '';

			if ($field) {
				// Trigger the getFieldValue to obtain data from the field.
				$value = ES::fields()->getValue($field, $this->cluster_type);

				$processed[$this->id][$key] = $value;
			}
		}

		return $processed[$this->id][$key];
	}
}
