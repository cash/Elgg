<?php
/**
 * Elgg Test navigation
 *
 *
 * @package Elgg
 * @subpackage Test
 */
class ElggCoreNavigationTest extends ElggCoreUnitTest {

	/**
	 * Called before each test object.
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Called before each test method.
	 */
	public function setUp() {
		$this->items = array();
		for ($i=1; $i<=5; $i++) {
			$m = new ElggMenuItem("menu$i", "title$i", "http://example.org/$i");
			$this->items[$i] = $m;
		}
	}

	/**
	 * Called after each test method.
	 */
	public function tearDown() {
		// do not allow SimpleTest to interpret Elgg notices as exceptions
		$this->swallowErrors();

		unset($this->items);
	}

	/**
	 * Called after each test object.
	 */
	public function __destruct() {
		// all __destruct() code should go above here
		parent::__destruct();
	}

	/**
	 * Test elgg_menu_setup_sections()
	 */
	public function testMenuSetupSections() {

		$s1 = array(1,3,5);
		$s2 = array(2,4);
		foreach ($s1 as $index) {
			$this->items[$index]->setSection('s1');
		}
		foreach ($s2 as $index) {
			$this->items[$index]->setSection('s2');
		}

		$menu = elgg_menu_setup_sections($this->items);

		foreach ($menu['s1'] as $item) {
			$id = (int)substr($item->getID(), strlen("menu"));
			$this->assertTrue(in_array($id, $s1));
		}
		foreach ($menu['s2'] as $item) {
			$id = (int)substr($item->getID(), strlen("menu"));
			$this->assertTrue(in_array($id, $s2));
		}
	}

	/**
	 * Test elgg_menu_find_selected()
	 */
	public function testMenuFindSelected() {

	}

	/**
	 * Test elgg_menu_setup_trees()
	 */
	public function testMenuSetupTrees() {
		//   1->3,4->5
		//   2

		$this->items[3]->setParentID("menu1");
		$this->items[4]->setParentID("menu1");
		$this->items[5]->setParentID("menu3");

		$grouped_menu = array();
		$grouped_menu['default'] = $this->items;

		$menu = elgg_menu_setup_trees($grouped_menu);
		$this->assertNull($menu['default'][0]->getParent());
		$children = $menu['default'][0]->getChildren();
		$this->assertEqual(2, count($children));
		if (isset($children[0])) {
			$this->assertEqual("menu3", $children[0]->getID());
			$parent = $children[0]->getParent();
			$this->assertEqual("menu1", $children[0]->getParent()->getID());
			$third_gen = $children[0]->getChildren();
			if (isset($third_gen[0])) {
				$this->assertEqual("menu5", $third_gen[0]->getID());
				$this->assertEqual("menu3", $third_gen[0]->getParent()->getID());
			}
		}
		if (isset($children[1])) {
			$this->assertEqual("menu4", $children[1]->getID());
			$this->assertEqual("menu1", $children[1]->getParent()->getID());
		}
		$this->assertEqual(0, count($menu['default'][1]->getChildren()));
		$this->assertNull($menu['default'][1]->getParent());
	}

	/**
	 * Test elgg_menu_setup_sort
	 */
	public function testMenuSort() {

	}

}