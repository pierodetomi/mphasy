<?php
	class HomeViewModel
	{
		public $Person = null;
		
		public function Index()
		{
			$this->Person = new Person();
			$this->Person->Name = "Jack";
			$this->Person->Age = 26;
		}
	}
	
// viewModels/HomeViewModel.php