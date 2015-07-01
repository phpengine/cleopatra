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

        $vhe_url = (isset($this->params['vhe-url'])) ? $this->params['vhe-url'] : 'www.gitbucket.tld' ;

        $this->steps =
        array(

            array ( "Logging" => array( "log" => array( "log-message" => "Lets begin Configuration of a Reverse Proxy from 8080 to 80"),),),


            array ( "Logging" => array( "log" => array( "log-message" => "Make a default local environment to load balance to", ), ), ),
            array ( "EnvironmentConfig" => array( "config-default" => array(
                "guess" => true,
                "environment-name" => "local",
            ), ), ),

            // Install Apache Reverse Proxy
            array ( "Logging" => array( "log" => array( "log-message" => "Lets Add our reverse proxy Apache VHost" ),),),
            array ( "ApacheVHostEditor" => array( "add-balancer" => array(
                "guess" => true,
                "vhe-url" => "$vhe_url",
                "vhe-ip-port" => "0.0.0.0:80",
                "vhe-cluster-name" => "gitbucket-proxy",
                "vhe-default-template-name" => "http",
                "environment-name" => "default-local"
            ),),),

            array ( "Logging" => array( "log" => array( "log-message" => "Now lets restart Apache so we are serving our new proxy", ), ), ),
            array ( "ApacheControl" => array( "restart" => array(
                "guess" => true,
            ), ), ),

            // End
            array ( "Logging" => array( "log" => array( "log-message" => "Configuration of a Reverse Proxy from 8080 to 80 complete"),),),

        );

    }

}
