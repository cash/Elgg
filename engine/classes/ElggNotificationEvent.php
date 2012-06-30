<?php
/**
 * Notification event
 */
class ElggNotificationEvent {

	/* @var string The name of the action/event */
	protected $event;

	/* @var string The type of the object (entity, relationship, annotation) */
	protected $object_type;

	/* @var int The identifier of the object (GUID for entity, id for relationship or annotation) */
	protected $object_id;

	/* @var int The GUID of the user who triggered the event */
	protected $actor_guid;


	/**
	 * Create a notification event
	 *
	 * @param ElggData $object The object of the event (ElggEntity, ElggAnnotation, ElggRelationship)
	 * @param string   $event  The name of the event (default: create)
	 * @param ElggUser $actor  The user that caused the event (default: logged in user)
	 */
	public function __construct($object, $event = 'create', $actor = null) {
		if (!($object instanceof ElggData)) {
			// @todo find the best message - probably create generic message
			throw new InvalidParameterException();
		}

		if (elgg_instanceof($object)) {
			$this->object_type = 'entity';
			$this->object_id = $object->getGUID();
		} else {
			$this->object_type = $object->getType();
			$this->object_id = $object->id;
		}

		if ($actor == null) {
			$this->actor_guid = elgg_get_logged_in_user_guid();
		} else {
			$this->actor_guid = $actor->getGUID();
		}

		$this->event = $event;
	}

	/**
	 * Get the actor of the event
	 *
	 * @return ElggUser
	 */
	public function getActor() {
		return get_entity($this->actor_guid);
	}

	/**
	 * Get the object of the event
	 *
	 * @return ElggData
	 */
	public function getObject() {
		switch ($this->object_type) {
			case 'entity':
				return get_entity($this->object_id);
				break;
			case 'relationship':
				return get_relationship($this->object_id);
				break;
			case 'annotation':
				return elgg_get_annotation_from_id($this->object_id);
				break;
		}
		return null;
	}

	/**
	 * Get the name of the event
	 *
	 * @return string
	 */
	public function getName() {
		return $this->event;
	}
}
