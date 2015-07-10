<?php

Namespace Info;

class JavaInfo extends PTConfigureBase {

    public $hidden = false;

    public $name = "Java JDK 1.7";

    public function __construct() {
      parent::__construct();
    }

    public function routesAvailable() {
      return array( "Java" =>  array_merge(parent::routesAvailable(), array("install") ) );
    }

    public function routeAliases() {
      return array("java"=>"Java", "java17"=>"Java");
    }

    public function helpDefinition() {
      $help = <<<"HELPDATA"
  This module allows you to install Java JDK 1.7 .

  Java, java, java17

        - install
        Installs a version of Oracle Java JDK 1.7. It will also configure java,
        javac and javaws to be provided by the new Oracle version.
        example: ptconfigure java17 install

HELPDATA;
      return $help ;
    }

}