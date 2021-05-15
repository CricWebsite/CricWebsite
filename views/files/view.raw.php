<?php
/**
 * @package     BreezingCommerce
 * @author      Markus Bopp
 * @link        http://www.crosstec.de
 * @license     GNU/GPL
*/

// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class BreezingcommerceViewFiles extends JViewLegacy
{
    function display($tpl = null)
    {
        
        $file = $this->get('File');
        
        if(!$file){
            JFactory::getApplication()->enqueueMessage(JText::_('COM_BREEZINGCOMMERCE_FILE_NOT_FOUND'),'error');
            JFactory::getApplication()->redirect('index.php');
        }
        
        $this->assignRef('file', $file);
        
        parent::display($tpl);
    }
}
