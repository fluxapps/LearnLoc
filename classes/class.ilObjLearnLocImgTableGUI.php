<?php
/*
        +-----------------------------------------------------------------------------+
        | ILIAS open source                                                           |
        +-----------------------------------------------------------------------------+
        | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
        |                                                                             |
        | This program is free software; you can redistribute it and/or               |
        | modify it under the terms of the GNU General Public License                 |
        | as published by the Free Software Foundation; either version 2              |
        | of the License, or (at your option) any later version.                      |
        |                                                                             |
        | This program is distributed in the hope that it will be useful,             |
        | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
        | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
        | GNU General Public License for more details.                                |
        |                                                                             |
        | You should have received a copy of the GNU General Public License           |
        | along with this program; if not, write to the Free Software                 |
        | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
        +-----------------------------------------------------------------------------+
*/

require_once('./Services/Table/classes/class.ilTable2GUI.php');


/**
* GUI class for course/group waiting list
* 
* @author Fabian Schmid
* @version $Id$
*
*/
class ilObjLearnLocImgTableGUI extends ilTable2GUI
{

	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function __construct($a_parent_obj, $command, $data, $show_content = true)
	{
	 	global $ilCtrl;
	 	
	 	$this->lng = $lng;
	 	$this->ctrl = $ilCtrl;
	 	$this->par = $a_parent_obj;
	 	
		parent::__construct($a_parent_obj, 'showMedia');
		
		$this->setFormName('media');
		
		$this->setData($data);

		if(is_array($data))
		{
			foreach(array_keys($data[0]) as $key)
			{
				$this->addColumn($this->lng->txt("rep_robj_xlel_".$key), $key, 'auto');
			}
		}


		$this->setPrefix('');
		
		$this->setRowTemplate("tpl.show_log.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/LearnLoc");
		
		
		if($show_content)
		{
			$this->enable('sort');
			$this->enable('header');
			$this->enable('numinfo');
			$this->enable('select_all');
		}
		else
		{
			$this->disable('content');
			$this->disable('header');
			$this->disable('footer');
			$this->disable('numinfo');
			$this->disable('select_all');
		}	
		
	}
	
	
	/**
	 * fill row 
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function fillRow($a_set)
	{
		global $ilUser, $ilCtrl;
		//echo "<pre>".print_r(get_class_methods($ilCtrl),1)."</pre>";
		require_once('./Services/Calendar/classes/class.ilDateTime.php');
		
	
		foreach($a_set as $k => $v)
		{
			
			/*switch ($k)
			{
				case "datum":
					$this->tpl->setVariable('VAL_'.strtoupper($k), date("d.m.y - H:i:s",$v));
					break;
				case "fach":
				case "fakultaet":
				case "institut":
					$fakfach = $this->par->object->getFakFach($a_set[fach]);
					$this->tpl->setVariable('VAL_'.strtoupper($k), $fakfach->$k);
					break;
				case "crsid":
					$link = "./repository.php?ref_id=".$v;
					$this->tpl->setVariable('VAL_'.strtoupper($k)."_LINK", $link);
					$this->tpl->setVariable('VAL_'.strtoupper($k), $v);
					break;
				default:
					$this->tpl->setVariable('VAL_'.strtoupper($k), $v);
					break;
			}*/
			
		}
		
		
		/*$this->tpl->setVariable('VAL_NAME',$a_set['name']);
		$this->tpl->setVariable('VAL_SUBTIME',ilDatePresentation::formatDate(new ilDateTime($a_set['sub_time'],IL_CAL_UNIX)));
		$this->tpl->setVariable('VAL_LOGIN',$a_set['login']);
		
		$this->ctrl->setParameterByClass(get_class($this->getParentObject()),'member_id',$a_set['id']);
		$link = $this->ctrl->getLinkTargetByClass(get_class($this->getParentObject()),'sendMailToSelectedUsers');
		$this->tpl->setVariable('MAIL_LINK',$link);
		$this->tpl->setVariable('MAIL_TITLE',$this->lng->txt('crs_mem_send_mail'));*/
	}
	

	
}
?>