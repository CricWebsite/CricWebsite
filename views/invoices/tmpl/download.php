<?php
/**
 * @package     BreezingCommerce
 * @author      Markus Bopp
 * @link        http://www.crosstec.de
 * @license     GNU/GPL
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

ob_end_clean();

$filename = $this->invoice;

header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: public", false);
header("Content-Description: File Transfer");
header('Content-Type: application/octet-stream');
header("Content-Disposition: attachment; filename=\"" . basename($filename) . "\";");
header("Content-Transfer-Encoding: binary");
header("Content-Length: " . filesize($filename));

$chunksize = 1*(1024*1024); // how many bytes per chunk
$buffer = '';
$handle = @fopen($filename, 'rb');
if ($handle === false) {
    JError::raiseError( 404, JText::_( 'COM_BREEZINGCOMMERCE_FILE_NOT_FOUND_OR_NOT_PUBLISHED' ) );
}
while (!@feof($handle)) {
    $buffer = @fread($handle, $chunksize);
    print $buffer;
}
@fclose($handle);

exit;



