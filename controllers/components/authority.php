<?php
/**
 * Authority: Simple static ACLs for the rest of us.
 *
 * @copyright     Copyright 2010, Plank Design (http://plankdesign.com)
 * @license       http://opensource.org/licenses/mit-license.php The MIT License
 */

/**
 * Authority Component
 *
 * General note: This component should be loaded _after_ the Auth component. This allows
 * normal AuthComponent methods to allow/disallow actions based on whether or not the
 * user is currently logged in, and then this component may be used to restrict access
 * to certain controller/action pairs of the already authenticated and
 * AuthComponent::allow'ed() users.
 *
 * This component provides all the basic functionality required from your
 * standard access control list schemes. Where this component differs from the
 * one included with CakePHP are the following:
 *
 * - All permissions are statically defined in a Configure::load()'able file,
 *   instead of in the database or in a static .ini file.
 * - All permissions are group-based.
 * - This component is designed to be used in tandem with the Auth component.
 *
 */
class AuthorityComponent extends Object {

	/**
	 * Components used by this component
	 *
	 * @var array Components & optional initialize settings
	 */
	public $components = array('Session');

	/**
	 * Handle to controller object that called this component
	 *
	 * @var object Controller reference
	 */
	public $controller = null;

	/**
	 * Name of the configuration file (without extension), that will
	 * be loaded & used to perform authorization queries against.
	 *
	 * @var string
	 */
	public $file = 'authority';

	/**
	 * Flag used to indicated whether or not the Authority configuration file
	 * has already been loaded.
	 *
	 * @var boolean Cached copy of the ACL if loaded, null otherwise.
	 */
	protected $_loaded = null;

	/**
	 * Initialization of the Authority component
	 *
	 * Captures the calling controller and sets teh settings.
	 *
	 * @param object Controller reference object
	 * @param array Optional settings that this component can parse.
	 */
	public function initialize(&$controller, $settings = array()) {
		$this->controller = $controller;
		$this->_set($settings);
	}

	/**
	 * Performs a check on the passed $identifier to ascertain it's
	 * access to the current controller/action. If the 'controller' and/or
	 * 'action' keys are set in the optional $params, these will be used
	 * instead of their counterparts found in the currently dispatched request.
	 *
	 * @param string $identifier Group identifier
	 * @param array $params Array of parameter overrides. Currently accepts
	 *        both 'action' and 'controller' keys.
	 * @return boolean True if $identifier is permitted to access the requested
	 *         controller/action pair, false otherwise.
	 */
	public function allowed($identifier, $params = array()) {
		if (!$this->_loaded) {
			$this->_loaded = $this->_load($this->file);
		}
		$params = $params + $this->controller->params;

		if (!isset($this->_loaded[$identifier])) {
			return false;
		}
		if (isset($this->_loaded[$identifier]['*'])) {
			return true;
		}
		if (!isset($this->_loaded[$identifier][$params['controller']])) {
			return (in_array($params['controller'], $this->_loaded[$identifier]));
		}
		return in_array($params['action'], $this->_loaded[$identifier][$params['controller']]);
	}

	/**
	 * Returns an array of the valid permissions for the passed $identifier
	 *
	 * @param string $identifer Group identifier
	 * @return array Access permissions, or false if $identifier does not map
	 *         to any currently configured groups
	 */
	public function permissions($identifier) {
		if (!$this->_loaded) {
			$this->_loaded = $this->_load($this->file);
		}
		if (!isset($this->_loaded[$identifier])) {
			return false;
		}
		return $this->_loaded[$identifier];
	}

	/**
	 * Loads the specified Configure file into the current request scope.
	 *
	 * @param string $config Extension-less name of configuration file
	 *        to be loaded.
	 * @return boolean True if configuration was successfully loaded, false otherwise.
	 */
	protected function _load($file) {
		/* Configure::load() returns null on success. Let's make it less dumb. */
		if (Configure::load($file) === null) {
			return $this->_loaded = Configure::read('ACL');
		}
		return false;
	}
}

?>