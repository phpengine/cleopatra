<?php

Namespace Core ;

class AutoPilotConfigured extends AutoPilot {

    public $steps ;

    public function __construct() {
        $this->setSteps();
    }

    /* Steps */
    private function setSteps() {

        include ("settings.php") ;

        $this->steps =
            array(
                array ( "Logging" => array( "log" => array( "log-message" => "Lets Manage Configuration on the Jenkins Environment" ),),),
                array ( "Logging" => array( "log" => array( "log-message" => "Lets Ensure PHP and Git on the Jenkins Environment" ),),),
                array ( "RunCommand" => array("install" => array(
                    "guess" => true,
                    "command" => 'ptconfigure autopilot execute --autopilot-file="build/config/ptconfigure/cleofy/medium-build-prep-ubuntu.php"',
                ),),),
                array ( "Logging" => array( "log" => array( "log-message" => "Lets Invoke Pharaoh Configure and Pharaoh Deploy on the Jenkins Environment" ),),),
                array ( "RunCommand" => array("install" => array(
                    "guess" => true,
                    "command" => 'ptconfigure autopilot execute --autopilot-file="build/config/ptconfigure/cleofy/medium-build-invoke-cleo-dapper-new.php"',
                ),),),
                array ( "Logging" => array( "log" => array( "log-message" => "Lets setup Jenkins Box on the Jenkins Environment" ),),),
                array ( "RunCommand" => array("install" => array(
                    "guess" => true,
                    "command" => 'ptconfigure autopilot execute --autopilot-file="build/config/ptconfigure/cleofy/medium-build-invoke-build.php"',
                ),),),
                array ( "Logging" => array( "log" => array( "log-message" => "Managing Configuration on Jenkins environment complete"),),),
            );

    }

}
