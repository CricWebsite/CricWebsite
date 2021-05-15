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
        
        if( JRequest::getInt('address_incomplete', 0) == 1 ){
            $true = true;
            $this->assignRef('address_incomplete', $true);
            $href = JRequest::getVar('href');
            $this->assignRef('href', $href);
            
        } else {
        
            try{

                $invoice = $this->get('Invoice');
                
                $true = true;
                $this->assignRef('success', $true);

            } catch(Exception $e){

                $this->assignRef('empty', 'empty');
            }
        
        }
        
        parent::display($tpl);
    }
}
