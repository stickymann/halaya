<?php
print "Processsing Inventory.....\n\n";

require_once(dirname(__FILE__).'/hsiconfig.php');
require_once(dirname(__FILE__).'/fileops.php');
require_once(dirname(__FILE__).'/inventoryops.php');

$cfg	  = new HSIConfig();
$config   = $cfg->get_config();
$fileops  = new FileOps($config);
$filelist = $fileops->get_all_filenames_in_directory($config['current_import']);
print "\n";
//print_r($filelist);
$inventoryops = new InventoryOps();
$inventoryops->set_inventory_filename($filelist[0]);
print $inventoryops->get_inventory_filepath()."\n";
$inventoryops->set_inventory_data();
print_r( $inventoryops->get_inventory_data() );

?>
