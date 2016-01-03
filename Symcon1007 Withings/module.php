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
      $this->RegisterPropertyBoolean("BodyMeasures", true);  
      $this->RegisterPropertyBoolean("BloodMeasures", false);  
      $this->RegisterPropertyString("Username", "user@user.de");  
      $this->RegisterPropertyString("Userpassword", "123456");  
      $this->RegisterPropertyString("User", "XXX");  
      $this->RegisterPropertyBoolean("Logging", false);  
      $this->RegisterTimer("WIT_UpdateTimer", 0, 'WIT_Update($_IPS[\'TARGET\']);');
        
      }
    

    //**************************************************************************
    //
    //**************************************************************************    
		public function ApplyChanges()
		  {
			//Never delete this line!
			parent::ApplyChanges();

			$id = $this->RegisterVariableString("name"       , "Name"      ,"~String",0);
			$id = $this->RegisterVariableString("gender"     , "Geschlecht","~String",2);
			$id = $this->RegisterVariableInteger("birthdate" , "Geburtstag","~UnixTimestamp",1);
			$id = $this->RegisterVariableInteger("height"    , "Groesse"   ,"~Valve" ,3);


			//$id = $this->RegisterVariableInteger("Withings", "Test", "~Intensity.100");
      $parent = IPS_GetParent($id);
      
      $CatID = $this->CreateKategorie("Blutdruck",$parent);
      if ( $CatID === false )
        throw new Exception("Kategorie Blutdruck nicht definiert");
      
      $VariablenID = @IPS_GetVariableIDByName("Diastolic",$CatID);  
			if ($VariablenID === false)
        {
        $id = $this->RegisterVariableInteger("diastolicblood", "Diastolic","~Valve",1);
        IPS_SetParent($id,$CatID);
        }
      $VariablenID = @IPS_GetVariableIDByName("Systolic",$CatID);  
			if ($VariablenID === false)
        {
        $id = $this->RegisterVariableInteger("systolicblood", "Systolic","~Valve",2);
        IPS_SetParent($id,$CatID);
        }
      $VariablenID = @IPS_GetVariableIDByName("Puls",$CatID);  
			if ($VariablenID === false)
        {
        $id = $this->RegisterVariableInteger("heartpulse", "Puls","~Valve",3);
        IPS_SetParent($id,$CatID);
        }
      $VariablenID = @IPS_GetVariableIDByName("DatumUhrzeit",$CatID);  
			if ($VariablenID === false)
        {
        $id = $this->RegisterVariableInteger("timestamp", "DatumUhrzeit","~UnixTimestamp",0);
        IPS_SetParent($id,$CatID);
        }
               
      $CatID = $this->CreateKategorie("Waage",$parent); 
      if ( $CatID === false )
        throw new Exception("Kategorie Waage nicht definiert");
 
      $VariablenID = @IPS_GetVariableIDByName("DatumUhrzeit",$CatID);  
			if ($VariablenID === false)
        {
        $id = $this->RegisterVariableInteger("timestamp", "DatumUhrzeit","~UnixTimestamp",0);
        IPS_SetParent($id,$CatID);
        }
      $VariablenID = @IPS_GetVariableIDByName("Gewicht",$CatID);  
			if ($VariablenID === false)
        {
        $id = $this->RegisterVariableFloat("weight", "Gewicht","~Valve.F",1);
        IPS_SetParent($id,$CatID);
        }
      $VariablenID = @IPS_GetVariableIDByName("Fettfrei Anteil",$CatID);  
			if ($VariablenID === false)
        {
        $id = $this->RegisterVariableFloat("fatfree", "Fettfrei Anteil","~Valve.F",3);
        IPS_SetParent($id,$CatID);
        }
      $VariablenID = @IPS_GetVariableIDByName("Fett Anteil",$CatID);  
			if ($VariablenID === false)
        {
        $id = $this->RegisterVariableFloat("fatmassweight", "Fett Anteil","~Valve.F",2);
        IPS_SetParent($id,$CatID);
        }
      $VariablenID = @IPS_GetVariableIDByName("Fett Prozent",$CatID);  
			if ($VariablenID === false)
        {
        $id = $this->RegisterVariableFloat("fatradio", "Fett Prozent","~Valve.F",4);
        IPS_SetParent($id,$CatID);
        }





			//Lets register a variable with action   ????
			//$this->RegisterVariableInteger("Withings", "Test", "~Intensity.100");
			//$this->EnableAction("Withings");
      
	    //Timer erstellen
      $this->SetTimerInterval("WIT_UpdateTimer", $this->ReadPropertyInteger("Intervall"));
 
      //Update
     	$this->Update();
		  }

    //**************************************************************************
    //
    //**************************************************************************    
    public function Update()
      {
      $this->Logging("Update");
          
      return true;
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
			
			switch($Ident) 
        {
				case "Withings":
					SetValue($this->GetIDForIdent($Ident), $Value);
					break;
				default:
					throw new Exception("Invalid ident");
			   }
		
		  }


    //**************************************************************************
    //  Create    Kategorie
    //**************************************************************************    
    private function CreateKategorie($Name,$Parent)
      {
      if ( $Parent == 0 OR $Parent == false )
        return false;
      $id = @IPS_GetCategoryIDByName($Name,$Parent);  
      if ( $id === false)
        {}
      else
        return $id;
        
      $id = IPS_CreateCategory ();
      if ( $id === false )
        return false;
      IPS_SetParent($id,$Parent);  
      IPS_SetName ($id,$Name);
      
      return $id;
      }
		
    //**************************************************************************
    //  Logging
    //**************************************************************************    
    private function Logging($Text)
      {
      if ( $this->ReadPropertyBoolean("Logging") == false )
        return;
      $ordner = IPS_GetLogDir() . "Withings";
      if ( !is_dir ( $ordner ) )
		    mkdir($ordner);

      if ( !is_dir ( $ordner ) )
	     return;

      $time = date("d.m.Y H:i:s");
      $logdatei = IPS_GetLogDir() . "Withings/Withings.log";
      $datei = fopen($logdatei,"a+");
      fwrite($datei, $time ." ". $Text . chr(13));
      fclose($datei);
      }


    protected function RegisterTimer($Name, $Interval, $Script,$Position = 99)
      {
      $id = @IPS_GetObjectIDByIdent($Name, $this->InstanceID);
      if ($id === false)
        $id = 0;
      if ($id > 0)
        {
          if (!IPS_EventExists($id))
            throw new Exception("Ident with name " . $Name . " is used for wrong object type", E_USER_WARNING);
          if (IPS_GetEvent($id)['EventType'] <> 1)
            {
            IPS_DeleteEvent($id);
            $id = 0;
            }
        }
      if ($id == 0)
        {
        $id = IPS_CreateEvent(1);
        IPS_SetParent($id, $this->InstanceID);
        IPS_SetIdent($id, $Name);
        }
      IPS_SetName($id, $Name);
      IPS_SetHidden($id, true);
      IPS_SetEventScript($id, $Script);
      IPS_SetPosition($id,$Position);
      if ($Interval > 0)
        {
        IPS_SetEventCyclic($id, 0, 0, 0, 0, 1, $Interval);
        IPS_SetEventActive($id, true);
        } 
      else
        {
        IPS_SetEventCyclic($id, 0, 0, 0, 0, 1, 1);
        IPS_SetEventActive($id, false);
        }
      }
      
    protected function UnregisterTimer($Name)
      {
      $id = @IPS_GetObjectIDByIdent($Name, $this->InstanceID);
      if ($id > 0)
        {
        if (!IPS_EventExists($id))
          throw new Exception('Timer not present', E_USER_NOTICE);
        IPS_DeleteEvent($id);
        }
      }
    
    protected function SetTimerInterval($Name, $Interval)
      {
      $id = @IPS_GetObjectIDByIdent($Name, $this->InstanceID);
      if ($id === false)
        throw new Exception('Timer not present', E_USER_WARNING);
      if (!IPS_EventExists($id))
        throw new Exception('Timer not present', E_USER_WARNING);
      $Event = IPS_GetEvent($id);
      if ($Interval < 1)
        {
        if ($Event['EventActive'])
          IPS_SetEventActive($id, false);
        }
      else
        {
        if ($Event['CyclicTimeValue'] <> $Interval)
          IPS_SetEventCyclic($id, 0, 0, 0, 0, 1, $Interval);
        if (!$Event['EventActive'])
          IPS_SetEventActive($id, true);
        }
      }
	
	}
?>
