<?php

Namespace Core ;

class AutoPilotConfigured extends AutoPilot {

    public $steps ;
    protected $myUser ;

    public function __construct() {
        $this->setSteps();
        $this->addDapperfileToStepsIfProvided();
    }

    /* Steps */
    protected function setSteps() {

        $this->steps =
            array(
                array ( "Logging" => array( "log" => array( "log-message" => "Lets begin Configuration of a PTVirtualize Box"),),),

                array ( "Logging" => array( "log" => array( "log-message" => "Lets ensure the PTVirtualize user can use Sudo without a Password"),),),
                array ( "SudoNoPass" => array( "install" => array(
                    "install-user-name" => "ptvirtualize"
                ),),),

                // All Pharoes
                array ( "Logging" => array( "log" => array( "log-message" => "Lets ensure PTConfigure" ),),),
                array ( "PTConfigure" => array( "ensure" => array(),),),
                array ( "Logging" => array( "log" => array( "log-message" => "Lets ensure PTDeploy" ),),),
                array ( "PTDeploy" => array( "ensure" => array(),),),
                array ( "Logging" => array( "log" => array( "log-message" => "Lets ensure PTTest" ),),),
                array ( "PTTest" => array( "ensure" => array(),),),

                // Standard Tools
                array ( "Logging" => array( "log" => array( "log-message" => "Lets ensure some standard tools are installed" ),),),
                array ( "StandardTools" => array( "ensure" => array(),),),

                // Git Tools
                array ( "Logging" => array( "log" => array( "log-message" => "Lets ensure some git tools are installed" ),),),
                array ( "GitTools" => array( "ensure" => array(),),),

                // Git Key Safe
                array ( "Logging" => array( "log" => array( "log-message" => "Lets ensure Git SSH Key Safe version is are installed" ),),),
                array ( "GitKeySafe" => array( "ensure" => array(),),),

                // PHP Modules
                array ( "Logging" => array( "log" => array( "log-message" => "Lets ensure our common PHP Modules are installed" ),),),
                array ( "PHPModules" => array( "ensure" => array(),),),

                // Apache
                array ( "Logging" => array( "log" => array( "log-message" => "Lets ensure Apache Server is installed" ),),),
                array ( "ApacheServer" => array( "ensure" =>  array("version" => "2.2"), ), ),

                // Apache Modules
                array ( "Logging" => array( "log" => array( "log-message" => "Lets ensure our common Apache Modules are installed" ),),),
                array ( "ApacheModules" => array( "ensure" => array(),),),

                // Restart Apache for new modules
                array ( "Logging" => array( "log" => array( "log-message" => "Lets restart Apache for our PHP and Apache Modules" ),),),
                array ( "RunCommand" => array( "install" => array(
                    "guess" => true,
                    "command" => "ptdeploy apachecontrol restart --yes --guess",
                ) ) ),

                //Mysql
                array ( "Logging" => array( "log" => array( "log-message" => "Lets ensure Mysql Server is installed" ),),),
                array ( "MysqlServer" => array( "ensure" =>  array(
                    "version" => "5",
                    "version-operator" => "+"
                ), ), ),
                array ( "Logging" => array( "log" => array( "log-message" => "Lets ensure a Mysql Admin User is installed"),),),
                array ( "MysqlAdmins" => array( "install" => array(
                    "root-user" => "root",
                    "root-pass" => "ptconfigure",
                    "new-user" => "root",
                    "new-pass" => "root",
                    "mysql-host" => "127.0.0.1"
                ) ) ),

                array ( "Logging" => array( "log" => array(
                    "log-message" => "PTConfigure Configuration Management of your PTVirtualize VM complete"
                ),),),

            );

    }

    protected function addDapperfileToStepsIfProvided() {

        if (isset($this->params["dapperfile"])) { $dfile = $this->params["dapperfile"] ; }
        if (isset($this->params["dapper-auto"])) { $dfile = $this->params["dapper-auto"] ; }
        if (isset($this->params["dapper-autopilot"])) { $dfile = $this->params["dapper-autopilot"] ; }
        if (isset($this->params["ptdeploy-auto"])) { $dfile = $this->params["ptdeploy-auto"] ; }
        if (isset($this->params["ptdeploy-autopilot"])) { $dfile = $this->params["ptdeploy-autopilot"] ; }

        if (isset($dfile)) {

            $a1 = array ( "Logging" => array( "log" => array(
                "log-message" => "A PTDeploy Autopilot was also provided, so we'll execute that too"
            ),),) ;
            array_push($this->steps, $a1) ;

            $a2 = array ( "RunCommand" => array( "install" => array(
                "guess" => true,
                "command" => "sudo ptdeploy auto x --yes --guess --af=$dfile",
            ) ) ) ;
            array_push($this->steps, $a2) ;

            $a3 = array ( "Logging" => array( "log" => array(
                "log-message" => "PTDeploy Automated Application Deployment of your PTVirtualize VM complete"
            ),),) ;
            array_push($this->steps, $a3) ;

        }

    }

}
