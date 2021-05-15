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

class BreezingcommerceControllerFiles extends JControllerLegacy
{
    function __construct()
    {
        parent::__construct();
        
        
    }
    
    function download(){
        
        if(JFactory::getApplication()->input->getWord('format') == 'raw'){
            
            JRequest::setVar('format','raw');
            JRequest::setVar('layout','download');
            
        }
        
        JRequest::setVar('tmpl', JRequest::getWord('tmpl',null));
        JRequest::setVar('layout', JRequest::getWord('layout',null));
        JRequest::setVar('view', 'files');
        parent::display();
    }
    
    function display($cachable = false, $urlparams = array())
    {
        
        JRequest::setVar('tmpl', JRequest::getWord('tmpl',null));
        JRequest::setVar('layout', JRequest::getWord('layout',null));
        JRequest::setVar('view', 'files');
        parent::display();
    }
}
