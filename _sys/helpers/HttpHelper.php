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

	class HttpHelper
	{
		/*
         * Sends a response header with the specified code.
         * Supported code (for the moment) are 404 and 200.
         *
         */
		public static function PutHeader($code)
        {
            switch($code)
            {
                case '200':
                    header('HTTP/1.0 200 OK');
                    break;
                case '404':
                    header('HTTP/1.0 404 Not Found');
                    break;
            }
        }
		
		public static function IsGetRequest()
		{
			return ( isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET' );
		}
		
		public static function IsPostRequest()
		{
			return ( isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST' );
		}
	}

// _sys/helpers/HttpHelper.php