<?php
print "Processsing Customer.....\n\n";

require_once(dirname(__FILE__).'/hsiconfig.php');
require_once(dirname(__FILE__).'/fileops.php');
require_once(dirname(__FILE__).'/customerops.php');

$cfg	  = new HSIConfig();
$config   = $cfg->get_config();
$fileops  = new FileOps($config);
$filelist = $fileops->get_all_filenames_in_directory($config['current_import']);
print "\n";
//print_r($filelist);
$customerops = new CustomerOps();
$customerops->set_customer_filename($filelist[0]);
print $customerops->get_customer_filepath()."\n";
$customerops->set_customer_data();
//print_r( $customerops->get_customer_data() );
$customerops->process_customer();

?>
