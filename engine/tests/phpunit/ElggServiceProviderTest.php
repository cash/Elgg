<?php

class ElggServiceProviderTest extends PHPUnit_Framework_TestCase {

	public function testSharedMetadataCache() {
		$mgr = $this->getMock('ElggAutoloadManager', array(), array(), '', false);

		$sp = new Elgg_ServiceProvider($mgr);

		$svcClasses = array(
			'metadataCache' => 'Elgg_VolatileMetadataCache',
			'autoloadManager' => 'ElggAutoloadManager',
			'db' => 'Elgg_Database',
			'hooks' => 'Elgg_PluginHookService',
			'logger' => 'Elgg_Logger',
		);

		foreach ($svcClasses as $key => $class) {
			$obj1 = $sp->{$key};
			$obj2 = $sp->{$key};
			$this->assertInstanceOf($class, $obj1);
			$this->assertSame($obj1, $obj2);
		}
	}
}
