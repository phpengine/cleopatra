<?php

Namespace Core ;

class AutoPilotConfigured extends AutoPilot {

    public $steps ;

    public function __construct($params = null) {
        parent::__construct($params);
        $this->setSteps();
    }

    /* Steps */
    private function setSteps() {
        $apache_user = $this->getApacheUser();
        $this->steps =
            array(

                array ( "Logging" => array( "log" => array( "log-message" => "Lets configure users and permissions for Pharaoh Build"),),),

                array ( "Logging" => array( "log" => array( "log-message" => "Allow user ptbuild a passwordless sudo", ), ), ),
                array ( "SudoNoPass" => array( "install" => array(
                    "guess" => true,
                    "install-user-name" => 'ptbuild',
                ), ), ),

                array ( "Logging" => array( "log" => array( "log-message" => "Allow apache user to switch to ptbuild user", ), ), ),
                array ( "File" => array( "should-have-line" => array(
                    "guess" => true,
                    "filename" => "/etc/sudoers",
                    "line" => "{$apache_user} ALL = NOPASSWD: /usr/bin/su - ptbuild",
                ), ), ),

                array ( "Logging" => array( "log" => array( "log-message" => "Make the PT Build Settings file writable", ), ), ),
                array ( "Chmod" => array( "path" => array(
                    "path" => PFILESDIR.'ptbuild'.DS.'ptbuild'.DS.'ptbuildvars',
                    "mode" => 0777,
                ), ), ),

                array ( "Logging" => array( "log" => array( "log-message" => "Ensure the Pipes Directory exists", ), ), ),
                array ( "Mkdir" => array( "path" => array(
                    "path" => PIPEDIR
                ), ), ),

                array ( "Logging" => array( "log" => array( "log-message" => "Ensure the Pipes Directory is writable", ), ), ),
                array ( "Chmod" => array( "path" => array(
                    "path" => PIPEDIR,
                    "recursive" => true,
                    "mode" => 0777,
                ), ), ),

                array ( "Logging" => array( "log" => array( "log-message" => "Configuration Management for Pharaoh Build Complete"),),),

            );

    }

    protected function getApacheUser() {
        $system = new \Model\SystemDetection();
        $thisSystem = $system->getModel($this->params);
        if (in_array($thisSystem->os, array("Darwin") ) ) {
            $apacheUser = "_www" ; }
        else if ($thisSystem->os == "Linux" && in_array($thisSystem->os, array("Debian") ) ) {
            $apacheUser = "www-data" ; }
        else if ($thisSystem->os == "Linux" && in_array($thisSystem->os, array("Redhat") ) ) {
            $apacheUser = "httpd" ; }
        else {
            $apacheUser = "www-data" ; }
        return $apacheUser ;
    }

}