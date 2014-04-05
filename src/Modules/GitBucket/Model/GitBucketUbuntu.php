<?php

Namespace Model;

class GitBucketUbuntu extends BaseLinuxApp {

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
        $this->autopilotDefiner = "GitBucket";
        $this->installCommands = array(
            array("method"=> array("object" => $this, "method" => "executeDependencies", "params" => array()) ),
        );
        $this->uninstallCommands = array(
            array("method"=> array("object" => $this, "method" => "executeDependencies", "params" => array()) ),);
        $this->programDataFolder = "";
        $this->programNameMachine = "gitlab"; // command and app dir name
        $this->programNameFriendly = "!Git Lab!!"; // 12 chars
        $this->programNameInstaller = "Git Lab";
        $this->initialize();
    }

    public function executeDependencies() {
        $gitTools = new \Model\GitTools($this->params);
        $gitTools->ensureInstalled();
    }

}