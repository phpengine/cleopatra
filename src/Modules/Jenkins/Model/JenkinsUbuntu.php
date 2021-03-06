<?php

Namespace Model;

//@todo if we can use a wget/binary method like selenium or gitbucket then we can easily use across other linux os
class JenkinsUbuntu extends BaseLinuxApp {

    // Compatibility
    public $os = array("Linux") ;
    public $linuxType = array("Debian") ;
    public $distros = array("Ubuntu") ;
    public $versions = array("11.04", "11.10", "12.04", "12.10", "13.04") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("Default") ;

    public function __construct($params) {
        parent::__construct($params);
        $this->autopilotDefiner = "Jenkins";
        $this->installCommands = $this->getInstallCommands();
        $this->uninstallCommands =
            array("method"=> array("object" => $this, "method" => "packageRemove", "params" => array("Apt", "jenkins")) ) ;
        $this->programDataFolder = "/var/lib/jenkins"; // command and app dir name
        $this->programNameMachine = "jenkins"; // command and app dir name
        $this->programNameFriendly = " ! Jenkins !"; // 12 chars
        $this->programNameInstaller = "Jenkins";
        $this->statusCommand = SUDOPREFIX."jenkins -v" ;
        $this->versionInstalledCommand = SUDOPREFIX."apt-cache policy jenkins" ;
        $this->versionRecommendedCommand = SUDOPREFIX."apt-cache policy jenkins" ;
        $this->versionLatestCommand = SUDOPREFIX."apt-cache policy jenkins" ;
        $this->initialize();
    }

    protected function getInstallCommands() {
        $ray = array(
            array("command" => array(
                "cd /tmp" ,
                "wget -q -O - http://pkg.jenkins-ci.org/debian/jenkins-ci.org.key | ".SUDOPREFIX." apt-key add -",
                // @todo we should be doing this with the file module so we don't write this multiple times in the file
                "echo deb http://pkg.jenkins-ci.org/debian binary/ > /etc/apt/sources.list.d/jenkins.list",
                "apt-get update -y" ) ),
            array("method"=> array("object" => $this, "method" => "packageAdd", "params" => array("Apt", "jenkins")) ),
        ) ;
        if (isset($this->params["with-http-proxy"]) && $this->params["with-http-proxy"]==true) {
            $dapperAuto = $this->getDapperAutoPath() ;
            $ray[0]["command"][] = SUDOPREFIX."ptdeploy autopilot execute --af=$dapperAuto" ; }
        return $ray ;
    }

    private function getDapperAutoPath() {
        $path = dirname(dirname(__FILE__)).'/Autopilots/PTDeploy/proxy-8080-to-80.php' ;
        return $path ;
    }

    public function versionInstalledCommandTrimmer($text) {
        $done = substr($text, 23, 15) ;
        return $done ;
    }

    public function versionLatestCommandTrimmer($text) {
        $done = substr($text, 42, 23) ;
        return $done ;
    }

    public function versionRecommendedCommandTrimmer($text) {
        $done = substr($text, 42, 23) ;
        return $done ;
    }

}