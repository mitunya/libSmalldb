<?php
/*
 * Copyright (c) 2012, Josef Kufner  <jk@frozen-doe.net>
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
 * %Reference to one or more state machines. Allows you to invoke transitions in
 * the easy way by calling methods on this reference object. This is syntactic
 * sugar only, nothing really happens here.
 *
 * $id is per machine type unique identifier. It is always a single literal
 * or an array of literals for compound primary keys.
 *
 * Method call on this class invokes the transition.
 *
 * Read-only properties:
 *   - state = $machine->getState($id);
 *   - properties = $machine->getProperties($id);
 *   - ... see __get().
 *
 * Read one property (will load all of them):
 *   $ref['property']
 *
 * Flush property cache:
 *   unset($ref->properties);
 *
 */
class Reference implements \ArrayAccess, \Iterator
{
	/**
	 * State machine.
	 */
	protected $machine;

	/**
	 * Primary key (unique within $machine).
	 */
	protected $id;

	/**
	 * Cached state of the machine.
	 */
	protected $state_cache;

	/**
	 * Cached properties of the machine.
	 */
	protected $properties_cache;

	/**
	 * Cached values from views on machine properties.
	 */
	protected $view_cache;

	/**
	 * Persisten view cache, which is not flushed automaticaly.
	 */
	protected $persistent_view_cache = array();


	/************************************************************************//**
	 * @name	Callbacks
	 * @{
	 *
	 * Callbacks are lists of callables. Reference calls them when 
	 * something interesting happens.
	 *
	 * To register a callable simply add it to the list.
	 */

	/**
	 * List of callbacks called when reference primary key changes.
	 *
	 * Just append callable to this array:
	 *
	 * `$ref->on_pk_change[] = function($ref, $new_pk) { };`
	 */
	public $pk_changed_cb = array();

	/**
	 * List of callbacks called before transition is invoked.
	 *
	 * Just append callable to this array:
	 *
	 * `$ref->on_pk_change[] = function($ref, $transition_name, $arguments) { };`
	 */
	public $before_transition_cb = array();

	/**
	 * List of callbacks called after transition is invoked.
	 *
	 * Just append callable to this array:
	 *
	 * `$ref->on_pk_change[] = function($ref, $transition_name, $arguments, $return_value, $returns) { };`
	 */
	public $after_transition_cb = array();

	/// @}


	/**
	 * Create reference and initialize it with given ID. To copy
	 * a reference use clone keyword.
	 *
	 * TODO: Check $id to be made of scalar values only.
	 */
	public function __construct(AbstractMachine $machine, $id = null)
	{
		$this->clearCache();
		$args = func_get_args();
		$this->machine = $machine;

		if (count($args) > 2) {
			// composite primary key as multiple arguments
			array_shift($args);
			$raw_id = $args;
		} else {
			$raw_id = $id;
		}

		if (is_array($raw_id)) {
			switch (count($raw_id)) {
				case 0:
					throw new \InvalidArgumentException('Invalid ID - empty array makes no sense.');
				case 1:
					list($this->id) = $raw_id;
					break;
				default:
					$this->id = $raw_id;
					break;
			}
		} else {
			$this->id = $raw_id;
		}
	}


	/**
	 * Create pre-heated reference.
	 *
	 * @warning This may break things a lot. Be careful.
	 */
	public static function createPreheatedReference($machine, $properties)
	{
		$ref = new self($machine, null);
		$ref->properties_cache = $properties;
		$ref->state_cache = $properties['state'];

		$id_properties = $machine->describeId();
		if (count($id_properties) == 1) {
			$ref->id = $properties[$id_properties[0]];
		} else {
			$id = array();
			foreach ($id_properties as $k) {
				$id[] = $properties[$k];
			}
			$ref->id = $id;
		}

		return $ref;
	}


	/**
	 * Call all registered callbacks when event happens.
	 */
	protected function emit($callback_list)
	{
		if (empty($callback_list)) {
			return;
		}

		$args = func_get_args();
		$args[0] = $this;

		foreach ($callback_list as $cb) {
			call_user_func_array($cb, $args);
		}
	}


	/**
	 * Returns true if reference points only to machine type. Such 
	 * reference may not be used to modify any machine, however, it can be 
	 * used to invoke 'create'-like transitions.
	 */
	public function isNullRef()
	{
		return $this->id === null || $this->id === array();
	}


	/**
	 * Drop all cached data.
	 */
	protected function clearCache()
	{
		$this->state_cache = null;
		$this->properties_cache = null;
		$this->view_cache = array();
	}


	/**
	 * Function call is transition invocation. Just forward it to backend.
	 *
	 * When transition returns new ID, the reference is updated to keep 
	 * it pointing to the same state machine.
	 */
	public function __call($name, $arguments)
	{
		$this->emit($this->before_transition_cb, $name, $arguments);
		$old_id = $this->id;

		$this->clearCache();
		$t = $this;
		$r = $this->machine->invokeTransition($this, $name, $arguments, $returns, function($new_id) use ($t) {
			if (is_array($new_id) && count($new_id) == 1) {
				list($t->id) = $new_id;
			} else {
				$t->id = $new_id;
			}
		});

		$this->emit($this->after_transition_cb, $name, $arguments, $r, $returns);

		switch ($returns) {
			case AbstractMachine::RETURNS_VALUE:
				// Returned value is simply passed to caller.
				return $r;
			case AbstractMachine::RETURNS_NEW_ID:
				// When state machine ID changes, reference must be updated to point to the same machine.
				$this->emit($this->pk_changed_cb, $old_id, $this->id);
				return $this;
			default:
				throw new RuntimeException('Unknown semantics of the return value: '.$returns);
		}
	}


	/**
	 * Get data from machine
	 *
	 * If you want to retrieve both state and properties, ask for 
	 * properties first. The state may get pre-cached.
	 */
	public function __get($key)
	{
		switch ($key) {
			case 'id':
				return $this->id;
			case 'machine':
				return $this->machine;
			case 'machineType':
			case 'machine_type':
				return $this->machine->getMachineType();
			case 'state':
				if ($this->state_cache === null) {
					$this->state_cache = $this->machine->getState($this->id);
				}
				return $this->state_cache;
			case 'properties':
				if ($this->properties_cache === null) {
					$this->properties_cache = $this->machine->getProperties($this->id, $this->state_cache);
					$this->properties_cache['state'] = $this->state_cache;
				}
				return $this->properties_cache;
			case 'actions':
				return $this->machine->getAvailableTransitions($this->id);
			default:
				return $this->machine->getView($this->id, $key, $this->properties_cache, $this->view_cache, $this->persistent_view_cache);
		}
	}


	/**
	 * Flush cached data.
	 */
	public function __unset($key)
	{
		switch ($key) {
			case 'id':
			case 'machine':
			case 'machineType':
			case 'actions':
				throw new InvalidArgumentException('Property is not cached: '.$key);
			case 'state':
			case 'properties':
				$this->clearCache();
				break;
			default:
				throw new InvalidArgumentException('Unknown property: '.$key);
		}
	}


	/******************************************************************//**
	 * @}
	 * @name 	Array access for properties
	 * @{
	 */

	public function offsetExists($offset)
	{
		if ($this->properties_cache === null) {
			$this->properties_cache = $this->machine->getProperties($this->id);
		}
		return array_key_exists($offset, $this->properties_cache);
	}

	public function offsetGet($offset)
	{
		if ($this->properties_cache === null) {
			$this->properties_cache = $this->machine->getProperties($this->id);
		}
		return $this->properties_cache[$offset];
	}

	public function offsetSet($offset, $value)
	{
		throw new InvalidArgumentException('Cannot set property: Property cache is read only.');
	}

	public function offsetUnset($offset)
	{
		throw new InvalidArgumentException('Cannot unset property: Property cache is read only.');
	}


	/******************************************************************//**
	 * @}
	 * @name	Iterator interface to iterate over properties
	 * @{
	 */

	function rewind() {
		if ($this->properties_cache === null) {
			$this->properties_cache = $this->machine->getProperties($this->id);
		}
		return reset($this->properties_cache);
	}

	function current() {
		if ($this->properties_cache === null) {
			$this->properties_cache = $this->machine->getProperties($this->id);
		}
		return current($this->properties_cache);
	}

	function key() {
		if ($this->properties_cache === null) {
			$this->properties_cache = $this->machine->getProperties($this->id);
		}
		return key($this->properties_cache);
	}

	function next() {
		if ($this->properties_cache === null) {
			$this->properties_cache = $this->machine->getProperties($this->id);
		}
		return next($this->properties_cache);
	}

	function valid() {
		if ($this->properties_cache === null) {
			$this->properties_cache = $this->machine->getProperties($this->id);
		}
		return key($this->properties_cache) !== null;
	}

	/** @} */
}

