<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Creates full system menutree of all leaves and nodes.<br>
 * The menu model is based on the implemetation of a nested tree.<br>
 * Documentation about implementing a nested tree can be found at<br>
 * http://www.phpriot.com/articles/nested-trees-1<br>
 * http://www.phpriot.com/articles/nested-trees-2<br>
 * It is suggested that these articles be read to gain a better understanding of how the menu model works.<br>
 * The output of the nested tree lookups form the foundation for any tree, horizontal or vertical html/javascript menus 
 * created by an unordered list, &lt;ul&gt;&lt;/ul&gt; tags.
 *
 * $Id: .php 2012-12-29 00:00:00 dnesbit $
  *
 * @package		Halaya Core
 * @module		core
 * @author		Dunstan Nesbit (dunstan.nesbit@gmail.com)
 * @copyright	(c) 2013
 * @license		
 */
class Model_MenuTreeAll extends Model
{
    	/**
         * Constructor. Set the database table name and necessary field names
         *
         * @param   string  $table          Name of the tree database table
         * @param   string  $idField        Name of the primary key ID field
         * @param   string  $parentField    Name of the parent ID field
         * @param   string  $sortField      Name of the field to sort data.
         */
        		
		function __construct($table, $idField, $parentField, $sortField)
        {
            $this->db = Database::instance();
			$this->table = $table;
            $this->fields = array('id'     => $idField,
                                  'parent' => $parentField,
                                  'sort'   => $sortField);
		}

		/**
         * A utility function to return an array of the fields
         * that need to be selected in SQL select queries
         *
         * @return  array   An indexed array of fields to select
         */
        function _get_fields()
        {
            return array($this->fields['id'], $this->fields['parent'], $this->fields['sort'],'id','nleft', 'nright', 'nlevel',
						'node_or_leaf','module','label_input','label_enquiry','url_input','url_enquiry','controls_input','controls_enquiry');
        }
 
        /**
         * Fetch the node data for the node identified by $id
         *
         * @param   int     $id     The ID of the node to fetch
         * @return  object          An object containing the node's
         *                          data, or null if node not found
         */

		/**
         * Fetch the node data for the node identified by $id
         *
         * @param   int     $id     The ID of the node to fetch
         * @return  object          An object containing the node's
         *                          data, or null if node not found
         */
        
		function get_node($id)
        {
            $querystr = sprintf('SELECT %s FROM %s WHERE %s = %d', join(',', $this->_get_fields()),
                                                                $this->table,
                                                                $this->fields['id'],
                                                                $id);
			$result = $this->db->query(Database::SELECT,$querystr);
			if ($row = $result[0])
                return $row;
            return null;
        }

		 /**
         * Fetch the descendants of a node, or if no node is specified, fetch the
         * entire tree. Optionally, only return child data instead of all descendant
         * data.
         *
         * @param   int     $id             The ID of the node to fetch descendant data for.
         *                                  Specify an invalid ID (e.g. 0) to retrieve all data.
         * @param   bool    $includeSelf    Whether or not to include the passed node in the
         *                                  the results. This has no meaning if fetching entire tree.
         * @param   bool    $childrenOnly   True if only returning children data. False if
         *                                  returning all descendant data
         * @return  array                   The descendants of the passed now
         */
        function get_descendants($id = 0, $includeSelf = false, $childrenOnly = false)
        {
            $idField = $this->fields['id'];
 
            $node = $this->get_node($id);
            if (is_null($node)) {
                $nleft = 0;
                $nright = 0;
                $parent_id = 0;
            }
            else {
                $nleft = $node['nleft'];
                $nright = $node['nright'];
                $parent_id = $node[$idField];
            }
 
            if ($childrenOnly) {
                if ($includeSelf) {
                    $querystr = sprintf('SELECT %s FROM %s WHERE %s = %d OR %s = %d ORDER BY nleft',
                                     join(',', $this->_get_fields()),
                                     $this->table,
                                     $this->fields['id'],
                                     $parent_id,
                                     $this->fields['parent'],
                                     $parent_id);
                }
                else {
                    $querystr = sprintf('SELECT %s FROM %s WHERE %s = %d ORDER BY nleft',
                                     join(',', $this->_get_fields()),
                                     $this->table,
                                     $this->fields['parent'],
                                     $parent_id);
                }
            }
            else {
                if ($nleft > 0 && $includeSelf) {
                    $querystr = sprintf('SELECT %s FROM %s WHERE nleft >= %d AND nright <= %d ORDER BY nleft',
                                     join(',', $this->_get_fields()),
                                     $this->table,
                                     $nleft,
                                     $nright);
                }
                else if ($nleft > 0) {
                    $querystr = sprintf('SELECT %s FROM %s WHERE nleft > %d AND nright < %d ORDER BY nleft',
                                     join(',', $this->_get_fields()),
                                     $this->table,
                                     $nleft,
                                     $nright);
                }
                else {
                    $querystr = sprintf('SELECT %s FROM %s ORDER BY nleft',
                                     join(',', $this->_get_fields()),
                                     $this->table);
                }
            }
 
            $result = $this->db->query(Database::SELECT,$querystr);
			$arr = array();
			foreach ($result as $row)
			{
				$arr[ $row[$idField] ] = $row;
			}
			return $arr;
	     }

		/**
         * Fetch the children of a node, or if no node is specified, fetch the
         * top level items.
         *
         * @param   int     $id             The ID of the node to fetch child data for.
         * @param   bool    $includeSelf    Whether or not to include the passed node in the
         *                                  the results.
         * @return  array                   The children of the passed node
         */
        function get_children($id = 0, $includeSelf = false)
        {
            return $this->get_descendants($id, $includeSelf, false);
        }

		/**
         * Fetch the path to a node. If an invalid node is passed, an empty array is returned.
         * If a top level node is passed, an array containing on that node is included (if
         * 'includeSelf' is set to true, otherwise an empty array)
         *
         * @param   int     $id             The ID of the node to fetch child data for.
         * @param   bool    $includeSelf    Whether or not to include the passed node in the
         *                                  the results.
         * @return  array                   An array of each node to passed node
         */
        function get_path($id = 0, $includeSelf = false)
        {
            $node = $this->get_node($id);
            if (is_null($node))
                return array();
 
            if ($includeSelf) {
                $querystr = sprintf('SELECT %s FROM %s WHERE nleft <= %d AND nright >= %d ORDER BY nlevel',
                                 join(',', $this->_get_fields()),
                                 $this->table,
                                 $node->nleft,
                                 $node->nright);
            }
            else {
                $querystr = sprintf('SELECT %s FROM %s WHERE nleft < %d AND nright > %d ORDER BY nlevel',
                                 join(',', $this->_get_fields()),
                                 $this->table,
                                 $node->nleft,
                                 $node->nright);
            }
 			
            $idField = $this->fields['id'];
			$result = $this->db->query(Database::SELECT,$querystr);
			$arr = array();
			foreach ($result as $row)
			{
				$arr[ $row[$idField] ] = $row;
			}
			return $arr;
        }
		
		/**
         * Check if one node descends FROM another node. If either node is not
         * found, then false is returned.
         *
         * @param   int     $descendant_id  The node that potentially descends
         * @param   int     $ancestor_id    The node that is potentially descended FROM
         * @return  bool                    True if $descendant_id descends FROM $ancestor_id, false otherwise
         */
        function is_descendant_of($descendant_id, $ancestor_id)
        {
            $node = $this->get_node($ancestor_id);
            if (is_null($node))
                return false;
 
            $querystr = sprintf('SELECT COUNT(*) AS is_descendant FROM %s WHERE %s = %d AND nleft > %d AND nright < %d',
                             $this->table,
                             $this->fields['id'],
                             $descendant_id,
                             $node->nleft,
                             $node->nright);
			
			$result = $this->db->query(Database::SELECT,$querystr);
			if ($row = $result[0])
			{
				return $row['is_descendant'] > 0;
			}
			return false;
        }
		
		/**
         * Check if one node is a child of another node. If either node is not
         * found, then false is returned.
         *
         * @param   int     $child_id       The node that is possibly a child
         * @param   int     $parent_id      The node that is possibly a parent
         * @return  bool                    True if $child_id is a child of $parent_id, false otherwise
         */
        function is_child_of($child_id, $parent_id)
        {
            $querystr = sprintf('SELECT COUNT(*) AS is_child FROM %s WHERE %s = %d AND %s = %d',
                             $this->table,
                             $this->fields['id'],
                             $child_id,
                             $this->fields['parent'],
                             $parent_id);
 
            $result = $this->db->query(Database::SELECT,$querystr);
			if ($row = $result[0])
			{
				return $row['is_descendant'] > 0;
			}
			return false;
        }

		/**
         * Find the number of descendants a node has
         *
         * @param   int     $id     The ID of the node to search for. Pass 0 to count all nodes in the tree.
         * @return  int             The number of descendants the node has, or -1 if the node isn't found.
         */
        function num_descendants($id)
        {
            if ($id == 0) 
			{
                $querystr = sprintf('SELECT COUNT(*) AS num_descendants FROM %s', $this->table);
                $result = $this->db->query(Database::SELECT,$querystr);
				if ($row = $result[0])
				{
					return (int) $row['num_descendants'];
				}
            }
            else 
			{
                $node = $this->get_node($id);
                if (!is_null($node)) 
				{
                    return ($node['nright'] - $node['nleft'] - 1) / 2;
                }
            }
            return -1;
        }
		
		/**
         * Find the number of children a node has
         *
         * @param   int     $id     The ID of the node to search for. Pass 0 to count the first level items
         * @return  int             The number of descendants the node has, or -1 if the node isn't found.
         */
        function num_children($id)
        {
            $querystr = sprintf('SELECT COUT(*) AS num_children FROM %s WHERE %s = %d',
                             $this->table,
                             $this->fields['parent'],
                             $id);
			$result = $this->db->query(Database::SELECT,$querystr);
			if ($row = $result[0])
			{
				return (int) $row['num_descendants'];
			}
            return -1;
        }

		/**
         * Fetch the tree data, nesting within each node references to the node's children
         *
         * @return  array       The tree with the node's child data
         */
        function get_tree_with_children()
        {
            $idField = $this->fields['id'];
            $parentField = $this->fields['parent'];
 
            $querystr = sprintf('SELECT %s FROM %s ORDER BY %s',
                             join(',', $this->_get_fields()),
                             $this->table,
                             $this->fields['sort']);

			$result = $this->db->query(Database::SELECT,$querystr);
			
            // create a root node to hold child data about first level items
            //$root = new stdClass;
            //$root->$idField = 0;
            //$root->children = array();
			
			$root = array();
            $root[$idField] = 0;
            $root['children'] = array();

            $arr = array($root);
 
            // populate the array and create an empty children array
			
			foreach ($result as $row)
			{
				$row['children'] = array();
				$arr[ $row[$idField] ] = $row;
			}

			// now process the array and build the child data
            foreach ($arr as $id => $row) {
                if (isset( $row[$parentField] ))
                    $arr[ $row[$parentField] ]['children'][$id] = $id;
            }
             return $arr;
        }
		
		 /**
         * Generate the tree data. A single call to this generates the n-values for
         * 1 node in the tree. This function assigns the passed in n value as the
         * node's nleft value. It then processes all the node's children (which
         * in turn recursively processes that node's children and so on), and when
         * it is finally done, it takes the update n-value and assigns it as its
         * nright value. Because it is passed as a reference, the subsequent changes
         * in subrequests are held over to when control is returned so the nright
         * can be assigned.
         *
         * @param   array   &$arr   A reference to the data array, since we need to
         *                          be able to update the data in it
         * @param   int     $id     The ID of the current node to process
         * @param   int     $level  The nlevel to assign to the current node
         * @param   int     &$n     A reference to the running tally for the n-value
         */
        function _generate_tree_data(&$arr, $id, $level, &$n)
        {
            $arr[$id]['nlevel'] = $level;
            $arr[$id]['nleft'] = $n++;
 
            // loop over the node's children and process their data
            // before assigning the nright value
            foreach ($arr[$id]['children'] as $child_id) {
                $this->_generate_tree_data($arr, $child_id, $level + 1, $n);
            }
            $arr[$id]['nright'] = $n++;
        }
				
		/**
         * Rebuilds the tree data and saves it to the database
         */
        public function rebuild()
        {
            $data = $this->get_tree_with_children();
 
            $n = 0; // need a variable to hold the running n tally
            $level = 0; // need a variable to hold the running level tally
 
            // invoke the recursive function. Start it processing
            // on the fake "root node" generated in getTreeWithChildren().
            // because this node doesn't really exist in the database, we
            // give it an initial nleft value of 0 and an nlevel of 0.
            $this->_generate_tree_data($data, 0, 0, $n);
 
            // at this point the the root node will have nleft of 0, nlevel of 0
            // and nright of (tree size * 2 + 1)
 
            foreach ($data as $id => $row) 
			{
 
                // skip the root node
                if ($id == 0)
                    continue;
 
                $querystr = sprintf('UPDATE %s SET nlevel = %d, nleft = %d, nright = %d WHERE %s = %d',
                                 $this->table,
                                 $row['nlevel'],
                                 $row['nleft'],
                                 $row['nright'],
                                 $this->fields['id'],
                                 $id);
				$result = $this->db->query(Database::UPDATE,$querystr);	
            }
        }

		/**
         * Gets number of parent menus
         *
         * @return  int       number of parent menus
         */
		public function get_number_parent_menus()
        {
            $querystr = sprintf('SELECT COUNT(%s) AS num FROM %s WHERE %s = 0',
							$this->fields['id'], 
							$this->table,
                            $this->fields['parent']
                            );
			$result = $this->db->query(Database::SELECT,$querystr);
			if ($row = $result[0])
			{
				return (int) $row['num'];
			}
        }
		
		/**
         * Gets array of records of top level menus
         *
         * @return	array	array containing records of top level menus 
         */
		public function get_top_level_menus()
        {
            $querystr = sprintf('SELECT menu_id,module,url_input FROM %s WHERE %s = 0 ORDER BY sortpos',$this->table,$this->fields['parent']);
			$result = $this->db->query(Database::SELECT,$querystr);
			foreach ($result as $row)
			{
				$arr[ $row['menu_id'] ] = $row;
			}
			return $arr;
        }
		
		/**
         * Gets array of records of top level menus except login module
         *
         * @return	array	array containing records of top level menus 
         */
		public function get_all_top_level_menus_nologin()
		{
			$querystr = sprintf('SELECT menu_id,module,url_input FROM %s WHERE %s = 0 AND module <> "%s" ORDER BY sortpos',$this->table,$this->fields['parent'],"login");  		
			$result = $this->db->query(Database::SELECT,$querystr);
			foreach ($result as $row)
			{
				$arr[ $row['menu_id'] ] = $row;
			}
			return $arr;
		}

} // End Model_MenuTreeAll

