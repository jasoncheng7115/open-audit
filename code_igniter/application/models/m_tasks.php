<?php
#  Copyright 2003-2015 Opmantek Limited (www.opmantek.com)
#
#  ALL CODE MODIFICATIONS MUST BE SENT TO CODE@OPMANTEK.COM
#
#  This file is part of Open-AudIT.
#
#  Open-AudIT is free software: you can redistribute it and/or modify
#  it under the terms of the GNU Affero General Public License as published
#  by the Free Software Foundation, either version 3 of the License, or
#  (at your option) any later version.
#
#  Open-AudIT is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  GNU Affero General Public License for more details.
#
#  You should have received a copy of the GNU Affero General Public License
#  along with Open-AudIT (most likely in a file named LICENSE).
#  If not, see <http://www.gnu.org/licenses/>
#
#  For further information on Open-AudIT or for a license other than AGPL please see
#  www.opmantek.com or email contact@opmantek.com
#
# *****************************************************************************

/**
* @category  Model
* @package   Open-AudIT
* @author    Mark Unwin <marku@opmantek.com>
* @copyright 2014 Opmantek
* @license   http://www.gnu.org/licenses/agpl-3.0.html aGPL v3
* @version   3.1.1
* @link      http://www.open-audit.org
 */
class M_tasks extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->log = new stdClass();
        $this->log->status = 'reading data';
        $this->log->type = 'system';
    }

    public function read($id = '')
    {
        $this->log->function = strtolower(__METHOD__);
        stdlog($this->log);
        if ($id == '') {
            $CI = & get_instance();
            $id = intval($CI->response->meta->id);
        } else {
            $id = intval($id);
        }
        $sql = "SELECT * FROM `tasks` WHERE `id` = ?";
        $data = array($id);
        $result = $this->run_sql($sql, $data);

        if ($result !== false) {
            for ($i=0; $i < count($result); $i++) {
                if ($result[$i]->type == 'discoveries' or $result[$i]->type == 'queries' or $result[$i]->type == 'summaries') {
                    $sql = "SELECT name AS `name` FROM `" . $result[$i]->type . "` WHERE id = ?";
                    $data = array($result[$i]->sub_resource_id);
                    $data_result = $this->run_sql($sql, $data);
                    if (!empty($data_result[0]->name)) {
                        $result[$i]->sub_resource_name = $data_result[0]->name;
                    } else {
                        $result[$i]->sub_resource_name = '';
                    }
                } else if ($result[$i]->type == 'reports') {
                    $result[$i]->sub_resource_name = "";
                }
            }
        }
        $result = $this->format_data($result, 'tasks');
        if (!empty($result[0]->attributes->options)) {
            $result[0]->attributes->options = my_json_decode($result[0]->attributes->options);
        }
        return ($result);
    }

    public function delete($id = '')
    {
        $this->log->function = strtolower(__METHOD__);
        $this->log->status = 'deleting data';
        stdlog($this->log);
        if ($id == '') {
            $CI = & get_instance();
            $id = intval($CI->response->meta->id);
        } else {
            $id = intval($id);
        }
        if ($id != 0) {
            $CI = & get_instance();
            $sql = "DELETE FROM `tasks` WHERE `id` = ?";
            $data = array(intval($id));
            $this->run_sql($sql, $data);
            return true;
        } else {
            return false;
        }
    }

}
