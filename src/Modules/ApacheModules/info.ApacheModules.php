<?php

Namespace Info;

class ApacheModulesInfo extends Base {

    public $hidden = false;

    public $name = "Apache Modules - Commonly used modules for Apache";

    public function __construct() {
      parent::__construct();
    }

    public function routesAvailable() {
      return array( "ApacheModules" =>  array_merge(parent::routesAvailable(), array("install") ) );
    }

    public function routeAliases() {
      return array("apache-modules"=>"ApacheModules", "apachemods"=>"ApacheModules", "apachemodules"=>"ApacheModules");
    }

    public function autoPilotVariables() {
      return array(
        "ApacheModules" => array(
          "ApacheModules" => array(
            "programDataFolder" => "/var/lib/jenkins", // command and app dir name
            "programNameMachine" => "jenkins", // command and app dir name
            "programNameFriendly" => " ! Jenkins !", // 12 chars
            "programNameInstaller" => "Jenkins",
          )
        )
      );
    }

    public function helpDefinition() {
      $help = <<<"HELPDATA"
  This command is part of Core and provides you  with a method by which you can configure Application Settings.
  You can configure default application settings, ie: mysql admin user, host, pass

  ApacheModules, apachemods, apache-modules, apachemodules

        - install
        Installs common apache Modules
        example: cleopatra apache-modules install

HELPDATA;
      return $help ;
    }

}