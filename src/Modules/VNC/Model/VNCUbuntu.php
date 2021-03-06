<?php

Namespace Model;

class VNCUbuntu extends BaseLinuxApp {

    // Compatibility
    public $os = array("Linux") ;
    public $linuxType = array("Debian") ;
    public $distros = array("Ubuntu") ;
    public $versions = array("11.04", "11.10", "12.04", "12.10", "13.04", "14.04") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("Default") ;

    public function __construct($params) {
        parent::__construct($params);
        $this->autopilotDefiner = "VNC";
        $this->installCommands = array(
            array("method"=> array("object" => $this, "method" => "packageAdd", "params" => array("Apt", "vnc4server")) ),
        ) ;
        $this->uninstallCommands = array(
            array("method"=> array("object" => $this, "method" => "packageRemove", "params" => array("Apt", "vnc4server")) ),
        ) ;
        $this->programDataFolder = "/opt/vnc"; // command and app dir name
        $this->programNameMachine = "vnc"; // command and app dir name
        $this->programNameFriendly = " ! VNC !"; // 12 chars
        $this->programNameInstaller = "VNC";
        $this->statusCommand = "which vncserver" ;
        $this->versionInstalledCommand = SUDOPREFIX."apt-cache policy vnc4server" ;
        $this->versionRecommendedCommand = SUDOPREFIX."apt-cache policy vnc4server" ;
        $this->versionLatestCommand = SUDOPREFIX."apt-cache policy vnc4server" ;
        $this->initialize();
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