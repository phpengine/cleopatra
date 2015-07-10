<?php

Namespace Info;

class StandardToolsInfo extends PTConfigureBase {

  public $hidden = false;

  public $name = "Standard Tools for any Installation";

  public function __construct() {
    parent::__construct();
  }

  public function routesAvailable() {
    return array( "StandardTools" =>  array_merge(parent::routesAvailable(), array("install") ) );
  }

  public function routeAliases() {
    return array("standard-tools"=>"StandardTools", "standardtools"=>"StandardTools",
      "stdtools"=>"StandardTools", "std-tools"=>"StandardTools");
  }

  public function helpDefinition() {
    $help = <<<"HELPDATA"
  This module allows you to install a few GC recommended Standard Tools
  for productivity in your system.  The kinds of tools we found ourselves
  installing on every box we have, client or server. These include curl,
  vim, drush and zip.

  StandardTools, standard-tools, standardtools, stdtools, std-tools

        - install
        Installs some standard tools
        example: ptconfigure stdtools install

HELPDATA;
    return $help ;
  }

}