<?
	class VariableAction extends IPSModule
	{
		
		public function ApplyChanges()
		{
			//Never delete this line!
			parent::ApplyChanges();
			
			//Lets register a variable with action
			$this->RegisterVariableInteger("Withings", "Test", "~Intensity.100");
			$this->EnableAction("Withings");
			
		}
		
		public function RequestAction($Ident, $Value)
		{
			
			switch($Ident) {
				case "Withings":
					SetValue($this->GetIDForIdent($Ident), $Value);
					break;
				default:
					throw new Exception("Invalid ident");
			}
		
		}
		
	
	}
?>
