Authority Component
===================

*General note*: This component should be loaded _after_ the Auth component. This allows
normal AuthComponent methods to allow/disallow actions based on whether or not the
user is currently logged in, and then this component may be used to restrict access
to certain controller/action pairs of the already authenticated and
AuthComponent::allow'ed() users.

This component provides all the basic functionality required from your
standard access control list schemes. Where this component differs from the
one included with CakePHP are the following:

 - All permissions are statically defined in a Configure::load()'able file,
   instead of in the database or in a static .ini file.
 - All permissions are group-based.
 - This component is designed to be used in tandem with the Auth component.


Configuration
-------------

An example Authority configuration, which would be placed in `app/config/authority.php`:

    <?php

    /*
     * Module permissions
     *
     * Keys are group slug names, and values are the controllers they have access to.
     * If a controller is specified only by a key (e.g. 'articles'), then the group
     * has access to all actions.
     * If it is a key/assoc. array pair, then the group is restricted to those specific
     * actions (e.g. 'store' => 'reports' or 'store' => array('reports', 'inventory')))
     *
     * A star '*' signifies unrestricted access to all controllers & actions contained therein.
     */

    $config['ACL'] = array(
    	'administrator' => '*',
    	'editor' => array('articles' => array('publish', 'edit', 'delete')),
    	'csr' => array('orders', 'payments', 'users' => array('view', 'resend_password'))
    );
    ?>

Use & Integration
-----------------

Integrating Authority into your application is simple, especially when you're already
using the default CakePHP Auth component.

First, we add the component to our `AppController`, making sure it comes _after_ the
Auth component:

    public $components = array('Auth', 'Authority')

Next, In your `AppController::beforeFilter()`, configure the `Auth` component to use the `controller`
authorization type:

    /* in your beforeFilter() */
    $this->Auth->authorize = 'controller';


This indicates that Auth should check for the existence of an `isAuthorized()` method in the
controller being called to determine if the user has the proper permissions to access the
requested resource. For the simplest possible configuration, we can add the following
method to our `app/app_controller.php`:


    /**
     * Global isAuthorized() AuthComponent callback to provide more fine-grained
     * authorization controls.
     *
     * This method is processed *after* we have already been authenticated.
     *
     * @return boolean True if authorized to view the resource, false otherwise.
     */
    public function isAuthorized() {
        return $this->Authority->allowed($this->Auth->user('role'));
    }

You can, of course, add in any additional logic that you may require in the `isAuthorized()` method.
Moreover, you are also able to override the method in subclasses (optionally calling
`parent::isAuthorized()`) to provide more fine-grained authorization control at the controller level.

This plugin comes complete with tests, and we welcome any suggested enhancements.
If you think you've found a bug or unexpected behavior, please submit a ticket or a pull request
for a patch & test case.

Thanks!