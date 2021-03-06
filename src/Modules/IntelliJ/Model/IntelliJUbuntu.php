<?php

Namespace Model;

class IntelliJUbuntu extends BaseLinuxApp {

    // Compatibility
    public $os = array("Linux") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("Default") ;

    protected $iv ;

    public function __construct($params) {
        parent::__construct($params);
        $this->autopilotDefiner = "IntelliJ";
        $this->installCommands = array (
            array("method"=> array("object" => $this, "method" => "askForIntelliJVersion", "params" => array()) ),
            array("method"=> array("object" => $this, "method" => "ensureJava", "params" => array()) ),
            array("command" => array(
                    "cd /tmp" ,
                    "git clone https://github.com/PharaohTools/ptconfigure-intellij{$this->iv} intellij",
                    "rm -rf ****PROGDIR****",
                    "mkdir -p ****PROGDIR****",
                    "mv /tmp/intellij/* ****PROGDIR****",
                    "chmod -R 777 ****PROGDIR****",
                    "rm -rf /tmp/intellij" ) ),
            array("method"=> array("object" => $this, "method" => "deleteExecutorIfExists", "params" => array()) ),
            array("method"=> array("object" => $this, "method" => "saveExecutorFile", "params" => array()) ),
        );
        $this->uninstallCommands = array(
            array("command" => array("rm -rf ****PROGDIR****") ),
            array("method"=> array("object" => $this, "method" => "deleteExecutorIfExists", "params" => array()) ),
        );
        $this->programDataFolder = "/opt/intellij"; // command and app dir name
        $this->programNameMachine = "intellij"; // command and app dir name
        $this->programNameFriendly = "IntelliJ IDE"; // 12 chars
        $this->programNameInstaller = "IntelliJ IDE";
        $this->programExecutorFolder = "/usr/bin";
        $this->programExecutorTargetPath = "intellij.sh";
        $this->programExecutorCommand = $this->programDataFolder.'/'.$this->programExecutorTargetPath;
        $this->statusCommand = "cat /usr/bin/intellij > /dev/null 2>&1";
        // @todo dont hardcode the installed version
        $this->versionInstalledCommand = 'echo "12.1"' ;
        $this->versionRecommendedCommand = 'echo "12.1"' ;
        $this->versionLatestCommand = 'echo "12.1"' ;
        $this->initialize();
    }

    protected function askForIntelliJVersion(){
        $ao = array("12" => "", "13" => "") ;
        if (isset($this->params["version"]) && in_array($this->params["version"], array_keys($ao))) {
            $this->iv = $this->params["version"] ; }
        else if (isset($this->params["guess"])) {
            $index = count(array_keys($ao))-1 ;
            $ind = (isset($ao[$index])) ? $ao[$index] : "12" ;
            $this->iv = ($index=="12") ? "" : "-".$ind ; }
        else {
            $question = 'Enter IntelliJ Version';
            return self::askForArrayOption($question, $ao, true); }
    }

    // todo intellij should ensure java
    public function ensureJava() {
		$javaFactory = new \Model\Java();
		$java = $javaFactory->getModel($this->params);
		return $java->ensureInstalled();
    }

    public function versionInstalledCommandTrimmer($text) {
        $done = substr($text, 0, 4) ;
        return $done ;
    }

    public function versionLatestCommandTrimmer($text) {
        $done = substr($text, 0, 4) ;
        return $done ;
    }

    public function versionRecommendedCommandTrimmer($text) {
        $done = substr($text, 0, 4) ;
        return $done ;
    }

}
