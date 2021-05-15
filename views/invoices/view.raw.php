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

class BreezingcommerceViewInvoices extends JViewLegacy
{
    function display($tpl = null)
    {
        
        try{
            
            $invoice = $this->get('Invoice');
            
        } catch(Exception $e){
            JFactory::getApplication()->enqueueMessage(JText::_('COM_BREEZINGCOMMERCE_INVOICE_NOT_FOUND'),'error');
            JFactory::getApplication()->redirect('index.php');
        }
        
        if(!JFile::exists($invoice)){
            JFactory::getApplication()->enqueueMessage(JText::_('COM_BREEZINGCOMMERCE_INVOICE_NOT_FOUND'),'error');
            JFactory::getApplication()->redirect('index.php');
        }
        
        $this->assignRef('invoice', $invoice);
        
        parent::display($tpl);
    }
}
