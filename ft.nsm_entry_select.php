<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * NSM Example Addon Fieldtype
 *
 * @package NSMEntrySelect
 * @version 0.0.1
 * @author Leevi Graham <http://newism.com.au>
 * @copyright Copyright (c) 2007-2010 Newism
 * @license Commercial - please see LICENSE file included with this distribution
 * @see http://expressionengine.com/public_beta/docs/development/extensions.html
 *
 **/
class Nsm_entry_select_ft extends EE_Fieldtype
{
	/**
	 * Field info - Required
	 * 
	 * @access public
	 * @var array
	 */
	public $info = array(
		'name'		=> 'NSM Entry Select',
		'version'	=> '0.0.1'
	);

	/**
	 * UI Modes
	 *
	 * @access private
	 * @var array
	 */
	private static $ui_modes = array(
		'select',
		'multi_select',
		'autocomplete'
	);

	/**
	 * Constructor
	 * 
	 * @access public
	 * 
	 * Calls the parent constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->EE->load->model('channel_model');

		// create a cache 
		if(!isset($this->EE->session->cache[__CLASS__]))
		{
			$this->EE->session->cache[__CLASS__] = array();
			$this->EE->session->cache[__CLASS__]["entry_data"] = array();
			$this->EE->session->cache[__CLASS__]["channel_custom_fields"] = array();
		}
	}

	/**
	 * Display the settings form for each custom field
	 * 
	 * @access public
	 * @param $data mixed The field settings
	 * @return string Override the field custom settings with custom html
	 * 
	 * In this case we add an extra row to the table. Not sure how the table is built
	 */
	public function display_settings($data)
	{
		$this->EE->lang->loadfile('nsm_entry_select');

		$data = array_merge(array(
			"field_channels" => array(),
			"field_ui_mode" => FALSE,
			"field_size" => 1
		), $data);

		$entry_channels_query = $this->EE->channel_model->get_channels()->result();
		$entry_channels = array();
		foreach ($entry_channels_query as $channel) {
			$entry_channels[$channel->channel_id] = $channel->channel_title;
		}

		$this->EE->table->add_row(
			form_hidden($this->field_id.'_field_fmt', 'none') .
			form_hidden('field_show_fmt', 'n') .
			lang("Restrict entry selection to:", 'field_channels'),
			form_multiselect($this->field_id.'_field_settings[field_channels][]', $entry_channels, $data["field_channels"], "id='field_channels'")
		);

		$select_opts = array();
		foreach (self::$ui_modes as $key)
			$select_opts[$key] = lang($key);

		$this->EE->table->add_row(
			lang("UI Mode:", 'field_ui_mode'),
			form_dropdown($this->field_id.'_field_settings[field_ui_mode]', $select_opts, $data["field_ui_mode"], "id='field_ui_mode'")
		);

		$this->EE->table->add_row(
			lang("Size", 'field_size')
			. "<br />Determines the multi-select height and number of results returned in the autocomplete",
			form_input($this->field_id.'_field_settings[field_size]', $data["field_size"], "id='field_size'")
		);
	}

	/**
	 * Save the custom field settings
	 * 
	 * @return boolean Valid or not
	 */
	public function save_settings()
	{
		$new_settings = $this->EE->input->post('nsm_entry_select_field_settings');
		return $new_settings;
	}

	/**
	 * Display the field in the publish form
	 * 
	 * @access public
	 * @param $data String Contains the current field data. Blank for new entries.
	 * @return String The custom field HTML
	 */
	public function display_field($data = FALSE)
	{
		if(!is_array($data))
			$data = explode("|", $data);

		switch ($this->settings['field_ui_mode']) {
			case "select":
				return $this->_uiSelect($this->field_id, $this->settings['field_channels'], $data);
				break;

			case "multi_select":
				return $this->_uiSelect($this->field_id, $this->settings['field_channels'], $data, TRUE, $this->settings['field_size']);
				break;
			
			case "auto_complete":
				return "Autocomplete coming soon!";
				break;
		}
	}

	/**
	 * Publish form validation
	 * 
	 * @param $data array Contains the submitted field data.
	 * @return mixed TRUE or an error message
	 */
	public function validate($data)
	{
		return TRUE;
	}

	/**
	 * Pre-process the data and return a string
	 *
	 * @access public
	 * @param array data The selected entry channels
	 * @return string Concatenated string f entry channels
	 */
	public function save($data){
		$this->EE->load->helper('custom_field');
		return encode_multi_field($data);
	}

	/**
	 * Replaces the custom field tag
	 * 
	 * @access public
	 * @param $data string Contains the field data (or prepped data, if using pre_process)
	 * @param $params array Contains field parameters (if any)
	 * @param $tagdata mixed Contains data between tag (for tag pairs) FALSE for single tags
	 * @return string The HTML replacing the tag
	 * 
	 */
	public function replace_tag($data, $params = FALSE, $tagdata = FALSE)
	{

		// print("<pre>" . print_r($params, TRUE) . "</pre>");

		$this->EE->load->helper('custom_field');
		$entries = decode_multi_field($data);

		$params = array_merge(array(
			"backspace" => FALSE,
			"glue" => ", ",
			"multi_field" => FALSE,
			"multi_field_glue" => ", ",
			"value" => "entry_id",
			"prefix" => "es:"
		), (array) $params);

		$entries = $this->_getEntryData($entries, $params);

		return ($tagdata) ? $this->_parseMulti($entries, $params, $tagdata) : $this->_parseSingle($entries, $params);
	}

	/**
	 * Parse a single tag
	 * 
	 * @access private
	 * @param $entries array The entry ids
	 * @param $params array the tag params
	 * @return string The entry ids concatenated with glue
	 */
	private function _parseSingle($entries, $params)
	{
		$ret = array();
		foreach ($entries as $entry_id => $entry)
		{
			if(self::_isTrue($params["multi_field"]))
			{
				$entry[$params["value"]] = implode($params["multi_field_glue"], decode_multi_field($entry[$params["value"]]));
			}
			$ret[] = $entry[$params["value"]];
		}
		return implode($params["glue"], $ret);
	}

	/**
	 * Parse a tag pair
	 * 
	 * @access private
	 * @param $entries array The entry ids
	 * @param $params array the tag params
	 * @param $tagdata string The data between the tag pair
	 * @return string The entry ids concatenated with glue
	 */
	private function _parseMulti($entries, $params, $tagdata)
	{
		$chunk = '';

		foreach($entries as $count => $entry)
		{
			$vars['count'] = $count + 1;
			foreach ($entry as $key => $value)
			{
				$vars[$params["prefix"] . $key] = $value;
			}
			$tmp = $this->EE->functions->prep_conditionals($tagdata, $vars);
			$chunk .= $this->EE->functions->var_swap($tmp, $vars);
		}

		if ($params['backspace'])
		{
			$chunk = substr($chunk, 0, - $params['backspace']);
		}
		return $chunk;
	}


	/**
	 * Get the entry data from the DB
	 * 
	 * @access private
	 * @param $entries array The entry ids
	 * @param $params array the tag params
	 * @return array Entries with DB data
	 */
	private function _getEntryData($entries, $params)
	{
		$required_entries = $entries; 
		foreach($required_entries as $k => $v)
		{
			if(array_key_exists($v, $this->EE->session->cache[__CLASS__]))
			{
				unset($required_entries[$k]);
			}
		}

		if(!empty($required_entries))
		{
			$this->EE->db->from("exp_channel_titles")
						->join("exp_channel_data", 'exp_channel_titles.entry_id = exp_channel_data.entry_id')
						->where_in("exp_channel_titles.entry_id", $required_entries);

			$query = $this->EE->db->get();
			foreach ($query->result_array() as $entry)
			{
				$this->EE->session->cache[__CLASS__]["entry_data"][$entry["entry_id"]] = $entry;
			}
		}

		$ret = array();
		foreach ($entries as $entry_id)
		{
			if(!isset($this->EE->session->cache[__CLASS__]["entry_data"][$entry_id]))
				continue;

			$ret[] = $this->EE->session->cache[__CLASS__]["entry_data"][$entry_id];
		}
		return $ret;
	}

	/**
	 * Create a select UI
	 * 
	 * @param $field_id string The field id
	 * @param $channels array Entry channels we are selecting entries from
	 * @param $selected_entries array Previous selected entries
	 * @param $multiple boolean Does this select accept multiple selections
	 * @param $size int Size of the select box
	 * @return str HTML for UI
	 */
	private function _uiSelect($field_id, array $channels, array $selected_entries = array(), $multiple = FALSE, $size = 1)
	{
		if(empty($channels))
			return lang("No channels have been selected in the field settings");

		$entry_query = $this->EE->db->query("SELECT
			exp_channel_titles.entry_id AS entry_id,
			exp_channel_titles.title AS entry_title,
			exp_channels.channel_title AS channel_title, 
			exp_channels.channel_id AS channel_id
		FROM
			exp_channel_titles
		INNER JOIN
			exp_channels
		ON
			exp_channel_titles.channel_id = exp_channels.channel_id
		WHERE 
			exp_channels.channel_id IN (" . implode(",",$channels) . ") 
		ORDER BY 
			exp_channels.channel_id ASC , exp_channel_titles.title ASC ");

		if($entry_query->num_rows == 0)
			return lang('No channel entries were found for this field');

		$attrs = ($multiple) ? 'multiple="multiple" size="'.$size.'"' : '';

		$r = "\n<select id='field_id_" . $this->field_id . "' name='field_id_" . $this->field_id . "[]'". $attrs ." >";

		if(!$multiple)
			$r .= "<option value=''>".lang('Do not associate an entry with this entry')."</option>";

		$channel = null;
		foreach ($entry_query->result_array() as $entry)
		{
			if($channel != $entry['channel_title'])
			{
				// if this is not the first channel
				if($channel != null)
				{
					// close the optgroup
					$r .= "\n\t</optgroup>";
				}
				// set the current channel
				$channel = $entry['channel_title'];
				// open another opt channel
				$r .= "\n\t<optgroup label='" . $entry['channel_title'] ."'>";
			}

			$selected = (in_array($entry['entry_id'], $selected_entries)) ? " selected='selected' " : "";
			$r .= "\n\t\t<option value='" . $entry['entry_id'] . "'" . $selected . " > " . $entry['entry_title'] . "</option>";
		}
		$r .= "</select>";
		return $r;
	}

	/**
	 * Checks if a param value is true
	 * 
	 * @access private
	 * @param $value mixed The param value
	 * @return boolean
	 */
	private static function _isTrue($value){
		return in_array($value, array("yes", "y", "1", TRUE, "true", "TRUE"));
	}

}
//END CLASS
