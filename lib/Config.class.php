<?php
/**
 * @author    Patrick Kleinschmidt (NoxNebula) <noxifoxi@gmail.com>
 * @copyright Copyright (c) 2013 SilexLab
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 */

/**
 * Class for configuration stuff
 */
class Config {
	/**
	 * @var array $config
	 */
	private $config = [];

	/**
	 * Get the config
	 * @param array $config optional
	 */
	public function __construct(array $config = []) {
		// Fetch config
		if($config)
			$this->config = $config;
		$config = Silex::getDB()->query('SELECT * FROM `config`')->fetchAllObject();

		// Now work with that stuff you got
		if($config) {
			foreach ($config as $c) {
				$value = $c->value;
				$type = $c->type;
				$this->formatValue($c->option, $value, $type);

				// TODO: for ACP, include type
				$this->config[$c->option] = $value;
			}
		}
	}

	private function __clone() {}

	/**
	 * Get the value of a config option
	 * @param  string $node
	 * @return mixed
	 */
	public function get($node) {
		return array_key_exists($node, $this->config) ? $this->config[$node] : null;
	}

	/**
	 * Set the value of a config option
	 * @param  string $node
	 * @param  mixed  $value
	 * @return null
	 */
	public function set($node, $value) {
		if(defined('ACP')) {
			// TODO: Set the value
		}
		return null;
	}

	/**
	 * Formates the value and returns the right type
	 * @param string    $node
	 * @param mixed     $value
	 * @param           $type
	 * @throws CoreException
	 */
	private function formatValue($node, &$value, &$type) {
		preg_match('/^[a-zA-Z]+/', $type, $match);
		switch ($match[0]) {
			case 'string':
				$value = (string)$value;
				if(defined('ACP'))
					$type = $this->parseType($type, true);
				break;
			case 'int':
				$value = (int)$value;
				if(defined('ACP'))
					$type = $this->parseType($type);
				break;
			case 'float':
				$value = (float)$value;
				if(defined('ACP'))
					$type = $this->parseType($type);
				break;
			case 'bool':
				if(in_array($value, ['true', '1']))
					$value = true;
				else if(in_array($value, ['false', '0']))
					$value = false;
				else
					throw new CoreException('The config node "'.$node.'" is in the wrong format. Bool expected, the value is: "'.$value.'"');
				break;
			default:
				throw new CoreException('The type ('.$this->config[$node]['Type'].') of the node "'.$node.'" is not a valid type.');
		}
	}

	/**
	 * Parse length or range
	 * @param           $type
	 * @param bool      $onlyLength optional
	 */
	private function parseType(&$type, $onlyLength = false) {
		if(!$onlyLength && preg_match('/(\-{0,1}[0-9]+(\.[0-9]+){0,1}){0,1}\|(\-{0,1}[0-9]+(\.[0-9]+){0,1}){0,1}/', $type, $range)) {
			// Range
			$range = $range[0];
			$aR = explode('|', $range);
			if(isset($aR[0]) && !empty($aR[0]) && isset($aR[1]) && !empty($aR[1])) {
				// Range
				if($aR[0] < $aR[1]) {
					$type = ['range' => ['min' => (int)$aR[0], 'max' => (int)$aR[1]]];
				}
			} else if(isset($aR[0]) && !empty($aR[0])){ // Min
				$type = ['range' => ['min' => (int)$aR[0]]];
			} else { // Max
				$type = ['range' => ['max' => (int)$aR[1]]];
			}
		} else if(preg_match('/[0-9]+/', $type, $length)) {
			// Length
			$type = ['length' => $length];
		}
	}
}
