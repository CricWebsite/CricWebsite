<?php
/**
 * @version     2.0.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2020 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('JPATH_PLATFORM') or die;

/**
 * Class pkg_sellaciousInstallerScript
 *
 * @since   1.0.0
 */
class pkg_sellaciousInstallerScript
{
	/**
	 * method to run before package install
	 *
	 * @param   string                 $type
	 * @param   JInstallerAdapterFile  $installer
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.0
	 */
	public function preflight($type, $installer)
	{
		// Sellacious products cache plugin faces issue due to changed table structure. Just kill that old plugin.
		// Todo: Remove this workaround and make extended extensions not load when package versions differ
		$fileName = JPATH_PLUGINS . '/system/sellaciouscache/sellaciouscache.php';

		if (is_file($fileName))
		{
			$contents = @file_get_contents($fileName);

			if (preg_match('/@version\s+(1\.[0-4]\.\d+)/', $contents))
			{
				$text = '<?php' . PHP_EOL .
					'// This file was removed due to Sellacious v1.5.0 version incompatibility.' . PHP_EOL .
					'// Install v1.5.0 and new plugin will be here.' . PHP_EOL;

				if (!JFile::write($fileName, $text) && !JFile::delete($fileName))
				{
					// Finally, fallback to disabling the plugin. User may need to enable it later manually.
					$table = JTable::getInstance('Extension');
					$table->load(array('type' => 'plugin', 'folder' => 'system', 'element' => 'sellaciouscache'));
					$table->set('enabled', 0);
					$table->store();
				}
			}
		}
	}

	/**
	 * method to run before package install
	 *
	 * @param   string                 $type
	 * @param   JInstallerAdapterFile  $installer
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.0
	 */
	public function postflight($type, $installer)
	{
		// Delete the old version files
		if ($type == 'update')
		{
			$this->cleanupOldFiles();
		}

		JFactory::getApplication()->redirect('index.php?option=com_sellacious&view=install');
	}

	/**
	 * method to run before package uninstall
	 *
	 * @param   JInstallerAdapterPackage  $installer
	 *
	 * @throws  Exception
	 *
	 * @since   1.4.4
	 */
	public function uninstall($installer)
	{
		$table = JTable::getInstance('Extension');
		$files = $table->load(array('type' => 'file', 'element' => 'sellacious'));

		$table = JTable::getInstance('Extension');
		$lib   = $table->load(array('type' => 'library', 'element' => 'sellacious'));

		if ($files || $lib)
		{
			JLog::add('You need to uninstall "Sellacious Extended Package" first. The list has been filtered for your convenience.', JLog::WARNING, 'jerror');

			JFactory::getApplication()->redirect('index.php?option=com_installer&view=manage&filter[search]=Sellacious&filter[type]=package');
		}
	}

	/**
	 * Delete the files which existed in earlier versions but not in this version
	 *
	 * @return  void
	 *
	 * @since   1.5.2
	 */
	protected function cleanupOldFiles()
	{
		$files = array(
			'components/com_sellacious/layouts/views/categories/default_product.php',
			'components/com_sellacious/layouts/views/product/default_price.php',
			'components/com_sellacious/layouts/views/product/default_prices.php',
			'components/com_sellacious/layouts/views/product/default_variations.php',
			'components/com_sellacious/layouts/views/products/default_block.php',
			'components/com_sellacious/layouts/views/search/default.php',
			'components/com_sellacious/layouts/views/search/default.xml',
			'components/com_sellacious/layouts/views/search/default_result.php',
			'components/com_sellacious/layouts/views/search/default_results.php',
			'components/com_sellacious/layouts/views/search/finder.php',
			'components/com_sellacious/layouts/views/search/finder.xml',
			'components/com_sellacious/layouts/views/search/finder_form.php',
			'components/com_sellacious/layouts/views/search/finder_result.php',
			'components/com_sellacious/layouts/views/search/finder_results.php',
			'components/com_sellacious/layouts/views/store/default_block.php',
			'components/com_sellacious/layouts/views/wishlist/default_block.php',
			'components/com_sellacious/models/search.php',
			'components/com_sellacious/views/search/tmpl/default.xml',
			'components/com_sellacious/views/search/view.html.php',
			'media/com_sellacious/css/fe.view.search.css',
			'media/com_sellacious/js/fe.view.profile.js',
			'media/com_sellacious/js/owl.carousel.min.js',
			'media/com_sellacious/js/plugin/select2-3.5/select2-bootstrap.css',
			'media/com_sellacious/js/plugin/select2-3.5/select2.css',
			'media/com_sellacious/js/plugin/select2-3.5/select2.png',
			'media/com_sellacious/js/plugin/select2-3.5/select2x2.png',
			'media/com_sellacious/js/util.rollover.js',
			'modules/mod_usercurrency/tmpl/default.php',
			'modules/mod_usercurrency/language/en-GB/en-GB.mod_usercurrency.ini',
			'modules/mod_usercurrency/language/en-GB/en-GB.mod_usercurrency.sys.ini',
			'modules/mod_usercurrency/mod_usercurrency.php',
			'media/mod_usercurrency/css/style.css',
			'media/mod_usercurrency/js/default.js',
			'modules/mod_usercurrency/mod_usercurrency.xml',
			'modules/mod_sellacious_cart/mod_sellacious_cart.php',
			'modules/mod_sellacious_cart/language/en-GB/en-GB.mod_sellacious_cart.ini',
			'modules/mod_sellacious_cart/language/en-GB/en-GB.mod_sellacious_cart.sys.ini',
			'modules/mod_sellacious_cart/tmpl/default.php',
			'media/mod_sellacious_cart/css/style.css',
			'media/mod_sellacious_cart/js/show-cart.js',
			'modules/mod_sellacious_cart/mod_sellacious_cart.xml',
			'modules/mod_sellacious_filters/mod_sellacious_filters.php',
			'modules/mod_sellacious_filters/helper.php',
			'modules/mod_sellacious_filters/language/en-GB/en-GB.mod_sellacious_filters.ini',
			'modules/mod_sellacious_filters/language/en-GB/en-GB.mod_sellacious_filters.sys.ini',
			'modules/mod_sellacious_filters/language/index.html',
			'modules/mod_sellacious_filters/tmpl/default.php',
			'modules/mod_sellacious_filters/tmpl/default_level.php',
			'media/mod_sellacious_filters/css/filters.css',
			'media/mod_sellacious_filters/less/filters.less',
			'media/mod_sellacious_filters/js/filters.js',
			'media/mod_sellacious_filters/js/jquery.treeview.js',
			'modules/mod_sellacious_filters/mod_sellacious_filters.xml',
			'modules/mod_sellacious_finder/mod_sellacious_finder.php',
			'modules/mod_sellacious_finder/language/en-GB/en-GB.mod_sellacious_finder.ini',
			'modules/mod_sellacious_finder/language/en-GB/en-GB.mod_sellacious_finder.sys.ini',
			'modules/mod_sellacious_finder/language/index.html',
			'modules/mod_sellacious_finder/tmpl/default.php',
			'modules/mod_sellacious_finder/tmpl/dropdown.php',
			'modules/mod_sellacious_finder/tmpl/expand.php',
			'modules/mod_sellacious_finder/tmpl/overlay.php',
			'media/mod_sellacious_finder/css/dropdown.css',
			'media/mod_sellacious_finder/css/expand.css',
			'media/mod_sellacious_finder/css/overlay.css',
			'media/mod_sellacious_finder/css/template.css',
			'modules/mod_sellacious_finder/mod_sellacious_finder.xml',
			'modules/mod_sellacious_latestproducts/mod_sellacious_latestproducts.php',
			'modules/mod_sellacious_latestproducts/helper.php',
			'modules/mod_sellacious_latestproducts/language/en-GB/en-GB.mod_sellacious_latestproducts.ini',
			'modules/mod_sellacious_latestproducts/language/en-GB/en-GB.mod_sellacious_latestproducts.sys.ini',
			'modules/mod_sellacious_latestproducts/tmpl/carousel.php',
			'modules/mod_sellacious_latestproducts/tmpl/default.php',
			'modules/mod_sellacious_latestproducts/tmpl/grid.php',
			'modules/mod_sellacious_latestproducts/tmpl/list.php',
			'modules/mod_sellacious_latestproducts/assets/css/owl.carousel.min.css',
			'modules/mod_sellacious_latestproducts/assets/js/owl.carousel.js',
			'media/mod_sellacious_latestproducts/css/style.css',
			'modules/mod_sellacious_latestproducts/mod_sellacious_latestproducts.xml',
			'modules/mod_sellacious_relatedproducts/mod_sellacious_relatedproducts.php',
			'modules/mod_sellacious_relatedproducts/helper.php',
			'modules/mod_sellacious_relatedproducts/language/en-GB/en-GB.mod_sellacious_relatedproducts.ini',
			'modules/mod_sellacious_relatedproducts/language/en-GB/en-GB.mod_sellacious_relatedproducts.sys.ini',
			'modules/mod_sellacious_relatedproducts/tmpl/carousel.php',
			'modules/mod_sellacious_relatedproducts/tmpl/default.php',
			'modules/mod_sellacious_relatedproducts/tmpl/grid.php',
			'modules/mod_sellacious_relatedproducts/tmpl/list.php',
			'modules/mod_sellacious_relatedproducts/assets/css/owl.carousel.min.css',
			'modules/mod_sellacious_relatedproducts/assets/js/owl.carousel.js',
			'media/mod_sellacious_relatedproducts/css/style.css',
			'modules/mod_sellacious_relatedproducts/mod_sellacious_relatedproducts.xml',
			'modules/mod_sellacious_specialcatsproducts/mod_sellacious_specialcatsproducts.php',
			'modules/mod_sellacious_specialcatsproducts/helper.php',
			'modules/mod_sellacious_specialcatsproducts/language/en-GB/en-GB.mod_sellacious_specialcatsproducts.ini',
			'modules/mod_sellacious_specialcatsproducts/language/en-GB/en-GB.mod_sellacious_specialcatsproducts.sys.ini',
			'modules/mod_sellacious_specialcatsproducts/tmpl/carousel.php',
			'modules/mod_sellacious_specialcatsproducts/tmpl/default.php',
			'modules/mod_sellacious_specialcatsproducts/tmpl/grid.php',
			'modules/mod_sellacious_specialcatsproducts/tmpl/list.php',
			'modules/mod_sellacious_specialcatsproducts/assets/css/owl.carousel.min.css',
			'modules/mod_sellacious_specialcatsproducts/assets/js/owl.carousel.js',
			'media/mod_sellacious_specialcatsproducts/css/style.css',
			'modules/mod_sellacious_specialcatsproducts/mod_sellacious_specialcatsproducts.xml',
			'media/mod_usercurrency/js/jquery.jqtransform.js',
			'media/mod_usercurrency/img/select_left.gif',
			'media/mod_usercurrency/img/select_right.gif',
			'modules/mod_sellacious_stores/mod_sellacious_stores.php',
			'modules/mod_sellacious_stores/helper.php',
			'modules/mod_sellacious_stores/language/en-GB/en-GB.mod_sellacious_stores.ini',
			'modules/mod_sellacious_stores/language/en-GB/en-GB.mod_sellacious_stores.sys.ini',
			'modules/mod_sellacious_stores/tmpl/carousel.php',
			'modules/mod_sellacious_stores/tmpl/default.php',
			'modules/mod_sellacious_stores/tmpl/grid.php',
			'modules/mod_sellacious_stores/tmpl/list.php',
			'modules/mod_sellacious_stores/assets/css/owl.carousel.min.css',
			'modules/mod_sellacious_stores/assets/js/owl.carousel.js',
			'media/mod_sellacious_stores/css/style.css',
			'modules/mod_sellacious_stores/mod_sellacious_stores.xml',
			'components/com_sellacious/layouts/views/profile/default_banking.php',
			'components/com_sellacious/tables/geolocation.php',
			'media/com_sellacious/css/field.mapaddress.css',
			'media/com_sellacious/js/field.geolocation.js',
			'media/com_sellacious/js/field.shopruleslabs.js',
			'media/com_sellacious/js/plugin/ckeditor/CHANGES.md',
			'media/com_sellacious/js/plugin/ckeditor/LICENSE.md',
			'media/com_sellacious/js/plugin/ckeditor/README.md',
			'media/com_sellacious/js/plugin/ckeditor/adapters/jquery.js',
			'media/com_sellacious/js/plugin/ckeditor/build-config.js',
			'media/com_sellacious/js/plugin/ckeditor/ckeditor.js',
			'media/com_sellacious/js/plugin/ckeditor/config.js',
			'media/com_sellacious/js/plugin/ckeditor/contents.css',
			'media/com_sellacious/js/plugin/ckeditor/lang/en.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/a11yhelp.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/_translationstatus.txt',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/af.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/ar.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/az.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/bg.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/ca.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/cs.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/cy.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/da.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/de-ch.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/de.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/el.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/en-au.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/en-gb.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/en.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/eo.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/es-mx.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/es.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/et.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/eu.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/fa.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/fi.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/fo.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/fr-ca.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/fr.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/gl.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/gu.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/he.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/hi.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/hr.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/hu.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/id.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/it.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/ja.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/km.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/ko.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/ku.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/lt.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/lv.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/mk.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/mn.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/nb.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/nl.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/no.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/oc.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/pl.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/pt-br.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/pt.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/ro.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/ru.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/si.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/sk.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/sl.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/sq.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/sr-latn.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/sr.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/sv.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/th.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/tr.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/tt.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/ug.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/uk.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/vi.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/zh-cn.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/a11yhelp/dialogs/lang/zh.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/about/dialogs/about.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/about/dialogs/hidpi/logo_ckeditor.png',
			'media/com_sellacious/js/plugin/ckeditor/plugins/about/dialogs/logo_ckeditor.png',
			'media/com_sellacious/js/plugin/ckeditor/plugins/clipboard/dialogs/paste.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/dialog/dialogDefinition.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/icons.png',
			'media/com_sellacious/js/plugin/ckeditor/plugins/icons_hidpi.png',
			'media/com_sellacious/js/plugin/ckeditor/plugins/image/dialogs/image.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/image/images/noimage.png',
			'media/com_sellacious/js/plugin/ckeditor/plugins/link/dialogs/anchor.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/link/dialogs/link.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/link/images/anchor.png',
			'media/com_sellacious/js/plugin/ckeditor/plugins/link/images/hidpi/anchor.png',
			'media/com_sellacious/js/plugin/ckeditor/plugins/magicline/images/hidpi/icon-rtl.png',
			'media/com_sellacious/js/plugin/ckeditor/plugins/magicline/images/hidpi/icon.png',
			'media/com_sellacious/js/plugin/ckeditor/plugins/magicline/images/icon-rtl.png',
			'media/com_sellacious/js/plugin/ckeditor/plugins/magicline/images/icon.png',
			'media/com_sellacious/js/plugin/ckeditor/plugins/pastefromword/filter/default.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/scayt/CHANGELOG.md',
			'media/com_sellacious/js/plugin/ckeditor/plugins/scayt/LICENSE.md',
			'media/com_sellacious/js/plugin/ckeditor/plugins/scayt/README.md',
			'media/com_sellacious/js/plugin/ckeditor/plugins/scayt/dialogs/dialog.css',
			'media/com_sellacious/js/plugin/ckeditor/plugins/scayt/dialogs/options.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/scayt/dialogs/toolbar.css',
			'media/com_sellacious/js/plugin/ckeditor/plugins/scayt/skins/moono-lisa/scayt.css',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/dialogs/sourcedialog.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/icons/hidpi/sourcedialog-rtl.png',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/icons/hidpi/sourcedialog.png',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/icons/sourcedialog-rtl.png',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/icons/sourcedialog.png',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/af.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/ar.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/az.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/bg.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/bn.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/bs.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/ca.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/cs.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/cy.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/da.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/de-ch.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/de.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/el.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/en-au.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/en-ca.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/en-gb.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/en.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/eo.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/es-mx.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/es.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/et.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/eu.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/fa.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/fi.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/fo.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/fr-ca.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/fr.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/gl.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/gu.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/he.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/hi.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/hr.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/hu.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/id.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/is.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/it.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/ja.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/ka.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/km.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/ko.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/ku.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/lt.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/lv.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/mn.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/ms.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/nb.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/nl.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/no.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/oc.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/pl.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/pt-br.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/pt.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/ro.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/ru.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/si.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/sk.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/sl.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/sq.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/sr-latn.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/sr.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/sv.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/th.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/tr.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/tt.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/ug.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/uk.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/vi.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/zh-cn.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/lang/zh.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/plugin.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/sourcedialog/samples/sourcedialog.html',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/_translationstatus.txt',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/af.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/ar.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/az.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/bg.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/ca.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/cs.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/cy.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/da.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/de-ch.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/de.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/el.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/en-au.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/en-ca.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/en-gb.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/en.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/eo.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/es-mx.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/es.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/et.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/eu.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/fa.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/fi.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/fr-ca.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/fr.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/gl.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/he.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/hr.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/hu.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/id.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/it.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/ja.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/km.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/ko.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/ku.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/lt.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/lv.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/nb.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/nl.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/no.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/oc.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/pl.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/pt-br.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/pt.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/ro.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/ru.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/si.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/sk.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/sl.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/sq.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/sv.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/th.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/tr.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/tt.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/ug.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/uk.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/vi.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/zh-cn.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/lang/zh.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/specialchar/dialogs/specialchar.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/table/dialogs/table.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/tableselection/styles/tableselection.css',
			'media/com_sellacious/js/plugin/ckeditor/plugins/tabletools/dialogs/tableCell.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/widget/images/handle.png',
			'media/com_sellacious/js/plugin/ckeditor/plugins/wsc/LICENSE.md',
			'media/com_sellacious/js/plugin/ckeditor/plugins/wsc/README.md',
			'media/com_sellacious/js/plugin/ckeditor/plugins/wsc/dialogs/ciframe.html',
			'media/com_sellacious/js/plugin/ckeditor/plugins/wsc/dialogs/tmpFrameset.html',
			'media/com_sellacious/js/plugin/ckeditor/plugins/wsc/dialogs/wsc.css',
			'media/com_sellacious/js/plugin/ckeditor/plugins/wsc/dialogs/wsc.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/wsc/dialogs/wsc_ie.js',
			'media/com_sellacious/js/plugin/ckeditor/plugins/wsc/skins/moono-lisa/wsc.css',
			'media/com_sellacious/js/plugin/ckeditor/samples/css/samples.css',
			'media/com_sellacious/js/plugin/ckeditor/samples/img/github-top.png',
			'media/com_sellacious/js/plugin/ckeditor/samples/img/header-bg.png',
			'media/com_sellacious/js/plugin/ckeditor/samples/img/header-separator.png',
			'media/com_sellacious/js/plugin/ckeditor/samples/img/logo.png',
			'media/com_sellacious/js/plugin/ckeditor/samples/img/logo.svg',
			'media/com_sellacious/js/plugin/ckeditor/samples/img/navigation-tip.png',
			'media/com_sellacious/js/plugin/ckeditor/samples/index.html',
			'media/com_sellacious/js/plugin/ckeditor/samples/js/sample.js',
			'media/com_sellacious/js/plugin/ckeditor/samples/js/sf.js',
			'media/com_sellacious/js/plugin/ckeditor/samples/old/ajax.html',
			'media/com_sellacious/js/plugin/ckeditor/samples/old/api.html',
			'media/com_sellacious/js/plugin/ckeditor/samples/old/appendto.html',
			'media/com_sellacious/js/plugin/ckeditor/samples/old/assets/inlineall/logo.png',
			'media/com_sellacious/js/plugin/ckeditor/samples/old/assets/outputxhtml/outputxhtml.css',
			'media/com_sellacious/js/plugin/ckeditor/samples/old/assets/posteddata.php',
			'media/com_sellacious/js/plugin/ckeditor/samples/old/assets/sample.jpg',
			'media/com_sellacious/js/plugin/ckeditor/samples/old/assets/uilanguages/languages.js',
			'media/com_sellacious/js/plugin/ckeditor/samples/old/datafiltering.html',
			'media/com_sellacious/js/plugin/ckeditor/samples/old/dialog/assets/my_dialog.js',
			'media/com_sellacious/js/plugin/ckeditor/samples/old/dialog/dialog.html',
			'media/com_sellacious/js/plugin/ckeditor/samples/old/divreplace.html',
			'media/com_sellacious/js/plugin/ckeditor/samples/old/enterkey/enterkey.html',
			'media/com_sellacious/js/plugin/ckeditor/samples/old/htmlwriter/assets/outputforflash/outputforflash.fla',
			'media/com_sellacious/js/plugin/ckeditor/samples/old/htmlwriter/assets/outputforflash/outputforflash.swf',
			'media/com_sellacious/js/plugin/ckeditor/samples/old/htmlwriter/assets/outputforflash/swfobject.js',
			'media/com_sellacious/js/plugin/ckeditor/samples/old/htmlwriter/outputforflash.html',
			'media/com_sellacious/js/plugin/ckeditor/samples/old/htmlwriter/outputhtml.html',
			'media/com_sellacious/js/plugin/ckeditor/samples/old/index.html',
			'media/com_sellacious/js/plugin/ckeditor/samples/old/inlineall.html',
			'media/com_sellacious/js/plugin/ckeditor/samples/old/inlinebycode.html',
			'media/com_sellacious/js/plugin/ckeditor/samples/old/inlinetextarea.html',
			'media/com_sellacious/js/plugin/ckeditor/samples/old/jquery.html',
			'media/com_sellacious/js/plugin/ckeditor/samples/old/magicline/magicline.html',
			'media/com_sellacious/js/plugin/ckeditor/samples/old/readonly.html',
			'media/com_sellacious/js/plugin/ckeditor/samples/old/replacebyclass.html',
			'media/com_sellacious/js/plugin/ckeditor/samples/old/replacebycode.html',
			'media/com_sellacious/js/plugin/ckeditor/samples/old/sample.css',
			'media/com_sellacious/js/plugin/ckeditor/samples/old/sample.js',
			'media/com_sellacious/js/plugin/ckeditor/samples/old/sample_posteddata.php',
			'media/com_sellacious/js/plugin/ckeditor/samples/old/tabindex.html',
			'media/com_sellacious/js/plugin/ckeditor/samples/old/toolbar/toolbar.html',
			'media/com_sellacious/js/plugin/ckeditor/samples/old/uicolor.html',
			'media/com_sellacious/js/plugin/ckeditor/samples/old/uilanguages.html',
			'media/com_sellacious/js/plugin/ckeditor/samples/old/wysiwygarea/fullpage.html',
			'media/com_sellacious/js/plugin/ckeditor/samples/old/xhtmlstyle.html',
			'media/com_sellacious/js/plugin/ckeditor/samples/toolbarconfigurator/css/fontello.css',
			'media/com_sellacious/js/plugin/ckeditor/samples/toolbarconfigurator/font/LICENSE.txt',
			'media/com_sellacious/js/plugin/ckeditor/samples/toolbarconfigurator/font/config.json',
			'media/com_sellacious/js/plugin/ckeditor/samples/toolbarconfigurator/font/fontello.eot',
			'media/com_sellacious/js/plugin/ckeditor/samples/toolbarconfigurator/font/fontello.svg',
			'media/com_sellacious/js/plugin/ckeditor/samples/toolbarconfigurator/font/fontello.ttf',
			'media/com_sellacious/js/plugin/ckeditor/samples/toolbarconfigurator/font/fontello.woff',
			'media/com_sellacious/js/plugin/ckeditor/samples/toolbarconfigurator/index.html',
			'media/com_sellacious/js/plugin/ckeditor/samples/toolbarconfigurator/js/abstracttoolbarmodifier.js',
			'media/com_sellacious/js/plugin/ckeditor/samples/toolbarconfigurator/js/fulltoolbareditor.js',
			'media/com_sellacious/js/plugin/ckeditor/samples/toolbarconfigurator/js/toolbarmodifier.js',
			'media/com_sellacious/js/plugin/ckeditor/samples/toolbarconfigurator/js/toolbartextmodifier.js',
			'media/com_sellacious/js/plugin/ckeditor/samples/toolbarconfigurator/lib/codemirror/LICENSE',
			'media/com_sellacious/js/plugin/ckeditor/samples/toolbarconfigurator/lib/codemirror/codemirror.css',
			'media/com_sellacious/js/plugin/ckeditor/samples/toolbarconfigurator/lib/codemirror/codemirror.js',
			'media/com_sellacious/js/plugin/ckeditor/samples/toolbarconfigurator/lib/codemirror/javascript.js',
			'media/com_sellacious/js/plugin/ckeditor/samples/toolbarconfigurator/lib/codemirror/neo.css',
			'media/com_sellacious/js/plugin/ckeditor/samples/toolbarconfigurator/lib/codemirror/show-hint.css',
			'media/com_sellacious/js/plugin/ckeditor/samples/toolbarconfigurator/lib/codemirror/show-hint.js',
			'media/com_sellacious/js/plugin/ckeditor/skins/moono-lisa/dialog.css',
			'media/com_sellacious/js/plugin/ckeditor/skins/moono-lisa/dialog_ie.css',
			'media/com_sellacious/js/plugin/ckeditor/skins/moono-lisa/dialog_ie8.css',
			'media/com_sellacious/js/plugin/ckeditor/skins/moono-lisa/dialog_iequirks.css',
			'media/com_sellacious/js/plugin/ckeditor/skins/moono-lisa/editor.css',
			'media/com_sellacious/js/plugin/ckeditor/skins/moono-lisa/editor_gecko.css',
			'media/com_sellacious/js/plugin/ckeditor/skins/moono-lisa/editor_ie.css',
			'media/com_sellacious/js/plugin/ckeditor/skins/moono-lisa/editor_ie8.css',
			'media/com_sellacious/js/plugin/ckeditor/skins/moono-lisa/editor_iequirks.css',
			'media/com_sellacious/js/plugin/ckeditor/skins/moono-lisa/icons.png',
			'media/com_sellacious/js/plugin/ckeditor/skins/moono-lisa/icons_hidpi.png',
			'media/com_sellacious/js/plugin/ckeditor/skins/moono-lisa/images/arrow.png',
			'media/com_sellacious/js/plugin/ckeditor/skins/moono-lisa/images/close.png',
			'media/com_sellacious/js/plugin/ckeditor/skins/moono-lisa/images/hidpi/close.png',
			'media/com_sellacious/js/plugin/ckeditor/skins/moono-lisa/images/hidpi/lock-open.png',
			'media/com_sellacious/js/plugin/ckeditor/skins/moono-lisa/images/hidpi/lock.png',
			'media/com_sellacious/js/plugin/ckeditor/skins/moono-lisa/images/hidpi/refresh.png',
			'media/com_sellacious/js/plugin/ckeditor/skins/moono-lisa/images/lock-open.png',
			'media/com_sellacious/js/plugin/ckeditor/skins/moono-lisa/images/lock.png',
			'media/com_sellacious/js/plugin/ckeditor/skins/moono-lisa/images/refresh.png',
			'media/com_sellacious/js/plugin/ckeditor/skins/moono-lisa/images/spinner.gif',
			'media/com_sellacious/js/plugin/ckeditor/skins/moono-lisa/readme.md',
			'media/com_sellacious/js/plugin/ckeditor/styles.js',
			'media/com_sellacious/js/view.shoprule.js'
		);

		foreach ($files as $file)
		{
			JFile::delete(JPATH_ROOT . '/' . $file);
		}
	}
}
