<?php

Namespace Info;

class VirtualboxInfo extends PTConfigureBase {

    public $hidden = false;

    public $name = "Virtualbox - The local Virtual Machine Solution";

    public function __construct() {
      parent::__construct();
    }

    public function routesAvailable() {
      return array( "Virtualbox" =>  array_merge(parent::routesAvailable(), array("install") ) );
    }

    public function routeAliases() {
      return array("virtualbox"=>"Virtualbox");
    }

    public function helpDefinition() {
      $help = <<<"HELPDATA"
  This module allows you to install Virtualbox, the popular Virtual Machine Solution.

  Virtualbox, virtualbox

        - install
        Installs Virtualbox through apt-get
        example: ptconfigure virtualbox install
        example: ptconfigure virtualbox install -yg
        example: ptconfigure virtualbox install --with-guest-additions

HELPDATA;
      return $help ;
    }

}