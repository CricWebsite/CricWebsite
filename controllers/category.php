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

class BreezingcommerceControllerCategory extends JControllerLegacy
{
    function __construct()
    {
        parent::__construct();
    }

    function display($cachable = false, $urlparams = array())
    {
        $cat = $this->getModel('category');
        $simple = $cat->getEnableSimpleProductView();
        if(JRequest::getVar('format','') != 'json' && $simple ){
            JRequest::setVar('layout','simple');
        }
        
        JRequest::setVar('tmpl', JRequest::getWord('tmpl',null));
        JRequest::setVar('layout', JRequest::getWord('layout',null));
        JRequest::setVar('view', 'category');
        parent::display();
    }
}
