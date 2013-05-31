<?php

/* ===================================================
 * Copyright 2013 Piero De Tomi
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ========================================================== */

	class ViewDirectivesHelper
	{
		const PREFIX = 'ViewModel->';
		
		const PREFIX_LENGTH = 11;
		
		public static function IsViewModelProperty($directiveName)
		{
			if( strlen($directiveName) >= self::PREFIX_LENGTH )
			{
				$subString = substr($directiveName, 0, self::PREFIX_LENGTH);
				return ($subString == self::PREFIX);
			}
			
			return false;
		}
		
		public static function GetViewModelProperty($directiveName)
		{
			return substr($directiveName, self::PREFIX_LENGTH);
		}
	}
	
// _sys/helpers/ViewDirectivesHelper.php