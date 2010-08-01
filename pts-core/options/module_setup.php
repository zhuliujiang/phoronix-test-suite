<?php

/*
	Phoronix Test Suite
	URLs: http://www.phoronix.com, http://www.phoronix-test-suite.com/
	Copyright (C) 2008 - 2010, Phoronix Media
	Copyright (C) 2008 - 2010, Michael Larabel

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

class module_setup implements pts_option_interface
{
	public static function argument_checks()
	{
		return array(
		new pts_argument_check(0, array("pts_module", "is_module"), null, "No module found.")
		);
	}
	public static function run($r)
	{
		$module = strtolower($r[0]);

		$pre_message = "";

		if(!class_exists($module))
		{
			pts_module_manager::load_module($module);
		}

		$module_name = pts_module_manager::module_call($module, "module_name");
		$module_description = pts_module_manager::module_call($module, "module_description");
		$module_setup = pts_module_manager::module_call($module, "module_setup");

		pts_client::$display->generic_heading($module_name . " Module Configuration");
		echo $module_description . "\n";

		if(count($module_setup) == 0)
		{
			echo "\nThere are no options available for configuring with the " . $module . " module.\n";
		}
		else
		{
			if(($module_presets = pts_client::read_env("PTS_MODULE_SETUP")) != false)
			{
				$module_presets = pts_client::parse_value_string_double_identifier($module_presets);
			}

			$set_options = array();
			foreach($module_setup as $module_option)
			{
				if($module_option instanceOf pts_module_option)
				{
					$option_identifier = $module_option->get_identifier();

					if(isset($module_presets[$module][$option_identifier]) && $module_option->is_supported_value($module_presets[$module][$option_identifier]))
					{
						echo "\n" . $module_option->get_formatted_question();
						echo $module_presets[$module][$option_identifier] . "\n";
						$input = $module_presets[$module][$option_identifier];
					}
					else
					{
						do
						{
							echo "\n" . $module_option->get_formatted_question();
							$input = pts_user_io::read_user_input();
						}
						while(!$module_option->is_supported_value($input));
					}

					if(empty($input))
					{
						$input = $module_option->get_default_value();
					}

					$set_options[$option_identifier] = $input;
				}
			}

			$set_options = pts_module_manager::module_call($module, "module_setup_validate", $set_options);
			pts_module::module_config_save($module, $set_options);
		}
		echo "\n";
	}
}

?>
