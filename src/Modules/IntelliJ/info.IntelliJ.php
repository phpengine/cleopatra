<?php

Namespace Info;

class IntelliJInfo extends PTConfigureBase {

    public $hidden = false;

    public $name = "IntelliJ - A great IDE from JetBrains";

    public function __construct() {
      parent::__construct();
    }

    public function routesAvailable() {
      return array( "IntelliJ" =>  array_merge(parent::routesAvailable(), array("install") ) );
    }

    public function routeAliases() {
      return array("intellij"=>"IntelliJ");
    }

    public function helpDefinition() {
      $help = <<<"HELPDATA"
  This module allows you to install Intellij, the JetBrains IDE

  IntelliJ, intellij

        - install
        Installs the latest version of Developer Tools
        example: ptconfigure gittools install

HELPDATA;
      return $help ;
    }

}