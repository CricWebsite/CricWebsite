<?php
/**
 * @package     BreezingCommerce
 * @author      Markus Bopp
 * @link        http://www.crosstec.de
 * @license     GNU/GPL
*/

// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

class BreezingcommerceControllerCategoriespicker extends JControllerLegacy
{
    function __construct()
    {
        parent::__construct();

        require_once( JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'CrBcCategoriesHelper.php' );

    }

    function display($cachable = false, $urlparams = array())
    {
        JRequest::setVar('view', 'categoriespicker');
        parent::display();
    }
}
