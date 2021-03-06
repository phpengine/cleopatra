<?php

Namespace Info;

class ChromeDriverInfo extends PTConfigureBase {

  public $hidden = false;

  public $name = "The Chrome Browser remote controlling server";

  public function __construct() {
    parent::__construct();
  }

  public function routesAvailable() {
    return array( "ChromeDriver" =>  array_merge(parent::routesAvailable(), array("install") ) );
  }

  public function routeAliases() {
    return array("chrome-driver"=>"ChromeDriver", "chromedriver"=>"ChromeDriver",
      "chromedriver-server"=>"ChromeDriver", "chromedriverserver"=>"ChromeDriver");
  }

  public function helpDefinition() {
    $help = <<<"HELPDATA"
  This module allows you to install a few GC recommended Standard Tools
  for productivity in your system.  The kinds of tools we found ourselves
  installing on every box we have, client or server. These include curl,
  vim, drush and zip.

  ChromeDriver, chromedriver-server, chromedriver, chromedriver-srv, chromedriverserver

        - install
        Installs ChromeDriver. Note, you'll also need Java installed
        as it is a prerequisite for ChromeDriver
        example: ptconfigure chromedriver install

HELPDATA;
    return $help ;
  }

}