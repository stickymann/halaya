<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Standard batchinvoices report. 
 *
 * $Id: Batchinvoices.php 2013-01-11 00:00:00 dnesbit $
 *
 * @package		Halaya Core
 * @module	    core
 * @author      Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright   (c) 2013
 * @license   
 */
class Controller_Core_Report_Batchinvoices extends Controller_Core_Sitereport
{
	public function __construct()
    {
		parent::__construct("batchinvoices_rpt");
	}	
		
	public function action_index()
    {
      $this->process_request();
    }

	public function report_run()
	{
		$batch_id = $this->OBJPOST['batch_id'];
		$table = 'batchinvoices';
		$querystr = sprintf('select batch_description from %s where batch_id = "%s"', $table,$batch_id);
		$desc = $this->sitemodel->execute_select_query($querystr);
		
		if($desc)
		{
			$batch_description = $desc[0]->batch_description;
			$table = 'batchinvoicedetails';
			$fields = array('id','order_id','invoice_id','alt_invoice_id');
			$querystr = sprintf('select %s from %s where batch_id = "%s"', join(',',$fields),$table,$batch_id);
			$batch_res = $this->sitemodel->execute_select_query($querystr);
			if($batch_res)
			{
				foreach($batch_res as $row => $linerec)
				{
					$linerec = (array)$linerec;
					$table = 'vw_orderbalances';
					$order_id = $linerec['order_id'];
					$fields = array
					(
						'order_id','branch_id','is_co','cc_id','inputter','first_name','last_name','customer_type','address1','address2','city',
						'phone_mobile1','phone_home','phone_work','current_no','order_date','invoice_date','order_status','quotation_date',
						'order_total','extended_total','tax_total','payment_total','balance','discount_total','order_details','payment_type','invoice_note'
					);
					$querystr = sprintf('select %s from %s where order_id = "%s"', join(',',$fields),$table,$order_id);
					$order_res = $this->sitemodel->execute_select_query($querystr);
					$item = (array) $order_res[0];
					$merge_r = array_merge($linerec,$item);
					$batch_res[$row] = $merge_r;
					$result[$row] = array
					(
						'invoice_id'=>$merge_r['invoice_id'], 'alt_invoice_id'=>$merge_r['alt_invoice_id'], 'order_id'=>$merge_r['order_id'],
						'order_date'=>$merge_r['order_date'], 'first_name'=>$merge_r['first_name'], 'last_name'=>$merge_r['last_name'],
						'order_details'=>$merge_r['order_details'], 'extended_total'=>$merge_r['extended_total'], 'tax_total'=>$merge_r['tax_total'],
						'order_total'=>$merge_r['order_total'], 'payment_total'=>$merge_r['payment_total'], 'balance'=>$merge_r['balance'],
						'payment_type'=>$merge_r['payment_type']
					);
				}	
			
				$num = rand(0,999999);
				$num = str_pad($num, 6, "0", STR_PAD_LEFT);
				$invoices	  = 'BCHI'.date("YmdHis").$num;
				$payments = 'BCHP'.date("YmdHis").$num;
				$pdfurl = ""; 
				if($this->printable)
				{
					$pdfurl = sprintf('<div id=enqprt>[ <a href=%sindex.php/core_pdfbuilder/index/%s target=_blank>Payments</a> ] ',URL::base(),$payments);
					$pdfurl .= sprintf(' [ <a href=%sindex.php/core_pdfbuilder/index/%s target=_blank>Invoices</a> ] </div>',URL::base(),$invoices)."\n";
				}
		
				$RESULT = '<div id="e" style="padding:5px 5px 5px 5px; overflow:auto;">';
				$RESULT .= sprintf('<div>Batch Id : %s<br>Batch Description : %s</div> %s',$batch_id, $batch_description, $pdfurl);
				$RESULT .= '<table id="rpttbl" width="98%">'."\n";
				$firstpass = true;
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
				$RESULT .= '</div>';
				$this->content->pagebody = $RESULT;
		
				$config['batch_id']	= $batch_id;
				$config['invoices']	= $invoices;
				$config['payments']	= $payments;
				$config['results']  = $batch_res;
				$config['idname']		= Auth::instance()->get_user()->idname;
				$config['controller']	= $this->controller;
				$config['type']		= "report";
				$this->create_pdf($config);
			}
			else
			{
				$this->content->pagebody = '<div id="i"><div class="frmmsg">No Result.</div></div>';		
			}
		}
		else
		{
			$this->content->pagebody = '<div id="i"><div class="frmmsg">No Result.</div></div>';		
		}
	}
	
	function make_pdf_xml($data)
	{
		$controller = "orders_enq";
		$enqdb = new Model_EnqDB();
		$enqdb->get_enq_formfields($controller,$enqparam['fieldnames'],$enqparam['labels'],$enqparam['filterfields']);
		$labels = $enqparam['labels'];
		
	$item = $data['item'];
		$id = $item['alt_invoice_id'];			$order_id = $item['order_id'];
		$branch_id = $item['branch_id'];		$inputter = $item['inputter'];
		$is_co = $item['is_co'];				$cc_id = $item['cc_id'];
		$first_name = $item['first_name'];		$last_name = $item['last_name'];
		$customer_type = $item['customer_type'];	$city = $item['city'];
		$address1 = $item['address1'];			$address2 = $item['address2'];
		$phone_mobile1 = $item['phone_mobile1'];	$phone_home = $item['phone_home'];		
		$phone_work = $item['phone_work'];		$current_no = $item['current_no'];
		$invoice_date = $item['invoice_date'];	$quotation_date = $item['quotation_date'];
		$order_total = $item['order_total'];		$payment_total = $item['payment_total']; 
		$balance = $item['balance'];				$sub_total = $item['extended_total'];
		$tax_total = $item['tax_total'];			$discount_total = $item['discount_total'];
		$order_status = $item['order_status'];		$payment_type = $item['payment_type'];
		$invoice_note = $item['invoice_note'];
				
		$label_id = $labels['id'];							$label_order_id = $labels['order_id'];
		$label_branch_id = $labels['branch_id'];			$label_inputter = $labels['inputter'];
		$label_is_co = $labels['is_co'];					$label_cc_id = $labels['cc_id'];
		$label_first_name = $labels['first_name'];			$label_last_name = $labels['last_name'];
		$label_customer_type = $labels['customer_type'];	$label_city = $labels['city'];
		$label_address1 = $labels['address1'];				$label_address2 = $labels['address2'];
		$label_phone_mobile1 = $labels['phone_mobile1'];	$label_phone_home = $labels['phone_home'];		
		$label_phone_work = $labels['phone_work'];			$label_current_no = $labels['current_no'];
		$label_invoice_date = $labels['invoice_date'];		$label_quotation_date = $labels['quotation_date'];
		$label_order_total = $labels['order_total'];		$label_payment_total = $labels['payment_total']; 
		$label_balance = $labels['balance'];				$label_sub_total = $labels['extended_total'];
		$label_tax_total = $labels['tax_total'];			$label_discount_total = $labels['discount_total'];
		$label_order_status = $labels['order_status'];		$label_payment_type = $labels['payment_type'];
		$label_invoice_note = $labels['invoice_note'];
	
	$XML=<<<_XML_
<fields>
	<id><label>$label_id</label><value>$id</value></id>	
	<order_id><label>$label_order_id</label><value>$order_id</value></order_id>
	<branch_id><label>$label_branch_id</label><value>$branch_id</value></branch_id>
	<inputter><label>$label_inputter</label><value>$inputter</value></inputter>
	<is_co><label>$label_is_co</label><value>$is_co</value></is_co>
	<cc_id><label>$label_cc_id</label><value>$cc_id</value></cc_id>
	<first_name><label>$label_first_name</label><value>$first_name</value></first_name>
	<last_name><label>$label_last_name</label><value>$last_name</value></last_name>
	<customer_type><label>$label_customer_type</label><value>$customer_type</value></customer_type>
	<address1><label>$label_address1</label><value>$address1</value></address1>
	<address2><label>$label_address2</label><value>$address2</value></address2>
	<city><label>$label_city</label><value>$city</value></city>
	<phone_mobile1><label>$label_phone_mobile1</label><value>$phone_mobile1</value></phone_mobile1>
	<phone_home><label>$label_phone_home</label><value>$phone_home</value></phone_home>
	<phone_work><label>$label_phone_work</label><value>$phone_work</value></phone_work>
	<quotation_date><label>$label_quotation_date</label><value>$quotation_date</value></quotation_date>
	<invoice_date><label>$label_invoice_date</label><value>$invoice_date</value></invoice_date>
	<current_no><label>$label_current_no</label><value>$current_no</value></current_no>
	<sub_total><label>$label_sub_total</label><value>$sub_total</value></sub_total>
	<discount_total><label>$label_discount_total</label><value>$discount_total</value></discount_total>
	<tax_total><label>$label_tax_total</label><value>$tax_total</value></tax_total>
	<order_total><label>$label_order_total</label><value>$order_total</value></order_total>
	<payment_total><label>$label_payment_total</label><value>$payment_total</value></payment_total>
	<balance><label>$label_balance</label><value>$balance</value></balance>
	<order_status><label>$label_order_status</label><value>$order_status</value></order_status>
	<payment_type><label>$label_payment_type</label><value>$payment_type</value></payment_type>
	<invoice_note><label>$label_invoice_note</label><value>$invoice_note</value></invoice_note>
</fields>
_XML_;
	return $XML;
	}

	public function create_invoices($data,$arr)
	{
		$arr['pdf_template'] = "BATCHINVOICES";
		
		$batch_id = $data['batch_id'];
		$table = 'batchinvoicedetails';
		$fields = array('id','order_id','alt_invoice_id');
		$querystr = sprintf('select %s from %s where batch_id = "%s"', join(',',$fields),$table,$batch_id);
		
		$result = $data['results'];
	
		if($result)
		{
			$xmldata = "";
			foreach($result as $key => $row)
			{
				//$data['order_id'] = $row['order_id'];
				//$data['alt_invoice_id'] = $row['alt_invoice_id'];
				$data['item'] = $row;
				$xmldata .= $this->make_pdf_xml($data)."\n";
			}
			$pdf_xml = "<?xml version='1.0' standalone='yes'?>"."\n"."<formfields>"."\n";
			$pdf_xml .= $xmldata;
			$pdf_xml .= "</formfields>"."\n";
			$pdf_xml = str_replace('&','and',$pdf_xml); 

			$data['pdf']->insert_into_pdf_table_no_delete($arr);
			$pdftxt = new Controller_Core_Sysadmin_Csv();
			$pdftxt->insert_into_csv_table($arr['pdf_id'],$pdf_xml,$this->controller,$arr['idname'],"xml");
		}
	}
	
	public function create_pdf($data)
	{
		//add xml data to pdfs_is table
		$pdf_xml	= "<batch>".$data['batch_id']."</batch>";
		$pdf_audit	= "<audit><printuser></printuser><printdate></printdate></audit>";
		if($this->rptparam['printuser'] || $this->rptparam['printdatetime'] )
		{
			$pdf_audit = "<audit>"; 
			if($this->rptparam['printuser']) {$pdf_audit .= sprintf('<printuser>Printed By : %s</printuser>',$data['idname']);} 
			if($this->rptparam['printdatetime']) {$pdf_audit .= sprintf('<printdate>Print Date : %s</printdate>',date('Y-m-d H:i:s'));} 
			$pdf_audit .= "</audit>"."\n"; 
		}
		$pdf_data = "<?xml version='1.0' standalone='yes'?>"."\n"."<formfields>"."\n";
		$pdf_data .= $pdf_xml."\n".$pdf_audit;
		$pdf_data .= "</formfields>"."\n";
		$pdf_data = str_replace('&','and',$pdf_data); 
		
		$pdf = new Controller_Core_Sysadmin_Pdf();
		$data['pdf'] = $pdf;
		$arr['pdf_id']			= $data['payments'];
		$arr['pdf_template']	= "BATCHINVOICESUMMARY";
		$arr['controller']		= $data['controller'];
		$arr['type']			= $data['type'];
		$arr['data']			= $pdf_data;
		$arr['datatype']		= "xml";
		$arr['idname']			= $data['idname'];
		
		if( $pdf->delete_from_pdf_table($arr) )
		{
			//wait for deletions
		}
		$pdf->insert_into_pdf_table_no_delete($arr);
		$arr['pdf_id']	= $data['invoices'];
		$this->create_invoices($data,$arr);
	}

}//End Controller_Core_Report_Batchinvoice 
