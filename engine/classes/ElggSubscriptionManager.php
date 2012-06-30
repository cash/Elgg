<?php

/**
 * This class manages the subscriptions of users.
 *
 * @todo add some constants instead of using strings
 *
 * @since 1.9
 */
class ElggSubscriptionManager {
	
	/**
	 * Subscribe a user for notifications to a notification event
	 *
	 * @todo move this into ElggSubscriptionManager class
	 *
	 * @param ElggUser $user       The subscriber
	 * @param string   $data_class 'entity', 'annotation', or 'relationship'
	 * @param array    $params     An array of parameters that define the event for this subscription.
	 *                             The parameters supported depend on the $data_type.
	 *                     ENTITY parameters:
	 *                     'actor'     => The GUID of the user performing the action on the entity
	 *                     'owner'     => The GUID of the entity that owns the entity
	 *                     'container' => the GUID of the container entity for the entity
	 *                     'type'      => the type of the entity
	 *                     'subtype'   => the subtype of the entity (requires that type is set)
	 *                     'event'     => the action being performed on the entity ('create', 'update', 'publish')
	 *                     One of actor, owner, container, or type/subtype pair is required
	 *
	 *                     RELATIONSHIP parameters:
	 *                     'actor'     => The GUID of the user creating the relationship
	 *                     'subject'   => The GUID of the subject of the relationship
	 *                     'object'    => The GUID of the object of the relationship
	 *                     'type'      => The type of the relationship
	 *
	 *                     ANNOTATION parameters:
	 *                     'actor'     => The GUID of the user creating the annotation
	 *                     'object'    => The GUID of the subject of the annotation
	 *                     'container' => The GUID of the subject of the annotation
	 *                     'type'      => The type of the annotation
	 *                     'event'     => the action being performed on the annotation ('create', 'update')
	 *
	 * @param array    $methods    An array of delivery methods strings: array('email', 'site')
	 *
	 * @return bool
	 */
	public function addSubscription($user, $data_class, array $params, array $methods) {
		if (!$this->validateSubscription($user, $data_class, $params, $methods)) {
			return false;
		}

		$params['guid'] = $user->getGUID();

		if (isset($params['subtype'])) {
			$params['subtype'] = get_subtype_id($params['type'], $params['subtype']);
		}

		$params['data_class'] = $data_class;
		foreach ($params as $key => $value) {
			$params[$key] = "'$value'";
		}

		$db_prefix = elgg_get_config('dbprefix');
		$result = true;
		foreach ($methods as $method) {
			$cols = array_keys($params);
			$values = array_values($params);
			array_push($cols, 'method');
			array_push($values, "'$method'");
			$cols = implode(',', $cols);
			$values = implode(',', $values);
			// @todo how to stop duplicates
			$query = "INSERT INTO {$db_prefix}subscriptions ($cols) VALUE ($values)";
			$result = $result && 0 === insert_data($query);
		}
		return $result;
	}

	/**
	 * Unsubscribe a user for a set of notification events
	 *
	 * @param ElggUser $user       The subscriber
	 * @param string   $data_class 'entity', 'annotation', or 'relationship'
	 * @param array    $params     An array of parameters that define the event for this subscription.
	 * @param array    $methods    An array of delivery methods strings. Example array('email', 'site')
	 * @return bool
	 */
	public function removeSubscription($user, $data_class, array $params, array $methods) {
		if (!$this->validateSubscription($user, $data_class, $params, $methods)) {
			return false;
		}

		// @todo implement
		return false;
	}

	/**
	 * Get the subscriptions for this notification event
	 *
	 * The return array is of the form:
	 *
	 * array(
	 *     <user guid> => array('email', 'sms', 'ajax'),
	 * );
	 *
	 * @param ElggNotificationEvent $event Notification event
	 * @return array
	 */
	public function getSubscriptions($event) {
		// @todo this is a mock implementation
		$users = elgg_get_entities(array(
			'type' => 'user',
		));
		$result = array();
		foreach ($users as $user) {
			$result[$user->guid] = array('site');
		}
		return $result;
	}

	/**
	 * Check that the subscription is valid
	 * 
	 * @param ElggUser $user       The subscriber
	 * @param string   $data_class 'entity', 'annotation', or 'relationship'
	 * @param array    $params     An array of parameters that define the event for this subscription.
	 * @param array    $methods    An array of delivery methods strings. Example array('email', 'site')
	 * @return bool
	 */
	protected function validateSubscription($user, $data_class, array $params, array $methods) {
		if (!elgg_instanceof($user, 'user')) {
			return false;
		}
		if (!in_array($data_class, array('entity', 'relationship', 'annotation'))) {
			return false;
		}

		if (isset($params['subtype'])) {
			if (!isset($params['type'])) {
				return false;
			}
		}

		if (count($methods) == 0) {
			return false;
		}

		// @todo check that the method has been registered

		$validKeys = array('actor', 'owner', 'container', 'type', 'subtype', 'event', 'subject', 'object');
		foreach ($params as $key => $value) {
			if (!in_array($key, $validKeys)) {
				return false;
			}
		}

		// @todo add checks for at least one required element
		return true;
	}

	/**
	 * Store the subscription in the database
	 * 
	 * @param array $params The parameters of the subscription
	 * @return bool
	 */
	protected function storeSubscription(array $params) {
		$cols = array_keys($params);
		$values = array_values($params);
		array_push($cols, 'method');
		array_push($values, "'$method'");
		$cols = implode(',', $cols);
		$values = implode(',', $values);
		// @todo how to stop duplicates - hash all values into a key and store the key
		$query = "INSERT INTO {$db_prefix}subscriptions ($cols) VALUE ($values)";
		return 0 === insert_data($query);
	}
}