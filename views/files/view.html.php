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
        CrBcHelpers::addCssFile(Juri::root(true).'/components/com_breezingcommerce/css/font-awesome/css/font-awesome.min.css');
        
        require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'CrBcCart.php');
        // clear (unverify anything that's no longer valid
        // (downloads and verified items)
        CrBcCart::unverifyItemsGlobally();

        // re-check to recover previously lost groups if items intersect
        CrBcCart::verifyItemsGlobally();
        
        $files = $this->get('Data');
        
        if($files == null || !count($files)){
            JFactory::getApplication()->enqueueMessage(JText::_('COM_BREEZINGCOMMERCE_NO_FILES_AVAILABLE_MESSAGE'),'error');
            JFactory::getApplication()->redirect('index.php');
        }
        
        $pagination = $this->get('Pagination');
        $this->assignRef('pagination', $pagination);
        
        $this->assignRef('files', $files);
        
        $menu = JFactory::getApplication()->getMenu();
        $active = $menu->getActive();
        
        if(is_object($active)){
        
            $heading = $active->params->get('page_heading');

            if(!$heading){

                $heading = JFactory::getDocument()->getTitle();
            }

            $this->assignRef('heading', $heading);

            $pageclass_sfx = $active->params->get('pageclass_sfx');
            $this->assignRef('pageclass_sfx', $pageclass_sfx);
        
        } else {
            
            $empty = '';
            $this->assignRef('heading', $empty);
            $this->assignRef('pageclass_sfx', $empty);
        }
        
        CrBcHelpers::setPageTitle(JFactory::getDocument()->getTitle());
        
        parent::display($tpl);
    }
}
