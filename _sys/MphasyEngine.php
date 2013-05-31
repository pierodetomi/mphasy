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
	
	class MphasyEngine
	{
		/*
         * Fields
         *
         */
		private $view = null;
        
        private $prms = null;
		
        /*
         * Properties
         *
         */
		public $TemplateName = null;
		
        public $ViewName = null;
        
		public $ViewModel = null;
        
		public static function Run()
		{
			$engine = new MphasyEngine();
			$engine->ProcessRequest();
		}

		/*
         * Main entry point for all requests.
         *
         */
		public function ProcessRequest()
		{
			$requestType = $_SERVER['REQUEST_METHOD'];

			if($requestType != 'GET' && $requestType != 'POST')
			{
				$this->Show404View();
				return;
			}
			
			if( !isset($_SERVER['QUERY_STRING']) || $_SERVER['QUERY_STRING'] == null )
			{
                $viewModelName = ROOT_VIEW_MODEL;
                $viewModelNameFull = $viewModelName.'ViewModel';
                $this->ViewName = ROOT_VIEW;
			}
			else
			{
				$query = $_SERVER['QUERY_STRING'];
				$args = explode('&', $query);
				$argsCount = count($args);
				
				for($i = 0; $i < $argsCount; $i++)
				{
					$parts = explode('=', $args[$i]);
					
					switch($parts[0])
					{
						case 'view':
							$this->ViewName = $parts[1];
							break;
						case 'viewModel':
							$viewModelName = $parts[1];
							$viewModelNameFull = $viewModelName.'ViewModel';
                            break;
						case 'prms':
							$prms = explode('/', $parts[1]);
							break;
					}
				}
			}
			
            if( !class_exists($viewModelNameFull) )
            {
				// Error - The specified view or view-model does not exist!
                $this->Show404View();
                return;
            }
            
            $this->ViewModel = new $viewModelNameFull();
            
			if( !method_exists($this->ViewModel, $this->ViewName) )
			{
				$this->Show404View();
				return;
			}
			
			$this->view = $this->ReadViewFile($this->ViewName, $viewModelName);
            
			if($this->view === FALSE || $this->view == null)
			{
				// By convention: in absence of a view, output the result of view-model's function call
				if( !isset($prms) || $prms == null || count($prms) == 0 )
				{
					// Call view-model's request method without parameters
					$methodName = $this->ViewName;
					echo $this->ViewModel->$methodName();
				}
				else
				{
					// Call view-model's request method with specific parameters
					echo call_user_func_array(array($this->ViewModel, $this->ViewName), $prms);
				}
				
				return;
			}
			
			// Process view-model request
			if( !isset($prms) || $prms == null || count($prms) == 0 )
			{
                // Call view-model's request method without parameters
                $methodName = $this->ViewName;
                $this->ViewModel->$methodName();
			}
            else
            {
                // Call view-model's request method with specific parameters
                call_user_func_array(array($this->ViewModel, $this->ViewName), $prms);
            }
			
			$this->ProcessViewDirectives($this->view);
			
			if( !isset($this->TemplateName) )
			{
				$this->ShowView($this->view);
				return;
			}
			
			$template = $this->ReadTemplateFile($this->TemplateName);
			
			if($template === FALSE || $template == null)
			{
				// This is an error - View has been found, but it specifies a template that
				// does not exist!
				$this->Show404View();
				return;
			}
			
			$template = $this->ProcessTemplate($template);
			
			if($template == null)
				$this->Show404View();
			
            $this->view = str_replace('@MainView()', $this->view, $template);
			$this->ShowView($this->view);
		}
        
		/*
         * Outputs the specified view, using an eval() statement
         * and a "200" response header.
         *
         */
		private function ShowView($view)
        {
            HttpHelper::PutHeader('200');
            eval('?>'.$view);
        }
		
        /*
         * Outputs the 404 view (uses the default 404 view, in case
         * that a user-defined-404 view has not been defined).
         * It also sends a "404" response header.
         *
         */
		private function Show404View()
        {
            HttpHelper::PutHeader('404');
            $view = $this->Read404ViewFile();
			eval('?>'.$view);
        }
		
        private function ProcessViewDirectives(&$view)
		{
			// Find @{ ... } tag, if present
			preg_match('/@{[^}]+}/', $view, $matches);
			
			if($matches != null && count($matches) > 0)
			{
				$match = $matches[0];
				
				// Get all directives inside @{ ... } tag
				preg_match_all('/\$[^;]+;/i', $match, $directives);
				
				if($directives != null && count($directives) > 0 && $directives[0] != null && count($directives[0] > 0))
				{
					$directivesCount = count($directives[0]);
					
					for($i = 0; $i < $directivesCount; $i++)
					{
						$directive = $directives[0][$i];
						$parts = explode('=', $directive);
						
						if($parts != null && count($parts) > 0)
						{
                            $name = $parts[0];
                            $value = $parts[1];
                            
                            // "Normalize" name
							$name = str_replace(' ', '', $name);
							$name = str_replace('$', '', $name);
							
							// "Normalize" value
							$value = str_replace('"', '', $value);
							$value = str_replace("'", '', $value);
							$value = str_replace(';', '', $value);
							
                            while(substr($value, 0, 1) == ' ')
                                $value = substr($value, 1);
                            
                            while(substr($value, strlen($value) - 1) == ' ')
                                $value = substr($value, 0, strlen($value) - 1);
                            
							if( ViewDirectivesHelper::IsViewModelProperty($name) )
							{
								$propertyName = ViewDirectivesHelper::GetViewModelProperty($name);
								$this->ViewModel->{$propertyName} = $value;
							}
							else
							{
								// Dynamically create a property in the engine with the specified
								// (name, value) couple.
								$this->{$name} = $value;
							}
						}
					}
				}
				
                // "Clean" view, removing the directive (avoiding it from showing up to the user)
				$view = str_replace($match, '', $view);
			}
		}
		
		private function ProcessTemplate($template)
		{
			while( TRUE )
			{
				$index = strpos($template, '@PartialView(');
				
				if($index === FALSE)
					break;
				
				$end_index = strpos($template, ')', $index);
				
				if($end_index === FALSE)
					break;
					
				$argsString = substr($template, $index + strlen('@PartialView('), $end_index - 1 - ($index + strlen('@PartialView(')));
				$args = explode(',', $argsString);
				$argsCount = count($args);

				if($argsCount != 3 && $argsCount != 1)
				 // Error: wrong arguments count
				 return null;
				
				if($argsCount == 3)
				{
					$viewName = $this->NormalizeTemplateFunctionParameter($args[0]);
					$viewModelName = $this->NormalizeTemplateFunctionParameter($args[1]);
					$viewModelMethod = $this->NormalizeTemplateFunctionParameter($args[2]);

					$viewModelFullName = $viewModelName.'ViewModel';
					
					if( !class_exists($viewModelFullName) )
						// Error: specified view model does not exist
						return null;

					// Store view-model for partial view (if it doesn't exist already)
					if( !isset($this->{$viewModelFullName}) )
						$this->{$viewModelFullName} = new $viewModelFullName();

					if( !method_exists($this->{$viewModelFullName}, $viewModelMethod) )
						// Error: specified method does not exist in specified view model
						return null;
				
					// Execute view model method, in order to bring the view model to the desired status
					$this->{$viewModelFullName}->$viewModelMethod();
				
					$view = $this->ReadViewFile($viewName, null);

					// Update php code in partial view, to refer to the specific view-model name
					preg_match_all('/<\?php [^-]*->ViewModel->/i', $view, $matches);
					$updatedCode = str_replace('ViewModel', $viewModelFullName, $matches[0][0]);
					$view = str_replace($matches[0][0], $updatedCode, $view);
				}
				else
				{
					$viewName = $this->NormalizeTemplateFunctionParameter($args[0]);
					$view = $this->ReadViewFile($viewName, null);
				}

				if( $view === FALSE || $view == null )
					// Error: unable to read the specified view
					return null;
				
				// Assemble template code
				$template = substr($template, 0, $index).$view.substr($template, $end_index + 1);
			}
			
			return $template;
		}
		
		private function NormalizeTemplateFunctionParameter($param)
		{
			$param = str_replace('"', '', $param);
			$param = str_replace("'", '', $param);
			$param = str_replace(' ', '', $param);

			return $param;
		}

		private function ReadTemplateFile($templateName)
		{
			$templatePath = TEMPLATES_PATH.$templateName.'.html';
			
			if( !file_exists($templatePath) )
				return null;
			
			$fh = fopen($templatePath, 'r');
			$template = fread($fh, filesize($templatePath));
			fclose($fh);
			
			return $template;
		}
		
		private function ReadViewFile($viewName, $viewModelName)
        {
            if($viewModelName != null)
			    $viewPath = VIEWS_PATH.$viewModelName.'/'.$viewName.'.html';
            else
                $viewPath = VIEWS_PATH.$viewName.'.html';
			
			if( !file_exists($viewPath) )
				return null;
			
			$fh = fopen($viewPath, 'r');
			$view = fread($fh, filesize($viewPath));
			fclose($fh);
			
			return $view;
		}
		
		private function Read404ViewFile()
		{
			$viewPath = VIEWS_PATH.'404.html';
			
			if( !file_exists($viewPath) )
				$viewPath = './_sys/views/404.html';
			
			$fh = fopen($viewPath, 'r');
			$view = fread($fh, filesize($viewPath));
			fclose($fh);
			
			return $view;
		}
	}
	
// _sys/PhMvvmEngine.php