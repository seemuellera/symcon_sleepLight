<?php

    // Klassendefinition
    class SleepLight extends IPSModule {
 
        // Der Konstruktor des Moduls
        // Überschreibt den Standard Kontruktor von IPS
        public function __construct($InstanceID) {
            // Diese Zeile nicht löschen
            parent::__construct($InstanceID);
 
            // Selbsterstellter Code
        }
 
        // Überschreibt die interne IPS_Create($id) Funktion
        public function Create() {
            
		// Diese Zeile nicht löschen.
            	parent::Create();

		// Properties
		$this->RegisterPropertyString("Sender","SleepLight");
		$this->RegisterPropertyInteger("TargetId",1);
		$this->RegisterPropertyInteger("DimStep",1);
		$this->RegisterPropertyInteger("DimStart",50);
		$this->RegisterPropertyInteger("RefreshInterval",0);

		// Variables
		$this->RegisterVariableBoolean("Status","Status","~Switch");

		// Default Actions
		$this->EnableAction("Status");

		// Timer
		$this->RegisterTimer("RefreshInformation", 0 , 'SLEEPLIGHT_RefreshInformation($_IPS[\'TARGET\']);');

        }

	public function Destroy() {

		// Never delete this line
		parent::Destroy();
	}
 
        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() {

		
		$newInterval = $this->ReadPropertyInteger("RefreshInterval") * 1000;
		$this->SetTimerInterval("RefreshInformation", $newInterval);
		

            	// Diese Zeile nicht löschen
            	parent::ApplyChanges();
        }


	public function GetConfigurationForm() {

        	
		// Initialize the form
		$form = Array(
            		"elements" => Array(),
					"actions" => Array()
        		);

		// Add the Elements
		$form['elements'][] = Array("type" => "NumberSpinner", "name" => "RefreshInterval", "caption" => "Refresh Interval");
		$form['elements'][] = Array("type" => "NumberSpinner", "name" => "DimStep", "caption" => "Dimming Step");
		$form['elements'][] = Array("type" => "NumberSpinner", "name" => "DimStart", "caption" => "Dimming Start Value");
		$form['elements'][] = Array("type" => "SelectObject", "name" => "TargetId", "caption" => "Target Object");
		

		// Add the buttons for the test center
		$form['actions'][] = Array("type" => "Button", "label" => "Run next cycle", "onClick" => 'SLEEPLIGHT_RefreshInformation($id);');
		$form['actions'][] = Array("type" => "Button", "label" => "Switch On", "onClick" => 'SLEEPLIGHT_SwitchOn($id);');
		$form['actions'][] = Array("type" => "Button", "label" => "Switch Off", "onClick" => 'SLEEPLIGHT_SwitchOff($id);');

		// Return the completed form
		return json_encode($form);

	}

	public function RefreshInformation() {

		if (GetValue($this->GetIDForIdent("Status") ) ) {
		
		
			$this->NextStep();
		}

	}

	public function SwitchOn() {
	
		$this->SetDim($this->ReadPropertyInteger("DimStart") );	
		SetValue($this->GetIDForIdent("Status"), true );	
	}

	public function NextStep() {
	
		$newDimValue = GetValue($this->ReadPropertyInteger("TargetId") ) - $this->ReadPropertyInteger("DimStep");		

		if ($newDimValue <= 0) {
		
			$this->SwitchOff();
		}
		else {
		
			$this->SetDim($newDimValue);
		}
	}

	protected function SetDim($newDimValue) {

		$result = RequestAction($this->ReadPropertyInteger("TargetId"), $newDimValue);

		if (! $result) {

			IPS_LogMessage($_IPS['SELF'],"SLEEPLIGHT - SwitchOn not possible for device $targetId - The action could not be triggered");
			return 2;
		}

		IPS_LogMessage($_IPS['SELF'], "SLEEPLIGHT - Dimming device " . $this->ReadPropertyInteger("TargetId") . " to new level $newDimValue");

	}

	public function SwitchOff() {
	
		$this->SetDim(0);
		SetValue($this->GetIDForIdent("Status"), false);
	}

	public function RequestAction($Ident, $Value) {
	
	
		switch ($Ident) {
		
			case "Status":
				// Default Action for Status Variable
				if ($Value) {
				
					$this->SwitchOn();
				}
				else {
				
					$this->SwitchOff();
				}

				// Neuen Wert in die Statusvariable schreiben
				SetValue($this->GetIDForIdent($Ident), $Value);
				break;
			default:
				throw new Exception("Invalid Ident");
		}
	}

    }
?>
