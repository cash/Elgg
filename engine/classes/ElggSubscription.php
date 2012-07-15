<?php
/**
 * Subscription
 *
 * This is a notification subscription of a user. It describes what events should
 * result in a notification being sent to a user. The schema is partially based on
 * Activity Streams (http://activitystrea.ms/). When an event that leads to
 * notifications occurs, the parameters of that event are compared with the
 * subscriptions to identify which users are notified.
 *
 * Definitions:
 * Actor:        the user causing the event.
 * Action:       the verb.
 * Action class: the type/subtype of the action/object
 * Object:       the entity acted on.
 * Target:       the container for the object.
 *
 * Some example subscriptions:
 * 1. I want to be notified when John does something
 *    actor: John
 *    action: null
 *    action_class: null
 *    object: null
 *    target: null
 *
 * 2. I want to be notified when Susan likes something
 *    actor: Susan
 *    action: create
 *    action_class: type = annotation and subtype = like
 *    object: null
 *    target: null
 *
 * 3. I want to be notified when something happens in the Cycling group
 *    actor: null
 *    action: null
 *    action_class: null
 *    object: null
 *    target: Cycling group
 *
 * 4. I want to be notified when someone joins the Cycling group
 *    actor: null
 *    action: create
 *    action_class: type = relationship and subtype = member
 *    object: Cycling group
 *    target: null
 *
 * 5. I want to be notified when Ed uploads a file to his personal files
 *    actor: Ed
 *    action: create
 *    action_class: type = object and subtype = file
 *    object: null
 *    target: Ed
 */
class ElggSubscription {

	/* @var ElggUser The user doing the subscribing */
	protected $subscriber_guid;

	/* @var string The method for sending the notification */
	protected $method;

	/* @var string The name of the action */
	protected $action;

	/* @var string The type related to the action/object */
	protected $action_type;

	/* @var string The subtype related to the action/object */
	protected $action_subtype;

	/* @var int The GUID of the user who triggered the event */
	protected $actor_guid;

	/* @var int The GUID of the object of the event */
	protected $object_guid;

	/* @var int The GUID of the target of the event (usually the container for the object) */
	protected $target_guid;


	/**
	 * Create a subscription object
	 * 
	 * @param ElggUser   $subscriber The user to be notified
	 * @param string     $method     How the user should be notified: email, site, sms, etc.
	 * @param string     $action     Action verb
	 * @param string     $type       Type of action/object
	 * @param string     $subtype    Subtype of action/object
	 * @param ElggUser   $actor      The user of the subscription
	 * @param ElggEntity $object     The entity acted on
	 * @param ElggEntity $target     Usually the container
	 */
	public function __construct(ElggUser $subscriber, $method, $action, $type = null, $subtype = null, ElggUser $actor = null, ElggEntity $object = null, ElggEntity $target = null) {
		if (!elgg_instanceof($subscriber, 'user')) {
			// throw exception
		}

		$this->subscriber_guid = $subscriber->getGUID();
		$this->method = $method;
		$this->action = $action;
		$this->action_type = $type;
		$this->action_subtype = $subtype;
		$this->actor_guid = $actor ? $actor->getGUID() : null;
		$this->object_guid = $object ? $object->getGUID() : null;
		$this->target_guid = $target ? $target->getGUID() : null;
	}

	/**
	 * Get the subscription as a set of key value pairs for non-null values
	 *
	 * @return array
	 */
	public function getData() {
		$data = array();
		foreach ($this as $key => $value) {
			if (!is_null($value)) {
				$data[$key] = $value;
			}
		}
		return $data;
	}

	public function getMethod() {
		return $this->method;
	}
}
