<?php

Namespace Info;

class JRushInfo extends PTConfigureBase {

    public $hidden = false;

    public $name = "JRush - The Joomla command line utility from Golden Contact";

    public function __construct() {
      parent::__construct();
    }

    public function routesAvailable() {
      return array( "JRush" =>  array_merge(parent::routesAvailable(), array("install") ) );
    }

    public function routeAliases() {
      return array("jrush"=>"JRush", "Jrush"=>"JRush", "jRush"=>"JRush");
    }

    public function helpDefinition() {
      $help = <<<"HELPDATA"
  This module allows you to install or Update JRush.

  JRush, jrush, Jrush, jRush

        - install
        Installs the latest version of jRush
        example: ptconfigure jRush install

HELPDATA;
      return $help ;
    }

}