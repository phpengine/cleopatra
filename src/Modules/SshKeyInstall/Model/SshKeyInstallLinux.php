<?php

Namespace Model;

class SshKeyInstallLinux extends BaseLinuxApp {

    // Compatibility
    public $os = array("any") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("Default") ;

    protected $userName ;
    protected $userHomeDir ;
    protected $publicKey ;
    protected $actionsToMethods = array(
        "public-key" => "askPublicKeyInstall",
        "public" => "askPublicKeyInstall",
    ) ;

    public function __construct($params) {
        parent::__construct($params);
        $this->autopilotDefiner = "SshKeyInstall";
        $this->programDataFolder = "";
        $this->programNameMachine = "sshkeyinstall"; // command and app dir name
        $this->programNameFriendly = "!SshKey Inst!"; // 12 chars
        $this->programNameInstaller = "Ssh Key Install";
        $this->initialize();
    }

    protected function askPublicKeyInstall() {
        $doPubKeyInstall = (isset($this->params["yes"]) && $this->params["yes"]==true) ?
            true : $this->askWhetherToInstallLinuxAppToScreen();
        if ($doPubKeyInstall == true) { return $this->installPublicKey(); }
        return false ;
    }

    protected function askWhetherToInstallPublicKeyToScreen(){
        $question = "Install SSH Public Key?";
        return self::askYesOrNo($question);
    }

    public function installPublicKey() {
        $this->setUsername() ;
        $this->setUserHome() ;
        $this->ensureSSHDir() ;
        $this->ensureAuthUserFile() ;
        if ($this->setKey() == false) {
            return false ; }
        $this->ensureKeyInstalled() ;
        $this->restartService() ;
        return true ;
    }

    protected function setUsername() {
        if (isset($this->params["username"])) {
            $this->userName = $this->params["username"]; }
        else if (isset($this->params["user-name"])) {
            $this->params["username"] = $this->params["user-name"] ;
            $this->userName = $this->params["user-name"]; }
        else if (isset($this->params["user"])) {
            $this->params["username"] = $this->params["user"] ;
            $this->userName = $this->params["user"]; }
        else {
            $question = "Enter Username to install SSH Key to:";
            $this->userName = self::askForInput($question, true); }
    }

    protected function setUserHome() {
        if (isset($this->userName)) {
            //@todo a check if the user exists
            $userFactory = new \Model\User() ;
            $user = $userFactory->getModel($this->params);
            $user->setUser($this->userName) ;
            $this->userHomeDir = $user->getHome(); }
        else if (isset($this->params["user-home"])) {
            $this->userHomeDir = $this->params["user-home"]; }
        else if (isset($this->params["home"])) {
            $this->userHomeDir = $this->params["home"]; }
        else if (isset($this->params["guess"])) {
            $this->userHomeDir = '/home/'.$this->params["username"]; }
        else {
            $question = "Enter User home directory:";
            $this->userHomeDir = self::askForInput($question, true); }
    }

    protected function ensureSSHDir() {
        $loggingFactory = new \Model\Logging() ;
        $logging = $loggingFactory->getModel($this->params);
        $sshDir = $this->userHomeDir.DS.'.ssh' ;
        $logging->log("Looking for User SSH Directory {$sshDir}", $this->getModuleName()) ;
        if (file_exists($sshDir)) {
            $logging->log("SSH Directory {$sshDir} exists, so not creating.", $this->getModuleName()) ; }
        else {
            $logging->log("SSH Directory {$sshDir} does not exist, so creating.", $this->getModuleName()) ;
            // @todo do a chown after?
            $this->setOwnership($sshDir); }
    }

    protected function ensureAuthUserFile() {
        $loggingFactory = new \Model\Logging() ;
        $logging = $loggingFactory->getModel($this->params);
        $authFile = $this->userHomeDir.DS.'.ssh'.DS.'authorized_keys' ;
        $logging->log("Looking for User Authorized Keys File", $this->getModuleName()) ;
        if (file_exists($authFile)) {
            $logging->log("$authFile exists, so not creating.", $this->getModuleName()) ; }
        else {
            $logging->log("$authFile does not exist, so creating.", $this->getModuleName()) ;
            touch($authFile);
            // @todo do a chown after?
            $this->setOwnership($authFile); }
    }

    protected function setOwnership($file) {
        $loggingFactory = new \Model\Logging() ;
        $logging = $loggingFactory->getModel($this->params);
        $logging->log("Attempting to set Ownership of {$file}", $this->getModuleName()) ;
        if (!file_exists($file)) {
            $logging->log("$file does not exist, so not changing ownership.", $this->getModuleName()) ; }
        else {
            $logging->log("Changing ownership of $file to user {$this->user}.", $this->getModuleName()) ;
            chown($file, $this->userName); }
    }

    protected function setKey() {
        $loggingFactory = new \Model\Logging() ;
        $logging = $loggingFactory->getModel($this->params);
        $logging->log("Attempting to set Public Key", $this->getModuleName()) ;
        if (isset($this->params["public-key"])) {
            if (file_exists($this->params["public-key"])) {
                $this->publicKey = file_get_contents($this->params["public-key"]) ;}
            else {
                $logging->log("Unable to use the requested Public Key from {$this->params["public-key"]}", $this->getModuleName(), LOG_FAILURE_EXIT_CODE) ;
                return false ; } }
        else if (isset($this->params["public-key-file"])) {
            // $this->publicKey = file_get_contents($this->params["public-key-file"]) ;
            if (file_exists($this->params["public-key-file"])) {
                $this->publicKey = file_get_contents($this->params["public-key-file"]) ;}
            else {
                $logging->log("Unable to find the requested Public Key from {$this->params["public-key-file"]}", $this->getModuleName(), LOG_FAILURE_EXIT_CODE) ;
                return false ; } }
        else if (isset($this->params["public-key-data"])) {
            $this->publicKey = $this->params["public-key-data"] ; }
        else {
            $question = "Enter Public Key:";
            $this->publicKey = self::askForInput($question, true); }
    }

    protected function ensureKeyInstalled() {
        $authFile = $this->userHomeDir.DS.'.ssh'.DS.'authorized_keys' ;
        $loggingFactory = new \Model\Logging() ;
        $logging = $loggingFactory->getModel($this->params);

        if ($this->publicKey === false || (is_string($this->publicKey) && strlen($this->publicKey)<1) ) {
            $logging->log("Unable to use this Public Key", $this->getModuleName(), LOG_FAILURE_EXIT_CODE) ;
            $logging->log("{$this->publicKey}", $this->getModuleName(), LOG_FAILURE_EXIT_CODE) ;
            return false ; }

        $fileFactory = new \Model\File() ;

//        foreach ($keys as $key) {
        $params = $this->params ;
        $params["file"] = $authFile ;
        $params["search"] = $this->publicKey ;
        $file = $fileFactory->getModel($params) ;
        $res = $file->performShouldHaveLine();
//    }

//        if (isset($keyExists) && $keyExists == true) {
//            $logging->log("Key already exists, so not adding.", $this->getModuleName()) ; }
//        else {
//            $logging->log("Key does not exist in file, so adding.", $this->getModuleName()) ;
//            $keys[] = $this->publicKey."\n" ;
//            $keyFileData = implode("\n", $keys) ;
//            file_put_contents($authFile, $keyFileData) ; }
        $this->setOwnership($authFile) ;
        return $res ;
    }

    // @todo will restarting ssh break it all?
    protected function restartService() {
        $serviceFactory = new \Model\Service();
        $service = $serviceFactory->getModel($this->params);
        $service->setService("ssh") ;
        $service->restart() ;
    }

}
