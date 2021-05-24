<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    datafield
 * @subpackage longtext
 * @copyright  2021 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class data_field_longtext extends data_field_base {

    var $type = 'longtext';
    /**
     * priority for globalsearch indexing
     *
     * @var int
     */
    protected static $priority = self::MAX_PRIORITY;

    /**
     * Print the relevant form element in the ADD template for this field
     *
     * @global object
     * @param int $recordid
     * @return string
     */
    function display_add_field($recordid=0, $formdata=null) {
        global $DB, $OUTPUT, $PAGE;

        if ($formdata) {
            $fieldname = 'field_' . $this->field->id;
            $content = $formdata->$fieldname;
        } else if ($recordid) {
            $content = $DB->get_field('data_content', 'content', array('fieldid' => $this->field->id, 'recordid' => $recordid));
        } else {
            $content = '';
        }

        if ($content === false) {
            $content = '';
        }

        $max = (int)trim($this->field->param2);
        $maxproperty = $max && is_int($max) ? ' maxlength="' . $max . '" ' : '';

        $str = '<div title="' . s($this->field->description) . '">';
        $str .= '<label for="field_' . $this->field->id . '"><span class="accesshide">' . $this->field->name . '</span>';
        if ($this->field->required) {
            $image = $OUTPUT->pix_icon('req', get_string('requiredelement', 'form'));
            $str .= html_writer::div($image, 'inline-req');
        }
        $str .= '</label><textarea class="basefieldinput form-control d-inline mod-data-input" ' .
                'type="text" name="field_' . $this->field->id . '" ' .
                'id="field_' . $this->field->id . '"' . $maxproperty . '>' . s($content) . '</textarea>';

        if ($max && is_int($max)) {
            $data = new stdClass();
            $data->current = strlen(s($content));
            $data->max = $max;
            $str .= '<br /><span class="maxchars" data-control="field_' . $this->field->id . '">' .
                                get_string('maxcharsrequired', 'datafield_longtext', $data) .
                            '</span>';

            $PAGE->requires->js_call_amd('datafield_longtext/main', 'init', array('field_' . $this->field->id));
        }


        $str .= '</div>';

        return $str;
    }

    /**
     * Prints the respective type icon
     *
     * @global object
     * @return string
     */
    function image() {
        global $OUTPUT;

        $params = array('d' => $this->data->id, 'fid' => $this->field->id, 'mode' => 'display', 'sesskey' => sesskey());
        $link = new moodle_url('/mod/data/field.php', $params);
        $str = '<a href="' . $link->out() . '">';
        $str .= $OUTPUT->pix_icon('field/' . $this->type, $this->type, 'datafield_' . $this->type);
        $str .= '</a>';
        return $str;
    }

    function display_search_field($value = '') {
        return '<label class="accesshide" for="f_' . $this->field->id . '">' . $this->field->name.'</label>' .
               '<input type="text" class="form-control" size="16" id="f_' . $this->field->id . '" ' .
               'name="f_' . $this->field->id . '" value="' . s($value) . '" />';
    }

    public function parse_search_field($defaults = null) {
        $param = 'f_'.$this->field->id;
        if (empty($defaults[$param])) {
            $defaults = array($param => '');
        }
        return optional_param($param, $defaults[$param], PARAM_NOTAGS);
    }

    function generate_sql($tablealias, $value) {
        global $DB;

        static $i=0;
        $i++;
        $name = "df_text_$i";
        return array(" ({$tablealias}.fieldid = {$this->field->id} AND " .
                        $DB->sql_like("{$tablealias}.content", ":$name", false) . ") ",
                        array($name => "%$value%"));
    }

    /**
     * Check if a field from an add form is empty
     *
     * @param mixed $value
     * @param mixed $name
     * @return bool
     */
    function notemptyfield($value, $name) {
        return strval($value) !== '';
    }

    /**
     * Return the plugin configs for external functions.
     *
     * @return array the list of config parameters
     * @since Moodle 3.3
     */
    public function get_config_for_external() {
        // Return all the config parameters.
        $configs = [];
        for ($i = 1; $i <= 10; $i++) {
            $configs["param$i"] = $this->field->{"param$i"};
        }
        return $configs;
    }
}


