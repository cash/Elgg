<?php
/**
 * Elgg Menu Item
 *
 * @package    Elgg.Core
 * @subpackage Navigation
 *
 * @since 1.8.0
 */
class ElggMenuItem {
	/**
	 * @var string Identifier of the menu
	 */
	protected $id;

	/**
	 * @var string The menu display string
	 */
	protected $title;

	/**
	 * @var string The menu url
	 */
	protected $url;

	/**
	 * @var array Page context array
	 */
	protected $contexts = array('all');

	/**
	 * @var string Menu section identifier
	 */
	protected $section = 'default';

	/**
	 * @var string Tooltip
	 */
	protected $tooltip = '';

	/**
	 * @var bool Is this the currently selected menu item 
	 */
	protected $selected = false;

	/**
	 * @var string Identifier of this item's parent
	 */
	 protected $parent_id = '';
	 
	 protected $children = array();

	/**
	 * ElggMenuItem constructor
	 *
	 * @param string $id
	 * @param string $title
	 * @param string $url
	 */
	public function __construct($id, $title, $url) {
		$this->id = $id;
		$this->title = $title;
		$this->url = $url;
	}

	/**
	 * Get the identifier of the menu item
	 *
	 * @return string
	 */
	public function getID() {
		return $this->id;
	}

	/**
	 * Get the display title of the menu
	 *
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Get the URL of the menu item
	 *
	 * @return string
	 */
	public function getURL() {
		return $this->url;
	}

	/**
	 * Set the contexts that this menu item is available for
	 *
	 * @param array $contexts An array of context strings
	 *
	 * @return void
	 */
	public function setContext($contexts) {
		if (is_string($contexts)) {
			$contexts = array($contexts);
		}
		$this->contexts = $contexts;
	}

	/**
	 * Get an array of context strings
	 *
	 * @return array
	 */
	public function getContext() {
		return $this->contexts;
	}

	/**
	 * Should this menu item be used given the current context
	 *
	 * @param string $context A context string (default is empty string for
	 *                        current context stack.
	 * 
	 * @return bool
	 */
	public function inContext($context = '') {
		if ($context) {
			return in_array($context, $this->contexts);
		}

		if (in_array('all', $this->contexts)) {
			return true;
		}

		foreach ($this->contexts as $context) {
			if (elgg_in_context($context)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Set the selected flag
	 *
	 * @param bool $state Selected state (default is true)
	 * 
	 * @return void
	 */
	public function setSelected($state = true) {
		$this->selected = $state;
	}

	/**
	 * Get selected state
	 *
	 * @return bool
	 */
	public function getSelected() {
		return $this->selected;
	}

	/**
	 * Set the tool tip text
	 *
	 * @param string $text The text of the tool tip
	 *
	 * @return void
	 */
	public function setTooltip($text) {
		$this->tooltip = $text;
	}

	/**
	 * Get the tool tip text
	 *
	 * @return string
	 */
	public function getTooltip() {
		return $this->tooltip;
	}

	/**
	 * Set the section identifier
	 *
	 * @param string $section The identifier of the section
	 *
	 * @return void
	 */
	public function setSection($section) {
		$this->section = $section;
	}

	/**
	 * Get the section identifier
	 *
	 * @return string
	 */
	public function getSection() {
		return $this->section;
	}

	/**
	 * Set the parent identifier
	 *
	 * @param string $parent_id The identifier of the parent ElggMenuItem
	 *
	 * @return void
	 */
	public function setParentID($parent_id) {
		$this->parent_id = $parent_id;
	}

	/**
	 * Get the parent identifier
	 *
	 * @return string
	 */
	public function getParentID() {
		return $this->parent_id;
	}

	
	public function addChild($item) {
		$this->children[] = $item;
	}

	/**
	 * Get the menu link
	 *
	 * @todo add styling
	 * 
	 * @return string
	 */
	public function getLink() {
		$vars = array(
			'href' => $this->url,
			'text' => $this->title
		);
		return elgg_view('output/url', $vars);
	}
}
