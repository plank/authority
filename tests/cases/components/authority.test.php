<?php
/**
 * Authority: Simple static ACLs for the rest of us.
 *
 * @copyright     Copyright 2010, Plank Design (http://plankdesign.com)
 * @license       http://opensource.org/licenses/mit-license.php The MIT License
 */

App::import('Component', array('Authority.Authority', 'Session'));
App::import('Core', 'AppController');

class AuthorityTest extends CakeTestCase {

	protected $_config = null;

	public function setUp() {
		$this->_config = Configure::read('Cache.disable');
		Configure::write('Cache.disable', false);

		Mock::generate('AppController', 'TestController');
		Mock::generate('SessionComponent', 'MockSession');

		$this->Controller = new TestController();
		$this->Acl = new AuthorityComponent();
		$this->Acl->Session = new MockSession();
		$this->_writeTestConfiguration('test_authority');
	}

	public function tearDown() {
		Configure::write('Cache.disable', $this->_config);
		unset($this->Controller, $this->Acl);
		unlink(CACHE . 'persistent' . DS . 'test_authority.php');
	}

	protected function _writeTestConfiguration() {
		$name = 'test_authority';
		$acl = array(
			'editor' => array('articles' => array('publish', 'edit', 'delete')),
			'administrator' => '*',
			'csr' => array('orders', 'payments', 'users' => array('view', 'resend_password'))
		);

		Configure::store('ACL', $name, $acl);
	}

	public function testInstance() {
		$this->assertTrue($this->Acl instanceof AuthorityComponent);
		$this->assertEqual($this->Acl->components, array('Session'));
	}

	public function testInitialize() {
		$this->Acl->initialize($this->Controller);
		$this->assertTrue($this->Acl->controller instanceof TestController);
	}

	public function testLoadAppAuthorizationConfigureFile() {
		Configure::load('test_authority');
		$result = Configure::read('ACL');
		$this->assertTrue(!empty($result), "%s Could not load the test_authority");
	}

	public function testInitializeSessionRoleCheck() {
		$this->Acl->initialize($this->Controller);
		$this->assertEqual($this->Acl->controller, $this->Controller);
	}

	public function testAllowed() {
		$this->Acl->file = 'test_authority';

		$this->Acl->controller = new StdClass();
		$this->Acl->controller->params = array('controller' => 'articles', 'action' => 'delete');
		$result = $this->Acl->allowed('editor');
		$this->assertTrue($result);

		$result = $this->Acl->allowed('does_not_exist');
		$this->assertFalse($result);

		$this->Acl->controller->params = array('controller' => 'store', 'action' => 'edit');
		$result = $this->Acl->allowed('editor');
		$this->assertFalse($result);

		$this->Acl->controller->params = array('controller' => 'articles', 'action' => 'unpublish');
		$result = $this->Acl->allowed('editor');
		$this->assertFalse($result);

		$this->Acl->controller->params = array('controller' => 'store', 'action' => 'edit');
		$result = $this->Acl->allowed('administrator');
		$this->assertTrue($result);

		$this->Acl->controller->params = array('controller' => 'orders', 'action' => 'view');
		$result = $this->Acl->allowed('csr');
		$this->assertTrue($result);

		$this->Acl->controller->params = array('controller' => 'articles', 'action' => 'delete');
		$result = $this->Acl->allowed('csr');
		$this->assertFalse($result);

		$this->Acl->controller->params = array('controller' => 'users', 'action' => 'view');
		$result = $this->Acl->allowed('csr');
		$this->assertTrue($result);

		$this->Acl->controller->params = array('controller' => 'users', 'action' => 'edit');
		$result = $this->Acl->allowed('csr');
		$this->assertFalse($result);

		$result = $this->Acl->allowed('editor');
		$this->assertFalse($result);
	}

	public function testAllowedPassedParams() {
		$this->Acl->file = 'test_authority';
		$this->Acl->controller = new StdClass();
		$this->Acl->controller->params = array();

		$params = array('controller' => 'users', 'action' => 'edit');
		$result = $this->Acl->allowed('csr', $params);
		$this->assertFalse($result);

		$result = $this->Acl->allowed('editor', $params);
		$this->assertFalse($result);

		$this->Acl->controller->params = array('controller' => 'users', 'action' => 'view');
		$result = $this->Acl->allowed('csr', $params);
		$this->assertFalse($result);
	}

	public function testPermissions() {
		$this->Acl->file = 'test_authority';

		$result = $this->Acl->permissions('editor');
		$this->assertTrue(array_key_exists('articles', $result));
		$this->assertEqual(array_diff($result['articles'], array('publish', 'edit', 'delete')), array());
		$this->assertEqual(array(), array_diff($result['articles'], array('publish', 'edit', 'delete')));
		$this->assertEqual(count($result['articles']), 3);
		$this->assertEqual(count($result), 1);
	}
}

?>