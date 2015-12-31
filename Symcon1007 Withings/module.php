<?
	class Withings extends IPSModule
	{
		


    //**************************************************************************
    //
    //**************************************************************************    
    public function Create()
      {
      //Never delete this line!
      parent::Create();
    
      $this->RegisterPropertyInteger("Intervall", 21600);  
        
      }
    

    //**************************************************************************
    //
    //**************************************************************************    
		public function ApplyChanges()
		  {
			//Never delete this line!
			parent::ApplyChanges();
			
			//Lets register a variable with action
			$this->RegisterVariableInteger("Withings", "Test", "~Intensity.100");
			$this->EnableAction("Withings");
      $this->RegisterPropertyInteger("Intervall", 21600);
      //$this->RegisterTimer("WIT_UpdateTimer", 0, 'WIT_Update($_IPS[\'TARGET\']);');

	    //Timer erstellen
      $this->SetTimerInterval("WIT_UpdateTimer", $this->ReadPropertyInteger("Intervall"));
 

      //Update
     	$this->Update();

		  }

    //**************************************************************************
    //
    //**************************************************************************    
    public function Destroy()
      {
      $this->UnregisterTimer("WIT_UpdateTimer");
        
      //Never delete this line!
      parent::Destroy();
      }




    //**************************************************************************
    //
    //**************************************************************************    
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
