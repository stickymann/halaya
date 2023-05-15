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
    'org'       => 'GPS Rescue Ltd.',
    'street'    => '55 Maloney Street',
    'area'      => 'Petit Bourg',
    'country'   => 'Trinidad, W.I.',
	 'tel'      => '675-8000, 222-5888, 685-4477',
     'email'    => 'gpsrescue@gmail.com',
     'website'  => 'www.gpsrescuett.com',
     'facebook' => 'www.facebook.com/gpsrescue',
     'taxreg'   => '179127'
  );
}  
