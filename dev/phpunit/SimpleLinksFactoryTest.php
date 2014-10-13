<?php
/**
 * SimpleLinksFactoryTest.php
 * 
 * @author mat
 * @since 9/25/2014
 *
 * @package wordpress
 */


class SimpleLinksFactoryTest extends WP_UnitTestCase {


	public function test_description(){

		$o = new SimpleLinksFactory( array( 'description' => true ), 'phpunit' );
		$this->assertContains( array( 'description' => true) , $o->args, "The description arg did not retain it's value. See 89d56ae1699aa6c95075ae702d61ae039f0dc794" );

	}

}
 