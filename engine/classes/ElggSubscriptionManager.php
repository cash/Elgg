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
				$params[$key] = "'$value'";
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
	 * Unsubscribe a user for a set of notification events
	 *
	 * @param ElggSubscription $subscription The subscription to remove
	 * @return bool
	 */
	public function removeSubscription(ElggSubscription $subscription) {
		if (!$this->validateSubscription($subscription)) {
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
	public function getSubscriptions(ElggNotificationEvent $event) {
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
	 * @param ElggSubscription $subscription The subscription to add
	 * @return bool
	 */
	protected function validateSubscription(ElggSubscription $subscription) {
		// method must be registered

		// event must be registered

		return true;
	}
}