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