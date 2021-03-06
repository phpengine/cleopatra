<?php

Namespace Info;

class ComposerInfo extends PTConfigureBase {

    public $hidden = false;

    public $name = "Composer - Upgrade or Re-install Composer";

    public function __construct() {
      parent::__construct();
    }

    public function routesAvailable() {
      return array( "Composer" =>  array_merge(parent::routesAvailable(), array("install") ) );
    }

    public function routeAliases() {
      return array("composer"=>"Composer");
    }

    public function helpDefinition() {
      $help = <<<"HELPDATA"
  This module allows you to update Composer.

  Composer, composer

        - install
        Installs the latest version of composer
        example: ptconfigure composer install

HELPDATA;
      return $help ;
    }

}