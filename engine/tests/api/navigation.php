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
			$id = (int)substr($item->getName(), strlen("menu"));
			$this->assertTrue(in_array($id, $s1));
		}
		foreach ($menu['s2'] as $item) {
			$id = (int)substr($item->getName(), strlen("menu"));
			$this->assertTrue(in_array($id, $s2));
		}
	}

	/**
	 * Test elgg_menu_setup_trees()
	 */
	public function testMenuSetupTrees() {
		//   1->3,4->5
		//   2

		$this->items[3]->setParentName("menu1");
		$this->items[4]->setParentName("menu1");
		$this->items[5]->setParentName("menu3");

		$grouped_menu = array();
		$grouped_menu['default'] = $this->items;

		$menu = elgg_menu_setup_trees($grouped_menu);
		$this->assertNull($menu['default'][0]->getParent());
		$children = $menu['default'][0]->getChildren();
		$this->assertEqual(2, count($children));
		if (isset($children[0])) {
			$this->assertEqual("menu3", $children[0]->getName());
			$parent = $children[0]->getParent();
			$this->assertEqual("menu1", $children[0]->getParent()->getName());
			$third_gen = $children[0]->getChildren();
			if (isset($third_gen[0])) {
				$this->assertEqual("menu5", $third_gen[0]->getName());
				$this->assertEqual("menu3", $third_gen[0]->getParent()->getName());
			}
		}
		if (isset($children[1])) {
			$this->assertEqual("menu4", $children[1]->getName());
			$this->assertEqual("menu1", $children[1]->getParent()->getName());
		}
		$this->assertEqual(0, count($menu['default'][1]->getChildren()));
		$this->assertNull($menu['default'][1]->getParent());
	}

	/**
	 * Test elgg_menu_setup_sort
	 */
	public function testMenuSort() {
		//   1->3,2  2->5,4

		$this->items[1]->addChild($this->items[3]);
		$this->items[3]->setParent($this->items[1]);
		$this->items[1]->addChild($this->items[2]);
		$this->items[2]->setParent($this->items[1]);

		$this->items[3]->addChild($this->items[5]);
		$this->items[5]->setParent($this->items[3]);
		$this->items[3]->addChild($this->items[4]);
		$this->items[4]->setParent($this->items[3]);

		$grouped_menu = array();
		$grouped_menu['default'] = array($this->items[1]);

		$sorted = elgg_menu_sort($grouped_menu);

		$root = $sorted['default'][0];
		$this->assertEqual($root->getName(), 'menu1');

		$children = $root->getChildren();
		$this->assertEqual($children[0]->getName(), 'menu2');
		$this->assertEqual($children[1]->getName(), 'menu3');

		$children = $children[1]->getChildren();
		$this->assertEqual($children[0]->getName(), 'menu4');
		$this->assertEqual($children[1]->getName(), 'menu5');
	}

}