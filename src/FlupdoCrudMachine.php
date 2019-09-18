<?php
/*
 * Copyright (c) 2013-2017, Josef Kufner  <josef@kufner.cz>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 */

namespace Smalldb\StateMachine;

/**
 * Simple state machine for typical CRUD entities accessed via Flupdo.
 *
 * ### Configuration Schema
 *
 * The state machine is configured using JSON object passed to the constructor
 * (the `$config` parameter). The object must match the following JSON schema
 * ([JSON format](FlupdoCrudMachine.schema.json)):
 *
 * @htmlinclude doxygen/html/FlupdoCrudMachine.schema.html
 */
class FlupdoCrudMachine extends FlupdoMachine
{
	/// Relation defining shich machine owns this machine
	protected $owner_relation = null;

	/// Transition of owner to check when creating this machine
	protected $owner_create_transition = null;

	/// Nested-sets configuration
	protected $nested_sets_table_columns = array(
		'id' => 'id',
		'parent_id' => 'parent_id',
		'left' => 'tree_left',
		'right' => 'tree_right',
		'depth' => 'tree_depth',
	);

	/// Enable nested-sets tree?
	protected $nested_sets_enabled = false;

	/// Order by this column
	protected $nested_sets_order_by = 'id';

	/// Generate random id?
	protected $generate_random_id = null;

	/// Set this column to CURRENT_TIMESTAMP on create transition
	protected $time_created_table_column = null;
	/// Set this column to CURRENT_TIMESTAMP on edit transition. If MySQL is in use, it is better to use CURRENT_TIMESTAMP column feature.
	protected $time_modified_table_column = null;

	/**
	 * @copydoc FlupdoMachine::configureMachine()
	 */
	protected function configureMachine(array $config)
	{
		parent::configureMachine($config);

		$this->loadMachineConfig($config, [
			'user_id_table_column', 'owner_relation', 'owner_create_transition',
			'generate_random_id',
			'time_created_table_column', 'time_modified_table_column'
		]);

		// nested-sets configuration
		if (isset($config['nested_sets'])) {
			$ns = $config['nested_sets'];
			if (isset($ns['table_columns'])) {
				$this->nested_sets_table_columns = $ns['table_columns'];
			}
			if (isset($ns['order_by'])) {
				$this->nested_sets_order_by = $ns['order_by'];
			}
			if (isset($ns['enabled'])) {
				$this->nested_sets_enabled = $ns['enabled'];
			}
		}
	}


	/**
	 * Setup basic CRUD machine.
	 */
	protected function setupDefaultMachine(array $config)
	{
		// Name of inputs and outputs with properties
		$io_name = isset($config['io_name']) ? (string) $config['io_name'] : 'item';

		// Create default transitions?
		$no_default_transitions = !empty($config['crud_machine_no_default_transitions']);	/// @deprecated

		// Exists state only
		$this->states = array_replace_recursive($no_default_transitions ? array() : array(
			'exists' => array(
				'label' => _('Exists'),
				'description' => '',
			),
		), $this->states ? : []);

		// Simple 'exists' state if not state select is not defined
		if ($this->state_select === null) {
			$this->state_select = '"exists"';
		}

		// Actions
		$this->actions = array_replace_recursive(array(
			'create' => array(
				'label' => _('Create'),
				'description' => _('Create a new item'),
				'transitions' => $no_default_transitions ? array() : array(
					'' => array(
						'targets' => array('exists'),
					),
				),
				'returns' => self::RETURNS_NEW_ID,
				'block' => array(
					'inputs' => array(
						$io_name => array()
					),
					'outputs' => array(
						'ref' => 'return_value'
					),
					'accepted_exceptions' => array(
						'PDOException' => true,
					),
				),
			),
			'edit' => array(
				'label' => _('Edit'),
				'description' => _('Modify item'),
				'transitions' => $no_default_transitions ? array() : array(
					'exists' => array(
						'targets' => array('exists'),
					),
				),
				'block' => array(
					'inputs' => array(
						'ref' => array(),
						$io_name => array()
					),
					'outputs' => array(
						'ref' => 'ref'
					),
					'accepted_exceptions' => array(
						'PDOException' => true,
					),
				),
			),
			'delete' => array(
				'label' => _('Delete'),
				'description' => _('Delete item'),
				'weight' => 80,
				'transitions' => $no_default_transitions ? array() : array(
					'exists' => array(
						'targets' => array(''),
					),
				),
				'block' => array(
					'inputs' => array(
						'ref' => array(),
					),
					'outputs' => array(
					),
					'accepted_exceptions' => array(
						'PDOException' => true,
					),
				),
			),
		), $this->actions ? : []);
	}


	/**
	 * Create
	 *
	 * $ref may be nullRef, then auto increment is used.
	 */
	protected function create(Reference $ref, $properties)
	{
		// filter out unknown keys
		$properties = array_intersect_key($properties, $this->properties);

		if (!$ref->isNullRef()) {
			$properties = array_merge($properties, array_combine($this->describeId(), (array) $ref->id));
		}

		// Set times
		if ($this->time_created_table_column) {
			$properties[$this->time_created_table_column] = new \Smalldb\Flupdo\FlupdoRawSql('CURRENT_TIMESTAMP');
		}
		if ($this->time_modified_table_column) {
			$properties[$this->time_modified_table_column] = new \Smalldb\Flupdo\FlupdoRawSql('CURRENT_TIMESTAMP');
		}


		if (empty($properties)) {
			throw new \InvalidArgumentException('No valid properties provided.');
		}

		// Set owner
		if ($this->user_id_table_column) {
			$properties[$this->user_id_table_column] = $this->auth->getUserId();
		}

		// Check permission of owning machine
		if ($this->owner_relation && $this->owner_create_transition) {
			$ref_ref = $this->resolveMachineReference($this->owner_relation, $properties);
			if (!$ref_ref->machine->isTransitionAllowed($ref_ref, $this->owner_create_transition)) {
				throw new \RuntimeException(sprintf(
					'Permission denied to create machine %s because transition %s of %s is not allowed.',
					$this->machine_type, $this->owner_create_transition, $ref->machine_type
				));
			}
		}

		// Generate random ID
		if ($this->generate_random_id) {
			list($random_property) = $this->describeId();
			if (empty($properties[$random_property])) {
				$properties[$random_property] = mt_rand(1, 2147483647);
			}
		}

		// Insert
		$data = $this->encodeProperties($properties);
		$q = $this->flupdo->insert()->into($this->flupdo->quoteIdent($this->table));
		foreach ($data as $k => $v) {
			$q->insert($this->flupdo->quoteIdent($k));
		}
		$q->values([$data]);
		$n = $q->debugDump()->exec();
		if (!$n) {
			// Insert failed
			return false;
		}

		// Return ID of inserted row
		if ($ref->isNullRef()) {
			$id_keys = $this->describeId();
			$id = array();
			foreach ($id_keys as $k) {
				if (isset($properties[$k])) {
					$id[] = $properties[$k];
				} else {
					// If part of ID is missing, it must be autoincremented
					// column, otherwise the insert would have failed.
					$id[] = $this->flupdo->lastInsertId();
				}
			}
		} else {
			$id = $ref->id;
		}

		if ($this->nested_sets_enabled) {
			$this->recalculateTree();
		}

		return $id;
	}


	/**
	 * Edit
	 */
	protected function edit(Reference $ref, $properties)
	{
		// filter out unknown keys
		$properties = array_intersect_key($properties, $this->properties);

		if (empty($properties)) {
			throw new \InvalidArgumentException('No valid properties provided.');
		}

		// Set modification time
		if ($this->time_modified_table_column) {
			$properties[$this->time_modified_table_column] = new \Smalldb\Flupdo\FlupdoRawSql('CURRENT_TIMESTAMP');
		}
		// Build update query
		$q = $this->flupdo->update($this->queryGetThisTable($this->flupdo));
		$this->queryAddPrimaryKeyWhere($q, $ref->id);
		foreach ($this->encodeProperties($properties) as $k => $v) {
			if ($v instanceof \Smalldb\Flupdo\FlupdoRawSql || $v instanceof \Smalldb\Flupdo\FlupdoBuilder) {
				$q->set(array($this->flupdo->quoteIdent($k).' = ', $v));
			} else {
				$q->set($q->quoteIdent($k).' = ?', $v);
			}
		}

		// Add calculated properties
		foreach ($this->properties as $pi => $p) {
			if (!empty($p['calculated']) && isset($p['sql_update'])) {
				$q->set($q->quoteIdent($pi).' = '.$p['sql_update']);
			}
		}

		$n = $q->debugDump()->exec();

		if ($n !== FALSE) {
			if ($this->nested_sets_enabled) {
				$this->recalculateTree();
			}
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Delete
	 */
	protected function delete(Reference $ref)
	{
		// build update query
		$q = $this->flupdo->delete()->from($this->flupdo->quoteIdent($this->table));
		$this->queryAddPrimaryKeyWhere($q, $ref->id);

		$n = $q->debugDump()->exec();

		if ($n) {
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Recalculate nested-sets tree indices
	 *
	 * To use this feature a parent, left, right and depth columns must be specified.
	 *
	 * Composed primary keys are not supported yet.
	 *
	 * Three extra columns are required: tree_left, tree_right, tree_depth
	 * (ints, all nullable). This function will update them according to id
	 * and parent_id columns.
	 */
	protected function recalculateTree()
	{
		if (!$this->nested_sets_enabled) {
			throw new \RuntimeException('Nested sets are disabled for this entity.');
		}

		$q_table     = $this->flupdo->quoteIdent($this->table);
		$cols        = $this->nested_sets_table_columns;
		$c_order_by  = $this->nested_sets_order_by;
		$c_id        = $this->flupdo->quoteIdent($cols['id']);
		$c_parent_id = $this->flupdo->quoteIdent($cols['parent_id']);
		$c_left      = $this->flupdo->quoteIdent($cols['left']);
		$c_right     = $this->flupdo->quoteIdent($cols['right']);
		$c_depth     = $this->flupdo->quoteIdent($cols['depth']);

		$set = $this->flupdo->select($c_id)
			->from($q_table)
			->where("$c_parent_id IS NULL")
			->orderBy($c_order_by)
			->query();

		$this->recalculateSubTree($set, 1, 0, $q_table, $c_order_by, $c_id, $c_parent_id, $c_left, $c_right, $c_depth);
	}


	/**
	 * Recalculate given subtree.
	 *
	 * @see recalculateTree()
	 */
	private function recalculateSubTree($set, $left, $depth, $q_table, $c_order_by, $c_id, $c_parent_id, $c_left, $c_right, $c_depth)
	{
		foreach($set as $row) {
			$id = $row['id'];
			
			$this->flupdo->update($q_table)
				->set("$c_left = ?", $left)
				->set("$c_depth = ?", $depth)
				->where("$c_id = ?", $id)
				->exec();

			$sub_set = $this->flupdo->select($c_id)
				->from($q_table)
				->where("$c_parent_id = ?", $id)
				->orderBy($c_order_by)
				->query();

			$left = $this->recalculateSubTree($sub_set, $left + 1, $depth + 1,
					$q_table, $c_order_by, $c_id, $c_parent_id, $c_left, $c_right, $c_depth);
			
			$this->flupdo->update($q_table)
				->set("$c_right = ?", $left)
				->where("$c_id = ?", $id)
				->exec();

			$left++;
		}
		return $left;
	}

}

