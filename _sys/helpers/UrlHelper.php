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

	class UrlHelper
	{
		public static function GetResourceUrl($relativePath)
		{
			$host = HOST_NAME;
			$has_slash = strpos($host, '/', strlen($host) - 1);
			
			if($has_slash === FALSE)
				$url = $host.'/'.$relativePath;
			else
				$url = $host.$relativePath;
			
			return $url;
		}
		
		public static function GetActionUrl($action, $viewModel, $args)
		{
			$host = HOST_NAME;
			$has_slash = strpos($host, '/', strlen($host) - 1);
			
			if($has_slash === FALSE)
				$url = $host.'/'.$viewModel.'/'.$action.'/';
			else
				$url = $host.$viewModel.'/'.$action.'/';
			
			if( $args != null && count($args) > 0 )
			{
				$args_count = count($args);
				
				for($i = 0; $i < $args_count; $i++)
					$url .= '/'.$args[$i];
			}
			
			return $url;
		}
        
        public static function RedirectToAction($action, $viewModel, $args)
		{
            $url = self::GetActionUrl($action, $viewModel, $args);
			header('location: '.$url);
			exit();
		}
	}

// _sys/HtmlHelper.php