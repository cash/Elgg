<?php

/**
 * This class manages the subscriptions of users.
 *
 * @since 1.9
 */
class ElggSubscriptionManager {
	
	/**
	 * Subscribe a user for notifications
	 *
	 * @param ElggSubscription $subscription The subscription to add
	 * @return bool
	 */
	public function addSubscription(ElggSubscription $subscription) {
		if (!$this->validateSubscription($subscription)) {
			return false;
		}

		$params = $subscription->getData();
		foreach ($params as $key => $value) {
			if (!is_int($value)) {
				$params[$key] = "'". sanitize_string($value) . "'";
			}
		}

		$db_prefix = elgg_get_config('dbprefix');
		$columns = implode(',', array_keys($params));
		$values = implode(',', array_values($params));

		// @todo how to stop duplicates
		$query = "INSERT INTO {$db_prefix}subscriptions ($columns) VALUE ($values)";
		return 0 === insert_data($query);
	}

	/**
	 * Unsubscribe a user for a notification event
	 *
	 * @param ElggSubscription $subscription The subscription to remove
	 * @return bool
	 */
	public function removeSubscription(ElggSubscription $subscription) {

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
	public function getSubscriptions(ElggNotificationEvent $event) {

		$actor = $event->getActor();
		$action = $event->getAction();
		$object = null;
		$target = null;

		$eventObject = $event->getObject();
		if (!$eventObject) {
			// object was deleted before notifications sent
			// @todo need to think about sending notifications on non-public stuff
			return array();
		}
		$type = $eventObject->getType();
		$subtype = $eventObject->getSubtype();
		switch ($type) {
			case 'relationship':
				$object = get_entity($eventObject->guid_two);
				break;
			case 'annotation':
				$object = $eventObject->getEntity();
				$target = $object->getContainerEntity();
				break;
			default:
				$object = $eventObject;
				$target = $object->getContainerEntity();
				break;
		}

		$subscriptions = array();
		$db_prefix = elgg_get_config('dbprefix');
		$query = "SELECT * from {$db_prefix}subscriptions";

		$wheres = array();
		//$wheres[] = "actor_guid = $actor->guid AND target_guid = NULL AND object_guid = NULL AND action = NULL";
		//$wheres[] = "actor_guid = $actor->guid AND target_guid = NULL AND object_guid = NULL AND action = 'create'";

		$results = get_data($query);
		if ($results) {
			foreach ($results as $result) {
				$subscriptions[$result->subscriber_guid] = array('site');
			}
		}
		return $subscriptions;
	}

	/**
	 * Check that the subscription is valid
	 * 
	 * @param ElggSubscription $subscription The subscription to add
	 * @return bool
	 */
	protected function validateSubscription(ElggSubscription $subscription) {
		// method must be registered
		$methods = elgg_get_config('notification_methods');
		if (!$methods || !in_array($subscription->getMethod(), $methods)) {
			return false;
		}

		// @todo event must be registered

		return true;
	}
}