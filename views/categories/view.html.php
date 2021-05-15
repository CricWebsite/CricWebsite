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

class BreezingcommerceViewCategories extends JViewLegacy
{
    function display($tpl = null)
    {
        require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'CrBcCart.php');
        require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'CrBcImage.php');
        
        // clear (unverify anything that's no longer valid
        // (downloads and verified items)
        CrBcCart::unverifyItemsGlobally();

        // re-check to recover previously lost groups if items intersect
        CrBcCart::verifyItemsGlobally();
        
        $menu_title = $this->get('MenuTitle');
        $this->assignRef('menu_title', $menu_title);
        
        $this_category = $this->get('ThisCategory');
        $this->assignRef('this_category', $this_category);
        
        $categories = $this->get('Categories');
        
        if($this_category && JRequest::getInt('start',null) === null && JRequest::getInt('limitstart',null) === null){
                
            CrBcHelpers::setCanonical('categories', $this_category);
        }
        
        foreach( $categories As $category ){
            $desc = $category->description;
            $introtext = '';
            $fulltext  = '';
            $desc = str_replace('<br>', '<br />', $desc);

            // Search for the {readmore} tag and split the text up accordingly.
            $pattern = '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
            $tagPos = preg_match($pattern, $desc);

            if ($tagPos == 0) {
                $introtext = $desc;
            } else {
                list($introtext, $fulltext) = preg_split($pattern, $desc, 2);
            }

            $category->introtext = $introtext;
            $category->fulltext = $fulltext;
        }
        
        $this->assignRef('categories', $categories);
        
        $show_page_heading = $this->get('ShowPageHeading');
        $this->assignRef('show_page_heading', $show_page_heading);
        
        $link_title = $this->get('LinkTitle');
        $this->assignRef('link_title', $link_title);
        
        $display_text = $this->get('DisplayText');
        $this->assignRef('display_text', $display_text);
        
        $display_image = $this->get('DisplayImage');
        $this->assignRef('display_image', $display_image);
        
        $display_category_image = $this->get('DisplayCategoryImage');
        $this->assignRef('display_category_image', $display_category_image);
        
        $link_category_title = $this->get('LinkCategoryTitle');
        $this->assignRef('link_category_title', $link_category_title);
        
        $display_category_text = $this->get('DisplayCategoryText');
        $this->assignRef('display_category_text', $display_category_text);
        
        $page_heading = $this->get('PageHeading');
        $this->assignRef('page_heading', $page_heading);
        
        $pageclass_sfx = $this->get('PageClass');
        $this->assignRef('pageclass_sfx', $pageclass_sfx);
        
        $col_break = intval($this->get('ColumnBreak'));
        
        if($col_break < 1 || $col_break > 12 || 12 % $col_break != 0){
            $col_break = 1;
        }
        
        $this->assignRef('col_break', $col_break);
        
        $pagination = $this->get('Pagination');
        $this->assignRef('pagination', $pagination);
        
        // META DATA
        
        CrBcHelpers::setMetaData();
        
        if($this_category){
            
            $page_title = $this->get('PageTitle');
            
            CrBcHelpers::setPageTitle($page_title != '' ? $page_title . ' - ' . $this_category->title : $this_category->title);
            
            $db = JFactory::getDbo();
            $db->setQuery("SELECT parent.title, parent.id, parent.alias
                                FROM #__breezingcommerce_categories AS node,
                                        #__breezingcommerce_categories AS parent
                                WHERE node.lft BETWEEN parent.lft AND parent.rgt
                                        AND node.id = ".$db->quote($this_category->id)."
                                ORDER BY node.lft");

            $entries = $db->loadObjectList();

            if( count($entries) ){

                unset($entries[0]);
                $entries = array_merge($entries);

            }

            if( count($entries) ){

                unset($entries[count($entries)-1]);
                $entries = array_merge($entries);
            }

            foreach( $entries As $entry ){

                $translated_object = CrBcHelpers::loadTranslation($entry->id, 'category');
                if($translated_object !== null){
                    $entry->description = $translated_object->body;
                    $entry->title = $translated_object->title;
                    $entry->alias = $translated_object->alias;
                }

                $alias = $entry->alias == '' ? CrBcHelpers::getSlug($entry->title) : $entry->alias;
                $category_url = JRoute::_('index.php?option=com_breezingcommerce&controller=categories&parent_category_id='.$entry->id.'&Itemid='.CrBcHelpers::getDefaultMenuItemId().'&alias='.$alias);

                JFactory::getApplication()->getPathway()->addItem($entry->title, $category_url);
            }

            JFactory::getApplication()->getPathway()->addItem($this_category->title);
            
        }
        
        parent::display($tpl);
    }
}
