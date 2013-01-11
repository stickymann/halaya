<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Returns AJAX request to javascripts. 
 *
 * $Id: Ajaxtodb.php 2013-01-01 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license     
 */

define("DELIMITER","{##}");

class Controller_Core_Ajaxtodb extends Controller
{
	public function before()
    {
		$this->sitedb = new Model_SiteDB;
		$this->enqdb = new Model_EnqDB;
		$this->paramkey = $this->sitedb->get_param_keys();
	}

	public function action_index()
	{
		$limit = "limit 500";
		$option = $_REQUEST['option'];
		switch($option)
		{
			case 'sideinfo':
				$fields		= $_REQUEST['fields'];
				$table		= $_REQUEST['table'];
				$idfield	= $_REQUEST['idfield'];
				$idval		= $_REQUEST['idval'];
				$querystr = sprintf('select %s from %s where %s="%s";',$fields,$table,$idfield,$idval);
				$result = $this->sitedb->execute_select_query($querystr);
				$this->print_sideinfo_result($result);
			break;
			
			case 'sidefunc':
				$func		= $_REQUEST['func'];
				$parameter	= $_REQUEST['parameter'];
				switch($func)
				{	
					case 'dobtoage':
						$AGE = $this->get_age($parameter);
						print $this->format_info($AGE);
					break;

					case 'minstohrs':
						$HRS = $this->get_hours($parameter);
						print $this->format_info($HRS);
					break;

					case 'datestoperiodstr':
						$STR = $this->get_period_string($parameter);
						print $this->format_info($STR);
					break;
									
					case 'orderbalance':
						$BAL = $this->get_orderbalance_info($parameter);
						print $this->format_info($BAL);
					break;
				}
			break;
			
			case 'jsubrecs':
				$controller  = $_REQUEST['subcontroller'];
				$parentfield = $_REQUEST['parentfield'];
				$current_no	 = $_REQUEST['curno'];
				$idfield	 = $_REQUEST['idfield'];
				$idval		 = $_REQUEST['idval'];
				if(isset($_REQUEST['tabletype'])){$table_type = $_REQUEST['tabletype'];} else {$table_type = false;}

				$param		 = $this->sitedb->getControllerParams($controller);
				$columnfield = $this->sitedb->getSubFormFields($controller);
				$prefix  = sprintf('subform_%s_',$parentfield);
				
				if($table_type)
				{
					if($table_type == "live"){ $table = $param['tb_live']; }else if($table_type == "inau"){	$table = $param['tb_inau'];}else if($table_type == "hist"){ $table = $param['tb_hist'];}
					$result = $this->sitedb->getRecsBySubform($table,$columnfield,$idfield,$idval,$current_no,$prefix);
				}
				else
				{	/*default action*/
					/* for subrecs in inau first*/
					$table = $param['tb_inau'];
					if(!($result = $this->sitedb->getRecsBySubform($table,$columnfield,$idfield,$idval,$current_no,$prefix)))
					{
						/*if no inau records found, check live*/
						$table = $param['tb_live'];
						$result = $this->sitedb->getRecsBySubform($table,$columnfield,$idfield,$idval,$current_no,$prefix);
						foreach($result as $key => $row)
						{
							$field = $prefix."id";
							$id = $result[$key]->$field;
							$this->sitedb->insertFromTableToTable($param['tb_inau'],$param['tb_live'],$id);
							$this->sitedb->setRecordStatus($param['tb_inau'],$id,"IHLD");
						}
					}
					else
					{
						foreach($result as $key => $row)
						{
							$field = $prefix."id";
							$id = $result[$key]->$field;
							$this->sitedb->setRecordStatus($param['tb_inau'],$id,"IHLD");
						}
					}
				}
				print json_encode($result);
			break;
			
			case 'jdefaultorderdetailscolumndef':
				$columnfield = $this->sitedb->getSubFormColumnDef("orderdetail","subform_order_details_");
				print json_encode($columnfield);
			break;

			case 'jdata':
				if(isset($_REQUEST['controller']))
				{
					$controller = $_REQUEST['controller'];
					if(isset($_REQUEST['tabletype'])){$table_type = $_REQUEST['tabletype'];} else {$table_type = "live";}
					$param  = $this->sitedb->getControllerParams($controller);
					if($table_type == "live"){ $table = $param['tb_live']; }else if($table_type == "inau"){ $table = $param['tb_inau'];}else if($table_type == "hist"){ $table = $param['tb_hist'];}
 				}
				else
				{
					$table = $_REQUEST['dbtable'];
				}
								
				$fields = $_REQUEST['fields'];
				$prefix = $_REQUEST['prefix'];
				$where = "";
				if(isset($_REQUEST['wfields']))
				{
					$where = "WHERE ";
					$wfields = $_REQUEST['wfields'];
					$wvals = $_REQUEST['wvals'];
					$wfields = preg_split('/,/',$wfields);
					$wvals = preg_split('/,/',$wvals);
					foreach($wfields as $key => $value)
					{
						$where .= sprintf('%s = "%s" AND ',$value,$wvals[$key]);
					}
					$where = substr_replace($where, '', -5);	
				}
				$orderby ="";
				if(isset($_REQUEST['orderby']))
				{ 
					$orderby = "ORDER BY ".$_REQUEST['orderby']; 
				}
				
				$fields = explode(",", $fields);
				$result = $this->sitedb->getAllRecsByFields($table,$fields,$prefix,$where,$orderby);
				print json_encode($result);
			break;

			case 'jdatabyid':
				if(isset($_REQUEST['controller']))
				{
					$controller = $_REQUEST['controller'];
					if(isset($_REQUEST['tabletype'])){$table_type = $_REQUEST['tabletype'];} else {$table_type = "live";}
					$param  = $this->sitedb->getControllerParams($controller);
					if($table_type == "live"){ $table = $param['tb_live']; }else if($table_type == "inau"){ $table = $param['tb_inau'];}else if($table_type == "hist"){ $table = $param['tb_hist'];}
				}
				else
				{
					$table = $_REQUEST['dbtable'];
				}
				
				$fields = $_REQUEST['fields'];
				$idfield = $_REQUEST['idfield'];
				$idval  = $_REQUEST['idval'];
				
				$fields = explode(",", $fields);	
				$idvals = preg_split('/,/',$idval);
				foreach($idvals as $key => $val)
				{
					if($result = $this->sitedb->getRecordByIdVal($table,$idfield,$val,$fields))
					{
						print json_encode($result);
						return;
					}
				}
			break;

			case 'jxmldatabyid':
				$controller = $_REQUEST['controller'];
				$field = $_REQUEST['field'];
				$idfield = $_REQUEST['idfield'];
				$idval  = $_REQUEST['idval'];
				if(isset($_REQUEST['tabletype'])){$table_type = $_REQUEST['tabletype'];} else {$table_type = "live";}
				if(isset($_REQUEST['prefix'])){$prefix = $_REQUEST['prefix'];} else {$prefix = "";}

				$param  = $this->sitedb->getControllerParams($controller);
				if($table_type == "live"){ $table = $param['tb_live']; }else if($table_type == "inau"){ $table = $param['tb_inau'];}else if($table_type == "hist"){ $table = $param['tb_hist'];}
				if($result = $this->sitedb->getXMLFieldDataByIdVal($table,$idfield,$idval,$field,$prefix))
				{
					print json_encode($result);
					return;
				}
			break;

			case 'popout':
				$fields		= $_REQUEST['fields'];
				$table		= $_REQUEST['table'];
				$idfield	= $_REQUEST['idfield'];
				$querystr = sprintf('select %s from %s order by %s asc %s;',$fields,$table,$idfield,$limit);
				/*
				// PDO equivalent
				// $stmt = $appdb->query($querystr);
				// $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
				*/
				$result = $this->sitedb->execute_select_query($querystr);
				$this->print_popout_result($result);
			break;

			case 'poplist':
				$fields		= $_REQUEST['fields'];
				$table		= $_REQUEST['table'];
				$idfield	= $_REQUEST['idfield'];
				$querystr = sprintf('select %s from %s order by %s asc %s;',$fields,$table,$idfield,$limit);
				//$stmt = $appdb->query($querystr);
				//$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
				$result = $this->sitedb->execute_select_query($querystr);
				$this->printPopListResult($result);
			break;
		
			case 'pofilter':
				$fields		= $_REQUEST['fields'];
				$table		= $_REQUEST['table'];
				$lkvals		= $_REQUEST['lkvals'];
				$idfield	= $_REQUEST['idfield'];
				$like = "";
				$lkarr = array_combine(preg_split('/,/',$fields),preg_split('/,/',$lkvals));
				foreach($lkarr as $key => $value)
				{
					if(strpos($value,':::'))
					{
						$range = preg_split('/:::/',$value);
						$like .= sprintf('(%s >= "%s" AND %s <= "%s") AND ',$key,$range[0],$key,$range[1]);
					}
					else
					{
						$like .= sprintf('%s LIKE "%s%s" AND ',$key,$value,"%");
					}
				}
				$like = substr_replace($like, '', -5);
				$querystr = sprintf('select %s from %s where %s order by %s asc %s;',$fields,$table,$like,$idfield,$limit);
				$result =  $this->sitedb->execute_select_query($querystr);
				$this->print_popout_result($result);
			break;

			case 'polistfilter':
				$fields		= $_REQUEST['fields'];
				$table		= $_REQUEST['table'];
				$lkvals		= $_REQUEST['lkvals'];
				$idfield	= $_REQUEST['idfield'];
				$like = "";
				$lkarr = array_combine(preg_split('/,/',$fields),preg_split('/,/',$lkvals));
				foreach($lkarr as $key => $value)
				{
					if(strpos($value,':::'))
					{
						$range = preg_split('/:::/',$value);
						$like .= sprintf('(%s >= "%s" AND %s <= "%s") AND ',$key,$range[0],$key,$range[1]);
					}
					else
					{
						$like .= sprintf('%s LIKE "%s%s" AND ',$key,$value,"%");
					}
				}
				$like = substr_replace($like, '', -5);
				$querystr = sprintf('select %s from %s where %s order by %s asc %s;',$fields,$table,$like,$idfield,$limit);
				//$stmt = $appdb->query($querystr);
				//$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
				$result =  $this->sitedb->execute_select_query($querystr);
				$this->printPopListResult($result);
			break;

			case 'enquiry':
				$fields		= "";
				$idfield	= 'id';
				$param_id	= $_REQUEST['param_id'];
				$controller	= $_REQUEST['controller'];
				$tabletype	= $_REQUEST['tabletype'];
				$export		= $_REQUEST['export'];
				$opvals		= $_REQUEST['opvals'];
				$fieldvals	= $_REQUEST['fieldvals'];
				$fn         = $_REQUEST['fieldnames'];
				$pager		= $_REQUEST['pager'];
				$enqtype	= $_REQUEST['enqtype'];
				$user		= $_REQUEST['user'];
				$limit		= "limit ".$_REQUEST['limit'];

				if($enqtype == 'default')
				{
					$fields = join(',',$this->sitedb->get_formfields($controller));
					$fields = $fields.",inputter,input_date,authorizer,auth_date,record_status,current_no";
					$querystr = sprintf('select tb_live,tb_inau,tb_hist from params where controller="%s";',$controller);
					$result =  $this->sitedb->execute_select_query($querystr);
					if($tabletype == 'ls'){$table = $result[0]->tb_live;} 
					elseif ($tabletype == 'is'){$table = $result[0]->tb_inau;}
					elseif ($tabletype == 'hs'){$table = $result[0]->tb_hist;}

					$label = array();
					$form = $this->sitedb->get_formfields($controller,$label);
					Controller_Core_Site::merge_form_with_audit_fields($form,$label);
				}
				elseif($enqtype == 'custom')
				{
					$this->enqdb->get_enq_formfields($controller,$form,$label,$filterfields);
					$fields = join(',',$form);
					$querystr = sprintf('select tablename from enquirydefs where controller="%s";',$controller);
					$result =  $this->sitedb->execute_select_query($querystr);
					if($tabletype == 'df'){$table = $result[0]->tablename;} 
				}
					
				$where = "";
				$oparr = array_combine(preg_split('/,/',$fields),preg_split('/,/',$opvals));
				$valarr = array_combine(preg_split('/,/',$fields),preg_split('/,/',$fieldvals));
				
				foreach($valarr as $key => $value)
				{
					$sub1 = substr($value,0,4);
					if( $sub1 == "%OR%")
					{
						$value = trim(str_replace("%OR%","",$value));
						$pos = strrpos($where, "AND");
						if(!($pos === false))
						{
							$where = substr_replace($where,"OR", $pos, -1);
						}
					}

					switch($oparr[$key])
					{
						case "EQ":
							if($value !=''){$where .= sprintf('%s = "%s" AND ',$key,$value);}
							break;
								
						case "NE": 
							if($value !=''){$where .= sprintf('%s != "%s" AND ',$key,$value);}
							break;
						
						case "LK":
							if($value !=''){$where.= sprintf('%s LIKE "%s%s" AND ',$key,$value,"%");}
							break;
						
						case "GT": 
							if($value !=''){$where .= sprintf('%s > "%s" AND ',$key,$value);}
							break;
						
						case "GE":
							if($value !=''){$where .= sprintf('%s >= "%s" AND ',$key,$value);}
							break;
						
						case "LT":
							if($value !=''){$where .= sprintf('%s < "%s" AND ',$key,$value);}
							break;

						case "LE":
							if($value !=''){$where .= sprintf('%s <= "%s" AND ',$key,$value);}
							break;
						
						case "RG":
							if(strpos($value,':::'))
							{
								$range = preg_split('/:::/',$value);
								$where .= sprintf('(%s >= "%s" AND %s <= "%s") AND ',$key,$range[0],$key,$range[1]);
							}
							break;
						
						case "NR":
							if(strpos($value,':::'))
							{
								$range = preg_split('/:::/',$value);
								$where .= sprintf('(%s < "%s" OR %s > "%s") AND ',$key,$range[0],$key,$range[1]);
							}
							break;

						case "BT":
							if(strpos($value,':::'))
							{
								$range = preg_split('/:::/',$value);
								$where .= sprintf('(%s > "%s" AND %s < "%s") AND ',$key,$range[0],$key,$range[1]);
							}
							break;
						
						case "NB":
							if(strpos($value,':::'))
							{
								$range = preg_split('/:::/',$value);
								$where .= sprintf('(%s <= "%s" OR %s >= "%s") AND ',$key,$range[0],$key,$range[1]);
							}	
							break;
					}
				}
				$where = substr_replace($where, '', -5);
				
				if($export){$limit = '';}
				if($where != '')
				{
					$querystr = sprintf('select %s from %s where %s order by %s asc %s;',$fields,$table,$where,$idfield,$limit);
					//print $querystr."<br>";
				}
				else
				{
					$querystr = sprintf('select %s from %s order by %s asc %s;',$fields,$table,$idfield,$limit);
					//print $querystr."<br>";
				}
				
				$result =  $this->sitedb->execute_select_query($querystr);
				if($export)
				{
					$this->print_CSV_id($param_id,$controller,$result,$tabletype,$label,$fn,$user,$enqtype);
				}
				else
				{
					$this->print_enquiry_result($param_id,$controller,$result,$tabletype,$label,$fn,$pager);
				}
			//END ENQUIRY//////////////////////////////////////////////////////
			break;

			case 'filterform':
				$controller		= $_REQUEST['controller'];
				$enqtype		= $_REQUEST['enqtype'];
				$loadfixedvals	= $_REQUEST['loadfixedvals'];
				$user			= $_REQUEST['user'];
				$rochk			= $_REQUEST['rochk'];
				
				if($rochk){$this->print_filterform($controller,$user,$enqtype,$loadfixedvals,$rochk);}
				else {$this->print_filterform($controller,$user,$enqtype,$loadfixedvals);}
			break;

			case 'enqctrl':
				$param_id	= $_REQUEST['param_id'];
				$controller = $_REQUEST['controller'];
				$idname		= $_REQUEST['user'];
				$this->print_enquiry_controls($param_id,$controller,$idname);
			break;

			case 'customerid':
				$id = $_REQUEST['id'];
				$firstname	= $_REQUEST['firstname'];
				$lastname	= $_REQUEST['lastname'];
				$controller = $_REQUEST['controller'];
				$RESULT		= $this->getCustomerId($id,$firstname,$lastname,$controller);
				print $RESULT;
			break;

			case 'orderid':
				$controller = $_REQUEST['controller'];
				$ctrl_id	= $_REQUEST['ctrlid'];
				$prefix		= $_REQUEST['prefix'];
				$RESULT		= $this->getNextDateTypeId($controller,$ctrl_id,$prefix,true);
				print $RESULT;
			break;
			
			case 'orderstatus':
				$fldval = $_REQUEST['fldval'];
				$this->printOrderStatusForm($fldval);
			break;
				
			case 'ordertotal';
				$order_id	 = $_REQUEST['order_id'];
				$order_total = $this->getOrderTotal($order_id);
				print number_format($order_total, 2, '.', '');
			break;

			case 'orderpaymenttotal';
				$order_id		= $_REQUEST['order_id'];
				$payment_total	= $this->getOrderPaymentTotal($order_id);
				print number_format($payment_total, 2, '.', '');
			break;

			case 'orderbalance';
				$order_id	= $_REQUEST['order_id'];
				$order_total = $this->getOrderTotal($order_id);
				$payment_total	= $this->getOrderPaymentTotal($order_id);
				$balance = $order_total - $payment_total;
				print number_format($balance, 2, '.', '');
			break;

			case 'userrolechkbox':
				$this->printUserRoleCheckBoxes();
			break;
			
			case 'roleadminchkbox':
				$spid		= $_REQUEST['spid'];
				$current_no = $_REQUEST['current_no'];
				$this->printRoleAdminCheckBoxes($spid,$current_no);
			break;

			case 'productpopoutchkbox':
				$items		= $_REQUEST['pitems'];
				$idfield	= $_REQUEST['idfield'];
				$table		= $_REQUEST['table'];
				$fields		= $_REQUEST['fields'];
				$querystr	= sprintf('select %s from %s order by %s asc %s;',$fields,$table,$idfield,$limit);
				$this->printPopOutCheckBoxes($querystr,$fields,$table,$idfield,$limit,$items,true);
			break;

			case 'idname':
				$idname = HTML::chars(Auth::instance()->get_user()->idname);
				print trim($idname);
			break;

			case 'changepasswordform':
				$this->printChangePasswordForm();
			break;

			case 'userbranch':
				$idname	= $_REQUEST['idname'];
				$RESULT	= $this->getUserBranch($idname);
				print $RESULT;
			break;

			case 'loginok';
				$user		= $_REQUEST['user'];
				$pass		= $_REQUEST['pass'];
				$querystr	= sprintf('select %s from users where idname="%s";',"username",$user);
				$result		= $this->sitedb->execute_select_query($querystr);
				$user		= $result[0]->username;
				if(Auth::instance()->login($user,$pass)){print "1";}else{print "0";} 
			break;

			case 'products';
				$querystr	= 'select * from products';
				$result		= $this->sitedb->execute_select_query($querystr);
				print json_encode($result); 
			break;
		}
	}
	
	function print_sideinfo_result($result)
	{
		$format = $_REQUEST['format'];
		$format = str_replace("*","%s",$format);
		$format = str_replace(";",",",$format); 
		$format = str_replace("_"," ",$format); 
		$RESULT="";
		
		if(!(empty($result)))
		{
			$result = (array)$result[0];
			$RESULT = vsprintf($format,$result);
		}
		print $RESULT;
	}

	function print_popout_result($result)
	{
		$idfield = $_REQUEST['idfield'];
		$RESULT = '<table id="potable" class="tablesorter" border="0" cellpadding="0" cellspacing="1" >'."\n";
		$firstpass = true;
		//$lbl=$this->label;
		foreach($result as $row => $linerec)
		{	
			$linerec = (array)$linerec;
			$header = ''; $data = '';
			foreach ($linerec as $key => $value)
			{
				if($firstpass)
				{
					$headtxt = Controller_Core_Site::strtotitlecase(str_replace("_"," ",$key));
					$header .= '<th>'.$headtxt.'</th>'; 
				}
				if($key == $idfield)
				{
					$data .= sprintf('<td><a href = "javascript:void(0)" onClick = window.popout.Update("%s")>%s</a></td>',str_replace(" ","_",$value),$value);
				}
				else
				{
					$data .= '<td>'.HTML::chars($value).'</td>'; 
				}
			}
			if($firstpass)
			{
				$header = "\n".'<thead>'."\n".'<tr>'.$header.'</tr>'."\n".'</thead>'."\n".'<tbody>'."\n";
				$RESULT .=$header;
			}
			$data = '<tr>'.$data.'</tr>'."\n"; 
			$RESULT .= $data;
			$firstpass = false;
		}
		$RESULT .='</tbody>'."\n".'</table>'."\n";
		$RESULT .= <<<_TEXT_
		<script>
			$(
				function()
				{	 
					$("#potable").tablesorter({sortList:[[0,0]], widgets: ['zebra']});
					$("#options").tablesorter({sortList: [[0,0]], headers: { 3:{sorter: false}, 4:{sorter: false}}});
				}
			);
		</script>
_TEXT_;
		print $RESULT;
	}
	
	function printPopListResult($result)
	{
		$idfield = $_REQUEST['idfield'];
		$RESULT = '<table id="potable" class="tablesorter" border="0" cellpadding="0" cellspacing="1" >'."\n";
		$firstpass = true;
		//$lbl=$this->label;
		foreach($result as $row => $linerec)
		{	
			$linerec = (array)$linerec;
			$header = ''; $data = '';
			foreach ($linerec as $key => $value)
			{
				if($firstpass)
				{
					$headtxt = Site_Controller::strtotitlecase(str_replace("_"," ",$key));
					$header .= '<th>'.$headtxt.'</th>'; 
				}
				$data .= '<td>'.HTML::chars($value).'</td>'; 
			
			}
			if($firstpass)
			{
				$header = "\n".'<thead>'."\n".'<tr>'.$header.'</tr>'."\n".'</thead>'."\n".'<tbody>'."\n";
				$RESULT .=$header;
			}
			$data = '<tr>'.$data.'</tr>'."\n"; 
			$RESULT .= $data;
			$firstpass = false;
		}
		$RESULT .='</tbody>'."\n".'</table>'."\n";
		$RESULT .= <<<_TEXT_
		<script>
			$(
				function()
				{	 
					$("#potable").tablesorter({sortList:[[0,0]], widgets: ['zebra']});
					$("#options").tablesorter({sortList: [[0,0]], headers: { 3:{sorter: false}, 4:{sorter: false}}});
				}
			);
		</script>
_TEXT_;
		print $RESULT;
	}

	function printCustomPopOutResult($result)
	{
		$idfield = $_REQUEST['idfield'];
		$func	 = $_REQUEST['func'];
		$RESULT = '<table id="chktable" class="tablesorter" border="0" cellpadding="0" cellspacing="1">'."\n";
		$firstpass = true;
		//$lbl=$this->label;
		foreach($result as $row => $linerec)
		{	
			$linerec = (array)$linerec;
			$header = ''; $data = '';
			foreach ($linerec as $key => $value)
			{
				if($firstpass)
				{
					$headtxt = Controller_Core_Site::strtotitlecase(str_replace("_"," ",$key));
					$header .= '<th>'.$headtxt.'</th>'; 
				}
				if($key == $idfield)
				{
					$data .= sprintf('<td><a href = "javascript:void(0)" onClick = %s("%s")>%s</a></td>',$func,str_replace(" ","_",$value),$value);
				}
				else
				{
					$data .= '<td>'.HTML::chars($value).'</td>'; 
				}
			}
			if($firstpass)
			{
				$header = "\n".'<thead>'."\n".'<tr>'.$header.'</tr>'."\n".'</thead>'."\n".'<tbody>'."\n";
				$RESULT .=$header;
			}
			$data = '<tr>'.$data.'</tr>'."\n"; 
			$RESULT .= $data;
			$firstpass = false;
		}
		$RESULT .='</tbody>'."\n".'</table>'."\n";
		$RESULT .= <<<_TEXT_
		<script>
			$(
				function()
				{	 
					$("#chktable").tablesorter({sortList:[[0,0]], widgets: ['zebra']});
					$("#options").tablesorter({sortList: [[0,0]], headers: { 3:{sorter: false}, 4:{sorter: false}}});
				}
			);
		</script>
_TEXT_;
		print $RESULT;
	}

	public function print_enquiry_result($param_id,$controller,$result,$tabletype,$label,$fn,$pager)
	{
		$RESULT = '<div id="enqresdiv" style="border:0px solid red; padding: 0px 0px 0px 0px; overflow:auto;">'."\n";
		$RESULT .= '<table id="enqrestab" class="tablesorter" border="0" cellpadding="0" cellspacing="1" width="500%">'."\n";
		$firstpass = true;
		$idfield  = 'id';
		
		if($tabletype == "df") 
		{
			$arr = $this->enqdb->get_enquiry_params($controller);
			$arr = (array) $arr[$controller];
			$altidfld = $arr['idfield'];
		} 
		else 
		{
			$arr = $this->sitedb->get_controller_params($controller);
			$altidfld = $arr['indexfield'];
		}
		
		foreach($result as $row => $linerec)
		{	
			$linerec = (array)$linerec;
			$header = ''; $data = '';
			foreach ($linerec as $key => $value)
			{
				if(strstr($value, '<?xml', true) === FALSE)
				{
				
				if($firstpass)
				{
					if($fn){$header .= '<th>'.$key.'</th>';}else {$header .= '<th>'.$label[$key].'</th>';} 
				}
				
				if($key == $idfield)
				{
					if($tabletype=="hs")
					{
						$value_no = $value.";".$linerec['current_no'];
						$data .= '<td>'.HTML::anchor($param_id.'/index/'.$value_no,$value,array('target'=>'input'));
					}
					else if($tabletype=="df")
					{
						$url =  sprintf('%s/spage?op=eq&controller=%s&fields=%s&lkvals=%s',$controller,$controller,$idfield,$value);
						$data .= '<td>'.HTML::anchor($url,$value,array('target'=>'input'));
					}
					else
					{
						$data .= '<td>'.HTML::anchor($param_id.'/index/'.$value,$value,array('target'=>'input'));
					}
				}
				else if($key == $altidfld)
				{
					if($tabletype=="hs")
					{
						$value_no = $linerec['id'].";".$linerec['current_no'];
						$data .= '<td>'.HTML::anchor($param_id.'/index/'.$value_no,$value,array('target'=>'input'));
					}
					else if($tabletype=="df")
					{
						$url =  sprintf('%s/spage?op=like&controller=%s&fields=%s&lkvals=%s',$controller,$controller,$altidfld,$value);
						$data .= '<td>'.HTML::anchor($url,$value,array('target'=>'input'));
					}
					else
					{
						$data .= '<td>'.html::anchor($$param_id.'/index/'.$value,$value,array('target'=>'input'));
					}
				}
				else
				{
					$data .= '<td>'.HTML::chars($value).'</td>'; 
				}
				}
			}
			if($firstpass)
			{
				$header = "\n".'<thead>'."\n".'<tr>'.$header.'</tr>'."\n".'</thead>'."\n".'<tbody>'."\n";
				$RESULT .=$header;
			}
			$data = '<tr>'.$data.'</tr>'."\n"; 
			$RESULT .= $data;
			$firstpass = false;
		}
		$RESULT .='</tbody>'."\n".'</table></div>'."\n";
		if($pager)
		{
			$TEXT = <<<_TEXT_
			<script type="text/javascript">
			$(function() 
			{		
				$("#enqrestab").tablesorter({sortList:[[0,0]], widgets: ['zebra']})
				.tablesorterPager({container: $("#enqrespager")});	
				$("#options").tablesorter({sortList: [[0,0]], headers: { 3:{sorter: false}, 4:{sorter: false}}});
			});
			</script> 
_TEXT_;
		print $TEXT.$RESULT.Controller_Core_Site::enquiry_pager().'<br>';
		}
		else
		{
			$TEXT = <<<_TEXT_
			<script type="text/javascript">
			$(function() 
			{		
				$("#enqrestab").tablesorter({sortList:[[0,0]], widgets: ['zebra']});	
				$("#options").tablesorter({sortList: [[0,0]], headers: { 3:{sorter: false}, 4:{sorter: false}}});
			});
			</script> 
_TEXT_;
		print $TEXT.$RESULT.'<br>';
		}
	}
	
	function print_CSV_id($param_id,$controller,$result,$tabletype,$label,$fn,$idname,$type)
	{
		$csv_id = $this->create_CSV($CSV,$result,$tabletype,$label,$fn);
		$csv = new Controller_Core_Csv();
		$csv->insert_into_CSV_table($csv_id,$CSV,$controller,$idname,$type);
		//$RESULT = str_replace("\r\n","<br>", $CSV);
		//$csv_id = "12344675475";
		print $csv_id.DELIMITER;
	}

	function create_CSV(&$CSV,$result,$tabletype,$label,$fn)
	{
		$RESULT = '';
		$firstpass = true;
		$idfield = 'id';
		$num = rand(0,999999);
		$num = str_pad($num, 6, "0", STR_PAD_LEFT);
		$csv_id	  = 'CSV'.date("YmdHis").$num;
		foreach($result as $row => $linerec)
		{	
			$linerec = (array)$linerec;
			$header = ""; $data = "";
			foreach ($linerec as $key => $value)
			{
				if($firstpass)
				{
					//if($fn){$header .= "'".$key."',";} else {$header .= "'".$label[$key]."',";} 
					if($fn){$header .= '"'.$key.'",';} else {$header .= '"'.$label[$key].'",';}
				}
				
				if($key == $idfield)
				{
					if($tabletype=="hs")
					{
						$value_no = $value.";".$linerec['current_no'];
						//$data .= "'".$value_no."',";
						$data .= '"'.$value_no.'",';
					}
					else
					{
						//$data .= "'".$value."',";
						$data .= '"'.$value.'",';
					}
				}
				else
				{
					//$data .= "'".$value."',";
					$data .= '"'.$value.'",';
				}
			}
			if($firstpass)
			{
				$header = substr_replace($header, "", -1)."\r\n";
				$RESULT .=$header;
			}
			$data = substr_replace($data, "", -1)."\r\n";
			$RESULT .= $data;
			$firstpass = false;
		}
		$CSV = $RESULT;
		return $csv_id;
	}

	function print_filterform($controller,$user,$enqtype,$loadfixedvals,$rochk=false)
	{
		if($enqtype == 'default')
		{
			$label = array();
			$form = $this->sitedb->get_formfields($controller,$label);
			Controller_Core_Site::merge_form_with_audit_fields($form,$label);
		}
		elseif($enqtype == 'custom')
		{
			$this->enqdb->get_enq_formfields($controller,$form,$label,$filterfields);
		}
		
		if($loadfixedvals)
		{
			$count = 0;
			if($this->enqdb->get_fixed_selection_params($controller,$fldname,$operand,$inputvalue,$attr))
			{
			
				foreach($inputvalue as $index => $value)
				{
					$value = str_replace("%IDNAME%",$user,$value);
					$value = str_replace("%CURDATE%",date('Y-m-d'),$value);
					$value	= str_replace("%DATE%","",$value,$count);
					if($count)
					{
						$dstr = sprintf('%s days',$value);
						$value = date('Y-m-d', strtotime($dstr));
					}
					$inputvalue[$index] = $value;
				}
			}
		}
		$selection = array('EQ'=>'EQ','NE'=>'NE','LK'=>'LK','GT'=>'GT','GE'=>'GE','LT'=>'LT','LE'=>'LE','RG'=>'RG','NR'=>'NR','BT'=>'BT','NB'=>'NB');
				
		/*
		$SELECT=<<<_HTML_
	<select id="%ID%">
		<option value="EQ">EQ</option>
		<option value="NE">NE</option>
		<option value="LK">LK</option>
		<option value="GT">GT</option>
		<option value="GE">GE</option>
		<option value="LT">LT</option>
		<option value="LE">LE</option>
		<option value="RG">RG</option>
		<option value="NR">NR</option>
		<option value="BT">BT</option>
		<option value="NB">NB</option>
	</select>
_HTML_;
*/
		$FORMTXT = '<form id=formfilter><table>'."\n";

		foreach($form as $key => $value)
		{	
			$inval = "";  $attrval = "";//array('id'=>'%ID%','name'=>'%ID%','class'=>'ff')
			$SELECT = Form::select('%ID%',$selection,'EQ', array('id'=>'%ID%','class'=>'ff'));
			if($loadfixedvals)
			{
				if(array_key_exists($key, $inputvalue)){$inval = $inputvalue[$key];}
				if(array_key_exists($key, $attr)){$attrval = $attr[$key];}
				if($attrval=="readonly" && array_key_exists($key,$operand))
				{
					$tmpselect = array($operand[$key]=>$operand[$key]);
					$SELECT = "\n".Form::select(array('id'=>'%ID%','name'=>'%ID%','class'=>'ff'),$tmpselect,$operand[$key])."\n";
				}
				else if(array_key_exists($key,$operand))
				{
					$SELECT = "\n".Form::select(array('id'=>'%ID%','name'=>'%ID%','class'=>'ff'),$selection,$operand[$key])."\n";
				}
				
			}
		
			if($rochk){$SELECT = "\n".Form::select(array('id'=>'%ID%','name'=>'%ID%','class'=>'ff','onChange'=>'window.fixedselection.setFS();'),$selection,'EQ')."\n";}
			$idfield = sprintf('%s_%s',$key,'select');
			$input = sprintf('%s',$key);
			$select = str_replace("%ID%",$idfield, $SELECT);
			/*
			if($rochk)
			{
				$html = "\n".sprintf('<input onChange="window.fixedselection.setFS();" type="checkbox" id="%s" name="%s" class="ff" checked/>',$input."_rochk",$input."_rochk").Form::label($input."_rochk",'readonly')."\n";
				$size = "30"; $inval = ""; $attrval = "";
			}
			else
			{
				$size = "50"; $ro = "";
			}
			$FORMTXT .= "\n".'<tr>'."\n";
			$FORMTXT .= sprintf('<td><label for="%s">%s :</label></td>',$input,$label[$key])."\n";
			$FORMTXT .= sprintf('<td>%s</td>',$select)."\n";
			$FORMTXT .= $INPUT;
			$FORMTXT .= '</tr>'."\n";
			*/

			if($rochk)
			{
				$ro = "\n".sprintf('<input onChange="window.fixedselection.setFS();" type="checkbox" id="%s" name="%s" class="ff" checked/>',$input."_rochk",$input."_rochk").form::label($input."_rochk",'readonly')."\n";
				$FORMTXT .= sprintf('<tr><td><label for="%s">%s :</label></td><td>'."\n".'%s</td><td><input onChange="window.fixedselection.setFS();" type="text" id="%s" size=30 value="" class="ff"> %s </td></tr>',$input,$label[$key],$select,$input,$ro);
			}
			else
			{
				$FORMTXT .= sprintf('<tr><td><label for="%s">%s :</label></td><td>'."\n".'%s</td><td><input type="text" id="%s" size=50 value="%s" %s class="ff"/></td></tr>',$input,$label[$key],$select,$input,$inval,$attrval)."\n\n";
			}
		}
		$FORMTXT .= '</table></form>'."\n";
		print $FORMTXT;
	}
	
	function print_enquiry_controls($param_id,$controller,$idname)
	{
		$enqradios = new Controller_Core_Sitecontrol($param_id,$controller,$idname);
		$html = $enqradios->get_enqform_controls();
		print $html;
	}
	
	function printOrderStatusForm($fldval)
	{
		$RESULT = "";
		$where = sprintf('WHERE progession_id >= (SELECT progession_id FROM _sys_orderstatus WHERE order_status_id = "%s")AND progession_id < (SELECT progession_id FROM _sys_orderstatus WHERE order_status_id = "INVOICE.PART.PAID")',$fldval);
		$querystr = sprintf('SELECT progession_id as id ,order_status_id FROM _sys_orderstatus %s AND progession_id > 1',$where);
		if($result = $this->sitedb->execute_select_query($querystr))
		{
			$this->printCustomPopOutResult($result);
		}
		print $RESULT;
	}

	function printUserRoleCheckBoxes()
	{
		$selection = ''; $allroles='';
		$roles = $_REQUEST['roles'];
		//if($key == 'idname') {$selection = ORM::factory('user')->select_list('idname','idname'); $user = ORM::factory('user',$key);}
		//if($key == 'roles') 
		//{	
			$arr = ORM::factory('role')->where(array('name !=' => 'login'))->select_list('name','description');
			$checklist = preg_split('/,/',$roles);
			$selection = "\n<td><table cellspacing=2>\n";
			foreach($arr as  $rolekey => $roledesc)
			{
				if(in_array($rolekey, $checklist)){$checked ='checked';}else{$checked ='';}
				$selection .= '<tr valign="center">';
				$html = sprintf('<td><input type="checkbox" id="%s" name="%s" value="%s" %s onchange=window.userrole.setRoles() /></td>',$rolekey,$rolekey,$rolekey,$checked);
				//$html .= '<script type="text/javascript"> alert("'.$rolekey.'");</script>';
				$allroles .= $rolekey.",";
				$selection .= $html;	
				$html = '<td>'.form::label($rolekey,$rolekey).'</td>';
				$selection .= $html;
				$html = sprintf('<td> -> %s </td>',$roledesc);
				$selection .= $html;	
				$selection .= "</tr>\n";
			}
			$selection .= '</table></td>';
			$allroles = substr_replace($allroles, '', -1);
			$selection .= sprintf('<input type="hidden" id="allroles" name="allroles" value="%s">',$allroles);

		//}
		print $selection;
	}
	
	function printRoleAdminCheckBoxes($spid,$current_no)
	{
		$treemenu = new Menusuper_Controller();
$HTML=<<<_HTML_
		<table class="ci">
			<tr><th class="ch">Symbol</th><th class="ch">Name</th><th class="ch">Description</th><th class="ch">Symbol</th><th class="ch">Name</th><th class="ch">Description</th></tr>
			<tr><td class="ci">if</td><td class="ci">Index Field</td><td class="ci">Index Field is writable</td>
				<td class="ci">ao</td><td class="ci">Authorize Other</td><td class="ci">User can authorize record not self created</td></tr>
			<tr><td class="ci">vw</td><td class="ci">View</td><td class="ci">User can view record</td>
				<td class="ci">as</td><td class="ci">Authorize Self</td><td class="ci">User can authorize record self created</td></tr>
			<tr><td class="ci">nw</td><td class="ci">New</td><td class="ci">User can create new record</td>
				<td class="ci">de</td><td class="ci">Delete</td><td class="ci">User can delete live record</td></tr>
			<tr><td class="ci">cp</td><td class="ci">Copy</td><td class="ci">User can copy existing record</td>
				<td class="ci">hd</td><td class="ci">Hold</td><td class="ci">User can place record on hold</td></tr>
			<tr><td class="ci">iw</td><td class="ci">Edit New</td><td class="ci">User can edit new record only</td>
				<td class="ci">va</td><td class="ci">Validate</td><td class="ci">User can validate record</td></tr>
			<tr><td class="ci">in</td><td class="ci">Edit</td><td class="ci">User can edit record</td>
				<td class="ci">pr</td><td class="ci">Printable</td><td class="ci">User can generate printable version</td></tr>
			<tr><td class="ci">vr</td><td class="ci">Verify</td><td class="ci">User can run system routine</td>
				<td class="ci">rj</td><td class="ci">Reject</td><td class="ci">User can delete unauthorized record (IHLD,INAU)</td></tr>
			<tr><td class="ce">ls</td><td class="ce">List Live</td><td class="ce">User can list live records</td>
				<td class="ce">is</td><td class="ce">List Inau</td><td class="ce">User can list unauthorized records</td></tr>
			<tr><td class="ce">hs</td><td class="ce">List History</td><td class="ce">User can list history records</td>
				<td class="ce">df</td><td class="ce">List Enquiry</td><td class="ce">User can list enquiry records</td></tr>
			<tr><td class="ce">ex</td><td class="ce">Export</td><td class="ce">User can export records to csv</td>
				<td class="ce">&nbsp</td><td class="ce">&nbsp</td><td class="ce">&nbsp</td></tr>
		</table>
	
	<div id="sidetree">
	<div class="treeheader">&nbsp;</div>
	<div id="sidetreecontrol"> <a href="?#">Collapse All</a> | <a href="?#">Expand All</a> </div>
_HTML_;
		$HTML = $HTML.$treemenu->roleselect(false,$spid,$current_no)."</div>";			
		print ($HTML);
	}
	
	function printChangePasswordForm()
	{
		$HTML = '';
		$HTML = "<table cellspacing=2 class='ff'>\n";
		$HTML .= "<tr valign='center'><td class='ff'>".form::label("cp_oldpasswd","Old Password")." :</td><td>".form::password("cp_oldpasswd","","")."</td></tr>\n"; 
		$HTML .= "<tr valign='center'><td class='ff'>".form::label("cp_newpasswd","New Password")." :</td><td>".form::password("cp_newpasswd","","")."</td></tr>\n";
		$HTML .= "<tr valign='center'><td class='ff'>".form::label("cp_conpasswd","Confirm Password")." :</td><td>".form::password("cp_conpasswd","","")."</td></tr>\n";
		$HTML .= "</table>";
		$HTML .= "<span id='cp_logintext'></span><span id='cp_passtext'></span>";
		$HTML .= '<input type="hidden" id="cp_isloginok" name="cp_isloginok" value="-1"/>';
		print $HTML;
	}
	
	function printPopOutCheckBoxes($querystr,$fields,$table,$idfield,$limit,$items,$inpfld=false)
	{
		$selection = ''; $selected_ids=''; $checklist = array();
		$result = $this->sitedb->execute_select_query($querystr);
		$arr = (array) $result;
		
		if($items !="" )
		{
			$tmplist = preg_split('/,/',$items);
			foreach($tmplist as $key => $row)
			{
				$plist = preg_split('/:/',$row);
				$checklist[$plist[0]] = $plist[1];
			}
		}
	
		$selection = "\n<td><table cellspacing=2>\n";
		foreach($arr as  $key => $row)
		{
			$qty = 1;
			if(array_key_exists($row->$idfield, $checklist))
			{
				$checked ='checked';
				$qty = $checklist[$row->$idfield];
			}
			else{$checked ='';}
			$selection .= '<tr valign="center">';
			$row_id = str_replace(".","_",$row->$idfield);
			if($inpfld)
			{
				$html = sprintf('<td class="ff"><input type="text" class="ff" id="%s_inp" name="%s_inp" value="%s" size="5" onchange=window.product.setCheckBoxItems() /></td>',$row_id,$row_id,$qty);
				$selection .= $html;
			}
			$html = sprintf('<td class="ff"><input type="checkbox" class="ff" id="%s" name="%s" value="%s" %s onchange=window.product.setCheckBoxItems() /></td>',$row_id,$row_id,$row->$idfield,$checked);
			$selected_ids .= $row_id.",";
			$selection .= $html;	
			$html = sprintf('<td class="ff">%s, %s, %s </td>',$row->product_id, $row->type,$row->product_description);
			$selection .= $html;	
			$selection .= "</tr>\n";
		}
		$selection .= '</table></td>';
		$selected_ids = substr_replace($selected_ids, '', -1);
		$selection .= sprintf('<input type="hidden" id="selids" name="selids" value="%s">',$selected_ids);
		print $selection;
	}

	function getUserBranch($idname,$active="Y")
	{
		$querystr	= sprintf('select branch_id from vw_userbranches where idname="%s" and active="%s";',$idname,$active);
		$result		= $this->sitedb->execute_select_query($querystr);
		$branch_id	= $result[0]->branch_id;
		return $branch_id;
	}

	function getCustomerId($id,$firstname,$lastname,$controller)
	{
		$lastname = str_replace(" ","",$lastname);
		$lastname = str_replace("'","",$lastname); 
		$lastname = str_replace("-","",$lastname); 
		$customer_id = "";

		$querystr = sprintf('select indexfield,tb_live,tb_inau from params where controller = "%s"',$controller);
		if($result = $this->sitedb->execute_select_query($querystr))
		{
			$table = $result[0]->tb_live;
			$table_is = $result[0]->tb_inau;
			$field = $result[0]->indexfield;
		
			if(strlen($lastname) == 2){ $lastname = $lastname."0";} elseif (strlen($lastname) == 1){$lastname = $lastname."00";}
		
			$L = substr($lastname,0,3);
			$F = substr($firstname,0,1);
			$N = "0001";
			$customer_id = strtoupper($L.$F.$N);
		
			while($this->sitedb->isDuplicateUniqueId($table,$field,$id,$customer_id) || $this->sitedb->isDuplicateUniqueId($table_is,$field,$id,$customer_id))
			{
				$N++;
				$N = str_pad($N, 4, "0", STR_PAD_LEFT);
				$customer_id = strtoupper($L.$F.$N);
			}
		}
		return $customer_id;
	}
		
	function getNextDateTypeId($controller,$ctrl_id,$prefix,$setid=false)
	{
		$id = "";
		$querystr = sprintf('select indexfield,tb_live,tb_inau from params where controller = "%s"',$controller);
		if($result = $this->sitedb->execute_select_query($querystr))
		{
			$table = $result[0]->tb_live;
			$table_is = $result[0]->tb_inau;
			$field = $result[0]->indexfield;

			$P = $prefix;
			$D = date('Ymd');
			$count = 1;
			$N = str_pad($count, 4, "0", STR_PAD_LEFT);
			$gen_id = strtoupper($P.$D."-".$N);
	
			while($this->sitedb->isDuplicateUniqueId($table,$field,$gen_id,$gen_id) || $this->sitedb->isDuplicateUniqueId($table_is,$field,$gen_id,$gen_id))
			{
				$count++;
				$N = str_pad($count, 4, "0", STR_PAD_LEFT);
				$gen_id = strtoupper($P.$D."-".$N);
			}
		}
		if($setid)
		{
			$querystr = sprintf('update %s set %s = "%s" where id = "%s"',$table_is,$field,$gen_id,$ctrl_id);
			$result = $this->sitedb->executeNonSelectQuery($querystr);
		}
		return $gen_id;
	}

	function format_info($info)
	{
		$format = $_REQUEST['format'];
		print $RESULT = "";
		$format = str_replace("*","%s",$format);
		$format = str_replace(";",",",$format); 
		$format = str_replace("_"," ",$format); 
		if(!($info==""))
		{
			$RESULT = sprintf($format,$info);
		}
		print $RESULT;
	}

	function getAge($dob)
	{
		if($dob == null) {return 0;}
		
		$date1 = time();
		$arr = preg_split('/-/',$dob);
		$date2 = mktime (0,0,0,$arr[1],$arr[2],$arr[0]);
		if($arr[0] < 1902) {return 0;}
		$datediff = $date1 - $date2;
		$fullyear = floor($datediff /(60*60*24*365));
		return $fullyear;
		
		//php 5.3
		//$age = date_diff(date_create($dob),date_create('now'))->y;
		//return age;
	}

	function get_hours($mins)
	{
		if($mins == null) {return 0;}
		return $mins/60;
	}
	
	function get_period_string($param)
	{
		$dates = preg_split('/,/',$param);
		if($dates[0] == null || $dates[1] == null) {return 0;}
		//$diff = abs(strtotime($dates[1]) - strtotime($dates[0]));
		$diff = strtotime($dates[1]) - strtotime($dates[0]);
		if($diff > -1)
		{
			$total_days = ($diff / (60*60*24))+1;
			$weeks = floor($total_days / 7);
			$days = $total_days % 7;
			if($days == 1){$ds = "day";}else{$ds = "days";}
			if($weeks == 1){$ws = "week";}else{$ws = "weeks";}
			$periodstr = sprintf('%s %s, %s %s',$weeks,$ws,$days,$ds); 
			return $periodstr;
		}
		else
		{
			return "End Date before Start Date";
		}
	}

	function get_orderbalance_info()
	{
		$param		= preg_split('/,/',$_REQUEST['parameter']);
		$payment_id	= $param[0]; 
		$order_id	= $param[1]; 
		$amount		= $param[2]; 
		
		// order/invoice total
		$order_total = $this->getOrderTotal($order_id);					

		// total previous payments excluding current record
		$querystr	= sprintf('select sum(amount) as payment_total from payments where order_id ="%s" and payment_id != "%s" and payment_status = "VALID" ',$order_id,$payment_id);
		$result		= $this->sitedb->execute_select_query($querystr);
		$payment_total = $result[0]->payment_total;

		$balance		= number_format($order_total - $amount - $payment_total,2);	
		$order_total	= number_format($order_total,2);
		$payment_total	= number_format($payment_total,2);
		$info_str		= sprintf("ORDER TOTAL: %s, OTHER PAYMENTS: %s, BALANCE: %s",$order_total,$payment_total,$balance);
		return $info_str;
	}

	function getOrderTotal($order_id)
	{
		$querystr	= sprintf('select sum(func_OrderDetailOrderTotal(qty,unit_price,discount_amount,tax_percentage,taxable,discount_type)) as order_total from orderdetails where order_id ="%s"',$order_id);
		$result		= $this->sitedb->execute_select_query($querystr);
		return $result[0]->order_total;
	}

	function getOrderPaymentTotal($order_id)
	{
		$querystr	= sprintf('select sum(amount) as payment_total from payments where order_id ="%s" and payment_status = "VALID"',$order_id);
		$result		= $this->sitedb->execute_select_query($querystr);
		return $result[0]->payment_total;
	}

} // End Core_Ajaxtodb
