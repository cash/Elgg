<?php

/**
 * Access Collection API
 *
 *
 * @since 1.9
 */
class ElggAccessCollection {

	protected $id;
	protected $name;
	protected $owner_guid;
	protected $site_guid;
	protected $members = null;
	protected $loaded = false;

	/**
	 * Initialize an access collection that exists in the database
	 *
	 * @param type $id The ID of the access collection
	 */
	public function __construct($id) {
		$this->id = $id;
	}

	protected function loadInfo() {
		global $CONFIG;
		$id = sanitize_int($this->id);
		$query = "SELECT * FROM {$CONFIG->dbprefix}access_collections WHERE id = $id";
		$collection = get_data_row($query);
		if ($collection) {
			foreach ($collection as $key => $value) {
				$this->$key = $value;
			}
			$loaded = true;
		}
	}

	protected function loadMembers() {
		global $CONFIG;
		$id = sanitize_int($this->id);
		$query = "SELECT user_guid FROM {$CONFIG->dbprefix}access_collection_membership"
				. " WHERE access_collection_id = $id";
		$collection_members = get_data($query);
		$this->members = array();
		if ($collection_members) {
			foreach ($collection_members as $member) {
				$this->members[] = $member->user_guid;
			}
		}
	}

	/**
	 * Create an access collection in the database
	 *
	 * @param string $name        Name of the access collection
	 * @param int    $owner_guid  The GUID of the owner (default is logged in user)
	 * @param int    $site_guid   The GUID of the site (default is current site)
	 * @return bool
	 */
	public static function create($name, $owner_guid = 0, $site_guid = 0) {
		global $CONFIG;

		$name = trim($name);
		if (empty($name)) {
			return false;
		}
		$name = sanitize_string($name);

		if ($owner_guid == 0) {
			$owner_guid = elgg_get_logged_in_user_guid();
		}
		$owner_guid = sanitize_int($owner_guid);
		if ($site_guid == 0) {
			$site_guid = elgg_get_site_entity()->getGUID();
		}
		$site_guid = sanitize_int($site_guid);

		$q = "INSERT INTO {$CONFIG->dbprefix}access_collections
			SET name = '$name', owner_guid = $owner_guid, site_guid = $site_guid";
		$id = insert_data($q);
		if (!$id) {
			return false;
		}

		$collection = new ElggAccessCollection($id);
		$params = array('collection' => $collection);
		if (!elgg_trigger_plugin_hook('create', 'access:collection', $params, true)) {
			$collection->delete();
			return false;
		}

		return $id;
	}

	/**
	 * Add a user to this access collection
	 *
	 * @param int $user_guid The GUID of user to add
	 * @return bool
	 */
	public function add($user_guid) {
		global $CONFIG;

		$user = get_user($user_guid);
		if (!elgg_instanceof($user, 'user')) {
			return false;
		}

		$params = array(
			'collection' => $this,
			'user' => $user,
		);
		if (!elgg_trigger_plugin_hook('add', 'access:collection', $params, true)) {
			return false;
		}

		// if someone tries to insert the same data twice, we do a no-op on duplicate key
		$id = sanitize_int($this->id);
		$guid = sanitize_int($user->guid);
		$q = "INSERT INTO {$CONFIG->dbprefix}access_collection_membership
				SET access_collection_id = $id, user_guid = $guid
				ON DUPLICATE KEY UPDATE user_guid = user_guid";
		$result = insert_data($q);

		return (bool)$result;
	}

	/**
	 * Remove a user from this access collection
	 *
	 * @param int $user_guid The GUID of user to remove
	 * @return bool
	 */
	public function remove($user_guid) {
		global $CONFIG;

		$user = get_user($user_guid);
		if (!elgg_instanceof($user, 'user')) {
			return false;
		}

		$params = array(
			'collection' => $this,
			'user' => $user,
		);
		if (!elgg_trigger_plugin_hook('remove', 'access:collection', $params, true)) {
			return false;
		}

		$id = sanitize_int($this->id);
		$guid = sanitize_int($user->guid);
		$q = "DELETE FROM {$CONFIG->dbprefix}access_collection_membership
			WHERE access_collection_id = $id AND user_guid = $guid";

		return (bool)delete_data($q);
	}

	/**
	 * Update the membership of the access collection
	 *
	 * @param array $new_members Array of user GUIDs. Replaces previous members.
	 * @return bool
	 */
	public function update($new_members) {

		$new_members = (is_array($new_members)) ? $new_members : array();

		$current_members = $this->getMembers();

		$remove_members = array_diff($current_members, $new_members);
		$add_members = array_diff($new_members, $current_members);

		$result = true;

		foreach ($add_members as $guid) {
			$result = $result && $this->add($guid);
		}

		foreach ($remove_members as $guid) {
			$result = $result && $this->remove($guid);
		}

		return $result;
	}

	/**
	 * Delete this access collection
	 *
	 * @return bool
	 */
	public function delete() {
		global $CONFIG;

		$params = array('collection' => $this);
		if (!elgg_trigger_plugin_hook('delete', 'access:collection', $params, true)) {
			return false;
		}

		// Deleting membership doesn't affect result of deleting ACL.
		$id = sanitize_int($this->id);
		$q = "DELETE FROM {$CONFIG->dbprefix}access_collection_membership
			WHERE access_collection_id = $id";
		delete_data($q);

		$q = "DELETE FROM {$CONFIG->dbprefix}access_collections
			WHERE id = $id";
		$result = delete_data($q);

		return (bool)$result;
	}

	/**
	 * Get the access collection ID
	 * @return int
	 */
	public function getID() {
		return $this->id;
	}

	/**
	 * Get the name of the access collection
	 * @return string
	 */
	public function getName() {
		if (!$this->loaded) {
			$this->loadInfo();
		}
		return $this->name;
	}

	/**
	 * Get the owner of the access collection
	 * @return ElggEntity
	 */
	public function getOwnerEntity() {
		if (!$this->loaded) {
			$this->loadInfo();
		}
		return get_entity($this->owner_guid);
	}

	/**
	 * Get the site the access collection belongs to
	 * @return ElggSite
	 */
	public function getSiteEntity() {
		if (!$this->loaded) {
			$this->loadInfo();
		}
		return get_entity($this->site_guid);
	}

	/**
	 * Get an array of the GUIDs of the members
	 * @return array
	 */
	public function getMembers() {
		if (!$this->members) {
			$this->loadMembers();
		}
		return $this->members;
	}

	/**
	 * Can the user change this access collection?
	 *
	 * Use the plugin hook of 'permissions_check', 'access:collection' to change this.
	 *
	 * Respects access control disabling for admin users and {@see elgg_set_ignore_access()}
	 *
	 * @param int $user_guid The user GUID to check for. Defaults to logged in user.
	 * @return bool
	 */
	public function canEdit($user_guid = 0) {
		if ($user_guid) {
			$user = get_user($user_guid);
		} else {
			$user = elgg_get_logged_in_user_entity();
		}

		if (elgg_instanceof($user, 'user')) {
			// owner and admins can edit a collection
			$result = elgg_is_admin_logged_in() || $user->guid == $this->getOwnerEntity()->guid;
			$result = $result || elgg_get_ignore_access();
		} else {
			$result = false;
		}

		// prefer hook name of 'can_edit', 'access:collection'
		return elgg_trigger_plugin_hook('permissions_check', 'access:collection', $params, $result);
	}
}
