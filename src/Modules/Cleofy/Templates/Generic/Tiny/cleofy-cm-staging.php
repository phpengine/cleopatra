<?php

Namespace Core ;

class AutoPilotConfigured extends AutoPilot {

    public $steps ;

    public function __construct() {
        $this->setSteps();
    }

    /* Steps */
    private function setSteps() {

        include(dirname(__DIR__)).DS."settings.php"  ;

        $this->steps =
            array(
                array ( "Logging" => array( "log" => array( "log-message" => "Lets Manage Configuration on the Staging Environment" ),),),
                array ( "Logging" => array( "log" => array( "log-message" => "Lets Prep Ubuntu on the Staging Environment" ),),),
                array ( "RunCommand" => array("install" => array(
                    "guess" => true,
                    "command" => 'ptconfigure autopilot execute --autopilot-file="build/config/ptconfigure/cleofy/autopilots/generated/tiny-staging-prep-ubuntu.php"',
                ),),),
                array ( "Logging" => array( "log" => array( "log-message" => "Lets Invoke Cleo and Dapper on the Staging Environment" ),),),
                array ( "RunCommand" => array("install" => array(
                    "guess" => true,
                    "command" => 'ptconfigure autopilot execute --autopilot-file="build/config/ptconfigure/cleofy/autopilots/generated/tiny-staging-invoke-cleo-dapper-new.php"',
                ),),),
                array ( "Logging" => array( "log" => array( "log-message" => "Lets setup a Standalone Server Box on the Staging Environment" ),),),
                array ( "RunCommand" => array("install" => array(
                    "guess" => true,
                    "command" => 'ptconfigure autopilot execute --autopilot-file="build/config/ptconfigure/cleofy/autopilots/generated/tiny-staging-invoke-standalone-server.php"',
                ),),),
                array ( "Logging" => array( "log" => array( "log-message" => "Managing Configuration on Staging environment complete"),),),
            );

    }

}
