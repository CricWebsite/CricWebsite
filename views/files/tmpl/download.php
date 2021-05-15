<?php
/**
 * @package     BreezingCommerce
 * @author      Markus Bopp
 * @link        http://www.crosstec.de
 * @license     GNU/GPL
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'CrBcFile.php');

$db = JFactory::getDbo();
$db->setQuery("Select verification_download_tries From #__breezingcommerce_files Where id = " . $db->quote($this->file->id));
$allowed_tries = $db->loadResult();

if( $allowed_tries > 0 && $this->file->download_tries >= $allowed_tries ){

    JFactory::getApplication()->enqueueMessage(JText::_('COM_BREEZINGCOMMERCE_MAXIMUM_DOWNLOADS_MESSAGE'), 'error');
    JFactory::getApplication()->redirect('index.php');
}

CrBcFile::downloadFile($this->file->id, false, $this->file->order_item_file_id);


