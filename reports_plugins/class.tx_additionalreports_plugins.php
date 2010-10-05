<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009-2010 Francois Suter <francois@typo3.org>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/


/**
 * This class provides a report displaying a list of all installed services
 * Code inspired by EXT:dam/lib/class.tx_dam_svlist.php by Rene Fritz
 *
 * @author		Francois Suter <francois@typo3.org>
 * @package		TYPO3
 * @subpackage	sv
 *
 * $Id: class.tx_sv_reports_serviceslist.php 7905 2010-06-13 14:42:33Z ohader $
 */
class tx_additionalreports_plugins implements tx_reports_Report {
	/**
	 * Back-reference to the calling reports module
	 *
	 * @var	tx_reports_Module	$reportObject
	 */
	protected $reportObject;

	/**
	 * Constructor for class tx_sv_reports_ServicesList
	 *
	 * @param	tx_reports_Module	Back-reference to the calling reports module
	 */
	 
	public function __construct(tx_reports_Module $reportObject) {
		$this->reportObject = $reportObject;
		$GLOBALS['LANG']->includeLLFile('EXT:additional_reports/locallang.xml');
	}

	/**
	 * This method renders the report
	 *
	 * @return	string	The status report as HTML
	 */
	 
	public function getReport() {
		$content = '';

		// Add custom stylesheet
		$this->reportObject->doc->getPageRenderer()->addCssFile(t3lib_extMgm::extRelPath('additional_reports') . 'tx_additionalreports.css');
		$content .= '<p class="help">'.$GLOBALS['LANG']->getLL('plugins_description').'</p>';
		$content .= $this->displayPlugins();

		return $content;
	}
	 
	protected function displayPlugins() {
		$display = t3lib_div::_GP('display');
			
		$content = '<h3 class="uppercase">'.$GLOBALS['LANG']->getLL('pluginschoose').'</h3>';
		$content .= '<form method="post" name="formplugins">';
		$content .= '<input style="margin-right:4px;" type="radio" name="display" value="1" id="radio1"'.(($display==1||$display==null) ? ' checked="checked"' : '').'/><label for="radio1" style="margin-right:10px;">'.$GLOBALS['LANG']->getLL('pluginsmode1').'</label>';
		$content .= '<input style="margin-right:4px;" type="radio" name="display" value="2" id="radio2"'.(($display==2) ? ' checked="checked"' : '').'/><label for="radio2" style="margin-right:10px;">'.$GLOBALS['LANG']->getLL('pluginsmode2').'</label>';
		$content .= '<input style="margin-right:4px;" type="radio" name="display" value="4" id="radio4"'.(($display==4) ? ' checked="checked"' : '').'/><label for="radio4" style="margin-right:10px;">'.$GLOBALS['LANG']->getLL('pluginsmode4').'</label>';
		$content .= '<input style="margin-right:4px;" type="radio" name="display" value="3" id="radio3"'.(($display==3) ? ' checked="checked"' : '').'/><label for="radio3" style="margin-right:10px;">'.$GLOBALS['LANG']->getLL('pluginsmode3').'</label>';
		$content .= '<input style="margin-right:4px;" type="radio" name="display" value="5" id="radio5"'.(($display==5) ? ' checked="checked"' : '').'/><label for="radio5" style="margin-right:10px;">'.$GLOBALS['LANG']->getLL('pluginsmode5').'</label>';
		$content .= '<input type="submit" name="submit" value="'.$GLOBALS['LANG']->getLL('pluginssubmit').'"/>';
		$content .= '</form>';
	
		$content .= $this->reportObject->doc->spacer(20);
		
		switch ($display) {
			case 1 : $content .= $this->getAllPlugins(); break;
			case 2 : $content .= $this->getAllCType();; break;
			case 3 : $content .= $this->getAllUsedCType(); break;
			case 4 : $content .= $this->getAllUsedPlugins(); break;
			case 5 : $content .= $this->getSummary(); break;
			default : $content .= $this->getAllPlugins(); break;
		}

		return $content;
	}
	
	function getAllPlugins () {
		$content = '';
		$content .= '<table cellspacing="1" cellpadding="2" border="0" class="tx_sv_reportlist">';
		$content .= '<tr class="bgColor2">';
		$content .= '<td class="cell"></td>';
		$content .= '<td class="cell">'.$GLOBALS['LANG']->getLL('extension').'</td>';
		$content .= '<td class="cell">'.$GLOBALS['LANG']->getLL('plugin').'</td>';
		$content .= '<td class="cell">'.$GLOBALS['LANG']->getLL('eminfo').'</td>';
		$content .= '<td class="cell">'.$GLOBALS['LANG']->getLL('used').'</td>';
		$content .= '</tr>';
		foreach ($GLOBALS['TCA']['tt_content']['columns']['list_type']['config']['items'] as $itemKey => $itemValue) {
			if (trim($itemValue[1])!='') {
				preg_match('/EXT:(.*?)\//', $itemValue[0],$ext);
				preg_match('/^LLL:(EXT:.*?):(.*)/', $itemValue[0],$llfile);
				$LOCAL_LANG = t3lib_div::readLLfile($llfile[1],$GLOBALS['LANG']->lang);
				$content .= '<tr class="bgColor3-20">';
				$content .= '<td class="cell" align="center"><img src="' . $itemValue[2] . '"/></td>';
				$content .= '<td class="cell">' . $ext[1] . '</td>';
				$content .= '<td class="cell">' . $GLOBALS['LANG']->getLLL($llfile[2],$LOCAL_LANG). ' ('. $itemValue[1] . ')</td>';
				$content .= '<td class="cell"><a href="/typo3/mod/tools/em/index.php?CMD[showExt]=' . $itemValue[1] . '&SET[singleDetails]=info">'.$GLOBALS['LANG']->getLL('emlink').'</a></td>';
				
				$items = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('DISTINCT tt_content.list_type,tt_content.pid,pages.title','tt_content,pages','tt_content.pid=pages.uid AND tt_content.hidden=0 AND tt_content.deleted=0 AND pages.hidden=0 AND pages.deleted=0 AND tt_content.CType=\'list\' AND tt_content.list_type=\''.$itemValue[1].'\'','','tt_content.list_type');
				
				if (count($items)>0) {
					$content .= '<td class="cell typo3-message message-ok">'.$GLOBALS['LANG']->getLL('yes').'</td>';
				} else {
					$content .= '<td class="cell typo3-message message-error">'.$GLOBALS['LANG']->getLL('no').'</td>';
				}
				
				$content .= '</tr>';
			}
		}
		$content .= '</table>';
		return $content;
	}
	
	function getAllCType () {
		$content = '';
		$content .= '<table cellspacing="1" cellpadding="2" border="0" class="tx_sv_reportlist">';
		$content .= '<tr class="bgColor2">';
		$content .= '<td class="cell"></td>';
		$content .= '<td class="cell">'.$GLOBALS['LANG']->getLL('ctype').'</td>';
		$content .= '<td class="cell">'.$GLOBALS['LANG']->getLL('used').'</td>';
		$content .= '</tr>';
	
		foreach ($GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] as $itemKey => $itemValue) {
			if ($itemValue[1]!='--div--') {
				preg_match('/^LLL:(EXT:.*?):(.*)/', $itemValue[0],$llfile);
				$LOCAL_LANG = t3lib_div::readLLfile($llfile[1],$GLOBALS['LANG']->lang);
				$content .= '<tr class="bgColor3-20">';
				$content .= '<td class="cell" align="center">';
				if ($itemValue[2]!=''&&is_file(PATH_site.'/typo3/sysext/t3skin/icons/gfx/' . $itemValue[2])) {
					$content .= '<img src="/typo3/sysext/t3skin/icons/gfx/' . $itemValue[2] . '"/>';
				}
				$content .= '</td>';
				$content .= '<td class="cell">' . $GLOBALS['LANG']->getLLL($llfile[2],$LOCAL_LANG) .' (' . $itemValue[1] . ')</td>';
				
				$items = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('DISTINCT tt_content.CType,tt_content.pid,pages.title','tt_content,pages','tt_content.pid=pages.uid AND tt_content.hidden=0 AND tt_content.deleted=0 AND pages.hidden=0 AND pages.deleted=0 AND tt_content.CType=\''.$itemValue[1].'\'','','tt_content.CType');
					
				if (count($items)>0) {
					$content .= '<td class="cell typo3-message message-ok">'.$GLOBALS['LANG']->getLL('yes').'</td>';
				} else {
					$content .= '<td class="cell typo3-message message-error">'.$GLOBALS['LANG']->getLL('no').'</td>';
				}
					
				$content .= '</tr>';
			}
		}
		$content .= '</table>';
		return $content;
	}
	
	function getAllUsedCType () {
		$ctypes = array();
		foreach ($GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] as $itemKey => $itemValue) {
			if ($itemValue[1]!='--div--') {
				$ctypes[$itemValue[1]] = $itemValue;
			}
		}
	
		$items = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('DISTINCT tt_content.CType,tt_content.pid,pages.title','tt_content,pages','tt_content.pid=pages.uid AND tt_content.hidden=0 AND tt_content.deleted=0 AND pages.hidden=0 AND pages.deleted=0 AND tt_content.CType<>\'list\'','','tt_content.CType,tt_content.pid');
		$content = '';
		$content .= '<table cellspacing="1" cellpadding="2" border="0" class="tx_sv_reportlist">';
		$content .= '<tr class="bgColor2">';
		$content .= '<td class="cell"></td>';
		$content .= '<td class="cell">'.$GLOBALS['LANG']->getLL('ctype').'</td>';
		$content .= '<td class="cell">'.$GLOBALS['LANG']->getLL('pid').'</td>';
		$content .= '<td class="cell">'.$GLOBALS['LANG']->getLL('pagetitle').'</td>';
		$content .= '</tr>';
		foreach ($items as $itemKey => $itemValue) {
			preg_match('/^LLL:(EXT:.*?):(.*)/', $ctypes[$itemValue['CType']][0],$llfile);
			$LOCAL_LANG = t3lib_div::readLLfile($llfile[1],$GLOBALS['LANG']->lang);
			$content .= '<tr class="bgColor3-20">';
			$content .= '<td class="cell" align="center">';
			if (is_file(PATH_site.'/typo3/sysext/t3skin/icons/gfx/' . $ctypes[$itemValue['CType']][2])) {
				$content .= '<img src="/typo3/sysext/t3skin/icons/gfx/' . $ctypes[$itemValue['CType']][2] . '"/>';
			}
			$content .= '</td>';
			$content .= '<td class="cell">' . $GLOBALS['LANG']->getLLL($llfile[2],$LOCAL_LANG) . ' (' . $itemValue['CType'] . ')</td>';
			$content .= '<td class="cell">' . $itemValue['pid'] . '</td>';
			$content .= '<td class="cell"><a href="/typo3/db_list.php?id='.$itemValue['pid'].'">' . $itemValue['title'] . '</a></td>';
			$content .= '</tr>';
		
		}
		$content .= '</table>';
		return $content;
	}
	
	function getAllUsedPlugins () {
		$plugins = array();
		foreach ($GLOBALS['TCA']['tt_content']['columns']['list_type']['config']['items'] as $itemKey => $itemValue) {
			if (trim($itemValue[1])!='') {
				$plugins[$itemValue[1]] = $itemValue;
			}
		}
		$items = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('DISTINCT tt_content.list_type,tt_content.pid,pages.title','tt_content,pages','tt_content.pid=pages.uid AND tt_content.hidden=0 AND tt_content.deleted=0 AND pages.hidden=0 AND pages.deleted=0 AND tt_content.CType=\'list\'','','tt_content.list_type,tt_content.pid');
		$content = '';
		$content .= '<table cellspacing="1" cellpadding="2" border="0" class="tx_sv_reportlist">';
		$content .= '<tr class="bgColor2">';
		$content .= '<td class="cell"></td>';
		$content .= '<td class="cell">'.$GLOBALS['LANG']->getLL('extension').'</td>';
		$content .= '<td class="cell">'.$GLOBALS['LANG']->getLL('plugin').'</td>';
		$content .= '<td class="cell">'.$GLOBALS['LANG']->getLL('pid').'</td>';
		$content .= '<td class="cell">'.$GLOBALS['LANG']->getLL('pagetitle').'</td>';
		$content .= '</tr>';
		foreach ($items as $itemKey => $itemValue) {
			preg_match('/EXT:(.*?)\//', $plugins[$itemValue['list_type']][0],$ext);
			preg_match('/^LLL:(EXT:.*?):(.*)/', $plugins[$itemValue['list_type']][0],$llfile);
			$LOCAL_LANG = t3lib_div::readLLfile($llfile[1],$GLOBALS['LANG']->lang);
			$content .= '<tr class="bgColor3-20">';
			$content .= '<td class="cell" align="center"><img src="' . $plugins[$itemValue['list_type']][2] . '"/></td>';
			$content .= '<td class="cell">' . $ext[1] . '</td>';
			$content .= '<td class="cell">' . $GLOBALS['LANG']->getLLL($llfile[2],$LOCAL_LANG). ' ('. $itemValue['list_type'] . ')</td>';
			$content .= '<td class="cell">' . $itemValue['pid'] . '</td>';
			$content .= '<td class="cell"><a href="/typo3/db_list.php?id='.$itemValue['pid'].'">' . $itemValue['title'] . '</a></td>';
			$content .= '</tr>';
		
		}
		$content .= '</table>';
		return $content;
	}
	
	function getSummary () {
		$plugins = array();
		foreach ($GLOBALS['TCA']['tt_content']['columns']['list_type']['config']['items'] as $itemKey => $itemValue) {
			if (trim($itemValue[1])!='') {
				$plugins[$itemValue[1]] = $itemValue;
			}
		}
		
		$ctypes = array();
		foreach ($GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] as $itemKey => $itemValue) {
			if ($itemValue[1]!='--div--') {
				$ctypes[$itemValue[1]] = $itemValue;
			}
		}
		
		$itemsCount = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('COUNT( tt_content.uid ) as "nb"','tt_content,pages','tt_content.pid=pages.uid AND tt_content.hidden=0 AND tt_content.deleted=0 AND pages.hidden=0 AND pages.deleted=0');
		$items = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('tt_content.CType,tt_content.list_type,count(*) as "nb"','tt_content,pages','tt_content.pid=pages.uid AND tt_content.hidden=0 AND tt_content.deleted=0 AND pages.hidden=0 AND pages.deleted=0','tt_content.CType,tt_content.list_type','nb DESC');
		
		$content = '';
		$content .= '<table cellspacing="1" cellpadding="2" border="0" class="tx_sv_reportlist">';
		$content .= '<tr class="bgColor2">';
		$content .= '<td class="cell"></td>';
		$content .= '<td class="cell">'.$GLOBALS['LANG']->getLL('content').'</td>';
		$content .= '<td class="cell">'.$GLOBALS['LANG']->getLL('references').'</td>';
		$content .= '<td class="cell">%</td>';
		$content .= '</tr>';
		foreach ($items as $itemKey => $itemValue) {

			$content .= '<tr class="bgColor3-20">';
			
			if ($itemValue['CType']=='list') {
				preg_match('/EXT:(.*?)\//', $plugins[$itemValue['list_type']][0],$ext);
				preg_match('/^LLL:(EXT:.*?):(.*)/', $plugins[$itemValue['list_type']][0],$llfile);
				$LOCAL_LANG = t3lib_div::readLLfile($llfile[1],$GLOBALS['LANG']->lang);
				$content .= '<td class="cell" align="center"><img src="' . $plugins[$itemValue['list_type']][2] . '"/></td>';
				$content .= '<td class="cell">' . $GLOBALS['LANG']->getLLL($llfile[2],$LOCAL_LANG). ' ('. $itemValue['list_type'] . ')</td>';
			} else {
				preg_match('/^LLL:(EXT:.*?):(.*)/', $ctypes[$itemValue['CType']][0],$llfile);
				$LOCAL_LANG = t3lib_div::readLLfile($llfile[1],$GLOBALS['LANG']->lang);
				$content .= '<td class="cell" align="center">';
				if (is_file(PATH_site.'/typo3/sysext/t3skin/icons/gfx/' . $ctypes[$itemValue['CType']][2])) {
					$content .= '<img src="/typo3/sysext/t3skin/icons/gfx/' . $ctypes[$itemValue['CType']][2] . '"/>';
				}
				$content .= '</td>';
				$content .= '<td class="cell">' . $GLOBALS['LANG']->getLLL($llfile[2],$LOCAL_LANG) . ' (' . $itemValue['CType'] . ')</td>';
			}
			
			$content .= '<td class="cell">' . $itemValue['nb'] . '</td>';
			$content .= '<td class="cell">' . round((($itemValue['nb']*100)/$itemsCount[0]['nb']),2) . ' %</td>';
			$content .= '</tr>';
		
		}
		$content .= '</table>';
		return $content;
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/additional_reports/reports_plugins/class.tx_additionalreports_plugins.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/additional_reports/reports_plugins/class.tx_additionalreports_plugins.php']);
}

?>