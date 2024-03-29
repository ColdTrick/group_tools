<?php

namespace ColdTrick\GroupTools;

/**
 * Handle automatic group join
 */
class AutoJoin {
	
	/**
	 * The user to try and match
	 *
	 * @var \ElggUser
	 */
	protected \ElggUser $user;
	
	/**
	 * The default auto join groups
	 *
	 * @var null|array
	 */
	protected ?array $defaults;
	
	/**
	 * Conditional rules for auto joining
	 *
	 * @var array
	 */
	protected array $configs;
	
	/**
	 * New auto join
	 *
	 * @param \ElggUser $user the user to check joins for
	 */
	public function __construct(\ElggUser $user) {
		$this->user = $user;
		
		// load additional rules
		$this->configs = group_tools_get_auto_join_configurations();
	}
	
	/**
	 * Get the GUIDs of the groups the user can join
	 *
	 * @return int[]
	 */
	public function getGroupGUIDs(): array {
		// check exclusives
		$exclusives = $this->checkExclusives();
		if (!empty($exclusives)) {
			return $this->sanitiseGUIDS($exclusives);
		}
		
		// get defaults
		$defaults = $this->getDefaults();
		
		// check for additionals
		$additionals = $this->checkAdditionals();
		
		$guids = array_merge($defaults, $additionals);
		
		return $this->sanitiseGUIDS($guids);
	}
	
	/**
	 * Set the user to check for
	 *
	 * @param \ElggUser $user new user to check
	 *
	 * @return void
	 */
	public function setUser(\ElggUser $user): void {
		$this->user = $user;
	}
	
	/**
	 * Get the exclusive groups to join
	 *
	 * @return int[]
	 */
	protected function checkExclusives(): array {
		foreach ($this->configs as $config) {
			if (elgg_extract('type', $config) !== 'exclusive') {
				continue;
			}
			
			$patterns = elgg_extract('patterns', $config);
			if (empty($patterns)) {
				continue;
			}
			
			if (!$this->checkConfigPatterns($patterns)) {
				continue;
			}
			
			return (array) elgg_extract('group_guids', $config, []);
		}
		
		return [];
	}
	
	/**
	 * Get the additional groups to join
	 *
	 * @return int[]
	 */
	protected function checkAdditionals(): array {
		$group_guids = [];
		
		foreach ($this->configs as $config) {
			if (elgg_extract('type', $config) !== 'additional') {
				continue;
			}
			
			$patterns = elgg_extract('patterns', $config);
			if (empty($patterns)) {
				continue;
			}
			
			if (!$this->checkConfigPatterns($patterns)) {
				continue;
			}
			
			$config_groups = (array) elgg_extract('group_guids', $config, []);
			$group_guids = array_merge($group_guids, $config_groups);
		}
		
		return $group_guids;
	}
	
	/**
	 * Validate the config patterns with the user
	 *
	 * @param array $patterns the patterns to match
	 *
	 * @return bool
	 */
	protected function checkConfigPatterns(array $patterns): bool {
		if (empty($patterns)) {
			return false;
		}
		
		$result = true;
		
		foreach ($patterns as $pattern) {
			$field = elgg_extract('field', $pattern);
			$value = elgg_extract('value', $pattern);
			$operand = elgg_extract('operand', $pattern);
			
			$user_value = $this->user->$field;
			if (!isset($user_value) || $user_value === '') {
				$result &= false;
			}
			
			if (is_array($user_value)) {
				foreach ($user_value as $v) {
					if ($this->checkUserValue($value, $operand, (string) $v)) {
						// go to next pattern
						continue(2);
					}
				}
				
				$result &= false;
			} else {
				$result &= $this->checkUserValue($value, $operand, (string) $user_value);
			}
		}
		
		return $result;
	}
	
	/**
	 * Check if a user value matches the expected value
	 *
	 * @param string $expected_value the configured matching value
	 * @param string $operand        the operand to use in the matching
	 * @param string $user_value     the user value
	 *
	 * @return bool
	 */
	protected function checkUserValue(string $expected_value, string $operand, string $user_value): bool {
		switch ($operand) {
			case 'equals':
				return strtolower($user_value) == strtolower($expected_value);
			
			case 'not_equals':
				return strtolower($user_value) != strtolower($expected_value);
				
			case 'contains':
				return (bool) stristr($user_value, $expected_value);
				
			case 'not_contains':
				return !(bool) stristr($user_value, $expected_value);
				
			case 'pregmatch':
				$valid = @preg_match('/' . $expected_value . '/', '');
				if ($valid === false) {
					// preg match pattern is invalid
					// @note this shouldn't happen
					return false;
				}
				return (bool) preg_match('/' . $expected_value . '/', $user_value);
		}
		
		return false;
	}
	
	/**
	 * Make sure we only have guids
	 *
	 * @param array $guids the array to sanitise
	 *
	 * @return int[]
	 */
	protected function sanitiseGUIDS($guids): array {
		if (empty($guids)) {
			return [];
		}
		
		if (!is_array($guids)) {
			$guids = [$guids];
		}
		
		$to_int = function ($value) {
			return (int) $value;
		};
		$positive = function ($value) {
			return $value > 0;
		};
		
		$guids = array_map($to_int, $guids);
		$guids = array_filter($guids, $positive);
		$guids = array_unique($guids);
		
		return array_values($guids);
	}
	
	/**
	 * Only load the default groups when needed
	 *
	 * @return int[]
	 */
	protected function getDefaults(): array {
		if (isset($this->defaults)) {
			return $this->defaults;
		}
		
		// load default groups
		$this->defaults = [];
		
		$auto_joins = elgg_get_plugin_setting('auto_join', 'group_tools');
		if (!empty($auto_joins)) {
			$this->defaults = $this->sanitiseGUIDS(elgg_string_to_array($auto_joins));
		}
		
		return $this->defaults;
	}
}
