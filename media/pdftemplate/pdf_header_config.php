<?php defined('SYSPATH') OR die('No direct access allowed.');

function get_pdf_header_config()
{
  return array
  (
	/**
    * The following options are available for your pdf header:
    *
    * string   logo     server hostname, or socket
    * string   org      organisation/company name
    * string   street   street address
    * string   area     area/city address
    * string   country  country
    * strinh   tel      organisation/company telephonu number
    * string   email    organisation/company email address
    * string   website  organisation/company website
    * string   facebook organisation/company facebook url
    * string   taxreg   tax/vat registration number   
    **/
    'logo'      => 'media/pdftemplate/images/default.logo.pdfheader.png',
    'org'       => 'Model System.',
    'street'    => 'Some Street',
    'area'      => 'Somewhere',
    'country'   => 'Trinidad, W.I.',
	 'tel'      => '555-5555',
     'email'    => 'modsys@mailserver.com',
     'website'  => 'www.modsys.com',
     'facebook' => 'www.facebook.com/modsys',
     'taxreg'   => 'xxxxxx'
  );
}  
