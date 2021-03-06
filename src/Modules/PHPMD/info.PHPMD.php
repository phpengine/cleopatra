<?php

Namespace Info;

class PHPMDInfo extends PTConfigureBase {

    public $hidden = false;

    public $name = "PHP Mess Detector - The static analysis tool";

    public function __construct() {
      parent::__construct();
    }

    public function routesAvailable() {
      return array( "PHPMD" =>  array_merge(parent::routesAvailable(), array("install") ) );
    }

    // @todo remove duplicate below
    public function routeAliases() {
      return array("phpmd"=>"PHPMD", "phpmd"=>"PHPMD", "php-md"=>"PHPMD");
    }

    public function helpDefinition() {
      $help = <<<"HELPDATA"
  This module allows you to install PHPMD from a GC Repo.

  PHPMD

        - install
        Installs the latest GC Repo version of PHPMD
        example: ptconfigure phpmd install

HELPDATA;
      return $help ;
    }

}