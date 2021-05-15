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

class BreezingcommerceViewCategoriespicker extends JViewLegacy
{
    function display($tpl = null)
    {
        CrBcHelpers::addCssFile(Juri::root(true).'/components/com_breezingcommerce/css/font-awesome/css/font-awesome.min.css');
        
        // Get data from the model
        $items = $this->get( 'Data');
        
        $pagination = $this->get('Pagination');

        /* Get the values from the state object that were inserted in the model's construct function */
        $state = $this->get( 'state' );
        $lists['order_Dir'] = $state->get( 'category_filter_order_Dir' );
        $lists['order'] = $state->get( 'category_filter_order' );
        $lists['category_search'] = $state->get( 'category_search' );
        $lists['category_search_by'] = $state->get( 'category_search_by' );
        $lists['limitstart'] = $state->get( 'limitstart' );
        $lists['state']	= JHTML::_('grid.state', $state->get( 'category_filter_state' ) );

        $ordering = ($lists['order'] == 'c.ordering');

        $this->assignRef('ordering', $ordering);
        $this->assignRef( 'lists', $lists );
        $this->assignRef( 'items', $items );
        $this->assignRef( 'pagination', $pagination );
        parent::display($tpl);
    }
}
