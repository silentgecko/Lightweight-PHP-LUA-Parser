<?php
/**
 * A class to parse LUA files to an PHP array.
 */
class LUAParser {

	/**
	 * Contains the lines of the LUA file.
	 */
	private $_lua = array();

	/**
	 * Contains the current position of the LUA array.
	 */
	private $_pos = 0;

	/**
	 * Contains the nuber of array elements.
	 */
	private $lines = 0;

	/**
	 * Contains the parsed LUA data from file.
	 */
	public $data = array();

	/**
	 * parseFile - Read and parse the given file.
	 *
	 * @param	string	$path	A valid LUA file path.
	 */
	public function parseFile($path) {

		// Check for file
		if(is_file($path) === false) {
			throw new Exception('Invalid input file (' . $path . ')');
		}

		// Initialise vars
		$this->_lua = array();
		$this->_post = 0;
		$this->_lines = 0;
		$this->data = array();

		// Read the file
		if(($lua = file_get_contents($path)) !== false) {

			// Split by new line and count lines
			$this->_lua = explode("\n", $lua);
			$this->_lines = count($this->_lua);

			// Free resources
			unset($lua);

			// Very small array, something is wrong
			if($this->_lines < 2) {
				throw new Exception('Input did not validate as array');
			}

			// Parse the LUA data
			$this->data = $this->parseLUA($this->_pos);

			// Free resources
			unset($this->_lua);
		}

		// Could not read file
		else {
			throw new Exception('Could not read input file (' . $path . ')');
		}
	}

	/**
	 * parseLUA - Parses the contents of the LUA file.
	 *
	 * @param	mixed	$position	The Position inside the LUA array.
	 */
	private function &parseLUA(&$position = false) {

		// Set initial position
		if($position === false) {
			$position = &$this->_pos;
		}

		// Initialise vars
		$data = array();
		$end = false;
		$i = $position;

		// The end of array has not been reached
		if($position < $this->_lines) {

			// Loop through LUA array
			while($end === false) {

				// End reached
				if($i >= $this->_lines) {
					break;
				}

				// Explode by assignment
				$parts = explode('=', $this->_lua[$i]);

				// Trim values
				$parts[0] = trim($parts[0]);

				// Trim if exists
				if(isset($parts[1]) === true) {
					$parts[1] = trim($parts[1]);
				}

				// Start of table
				if(isset($parts[1]) === true && ($parts[1] === '{' || empty($parts[1]) === true)) {

					// When Bracket in next line, jump the next line
					$i += (empty($parts[1]) === true) ? 2 : 1;

					// Parse content
					$data[$this->getValue($parts[0], true)] = $this->parseLUA($i);
				}

				// End of table
				else if($parts[0] === '}' || $parts[0] === '},') {
					$end = true;
					$i++;
				}

				// Get value
				else {

					// Save key to avoid multiply function execution
					$key = $this->getValue($parts[0], true);

					// Data has been found
					if(mb_strlen($key) > 0 && mb_strlen($parts[1]) > 0) {
						$data[$key] = $this->getValue($parts[1], false);
					}

					// Increase position
					$i++;
				}
			}
		}

		// Set temp position to position pointer
		$position = $i;

		// Return fetched data by reference
		return $data;
	}

	/**
	 * getValue - Removes control characters from the given string.
	 *
	 * @param	string	$str	A string.
	 * @return	mixed	Return the string without control characters.
	 */
	private function &getValue(&$str, $is_id) {

		// Remove spaces at start and end
		$str = trim($str);

		// Remove controls characters from ID
		if($is_id === true) {
			$str = str_replace(array('"', '[', ']'), '', $str);
		}

		// Remove controls characters from value
		else {

			// Remove ending control characters
			if(mb_substr($str, -2) === '",') {
				$str = mb_substr($str, 0, -2);
			}
			else if(mb_substr($str, -1) === '"') {
				$str = mb_substr($str, 0, -1);
			}

			// Remove starting control character
			if(mb_substr($str, 0, 1) === '"') {
				$str = mb_substr($str, 1);
			}
		}

		// Remove spaces at start and end
		$str = trim($str);

		// Return fetched data by reference
		return $str;
	}
}
?>