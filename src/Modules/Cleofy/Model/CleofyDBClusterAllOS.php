<?php

Namespace Model;

// @todo shouldnt this extend base templater? is it missing anything?
class CleofyDBClusterAllOS extends Base {

    // Compatibility
    public $os = array("any") ;
    public $linuxType = array("any") ;
    public $distros = array("any") ;
    public $versions = array("any") ;
    public $architectures = array("any") ;

    // Model Group
    public $modelGroup = array("DBCluster") ;

    private $environments ;
    private $environmentReplacements ;

    public function __construct($params) {
      parent::__construct($params);
    }

    public function askWhetherToCleofy() {
        if ($this->askToScreenWhetherToCleofy() != true) { return false; }
        $this->setEnvironmentReplacements() ;
        $this->getEnvironments() ;
        $this->doCleofy() ;
        return true;
    }

    public function askToScreenWhetherToCleofy() {
        if (isset($this->params["yes"]) && $this->params["yes"]==true) { return true ; }
        $question = 'Cleofy This?';
        return self::askYesOrNo($question, true);
    }

    public function setEnvironmentReplacements() {
        $this->environmentReplacements =
            array( "cleo" => array(
               // array("var"=>"dap_proj_cont_dir", "friendly_text"=>"Project Container directory, (inc slash)"),
            ) );
    }

    public function getEnvironments() {
        $environmentConfigModelFactory = new EnvironmentConfig();
        $environmentConfigModel = $environmentConfigModelFactory->getModel($this->params);
        $environmentConfigModel->askWhetherToEnvironmentConfig($this->environmentReplacements) ;
        $this->environments = $environmentConfigModel->environments ;
    }

    public function getServerArrayText($serversArray) {
        $serversText = "";
        foreach($serversArray as $serverArray) {
            $serversText .= 'array(';
            $serversText .= '"target" => "'.$serverArray["target"].'", ';
            $serversText .= '"user" => "'.$serverArray["user"].'", ';
            $serversText .= '"pword" => "'.$serverArray["password"].'", ';
            $serversText .= '),'."\n"; }
        return $serversText;
    }

    private function doCleofy() {
      $templatesDir = str_replace("Model", "Templates/EnvSpecific", dirname(__FILE__) ) ;
      $templates = scandir($templatesDir);
      foreach ($this->environments as $environment) {
        foreach ($templates as $template) {
          if (!in_array($template, array(".", ".."))) {
              $templatorFactory = new \Model\Templating();
              $templator = $templatorFactory->getModel($this->params);
              $newFileName = str_replace("environment", $environment["any-app"]["gen_env_name"], $template ) ;
              $autosDir = getcwd().DS.'build'.DS.'config'.DS.'ptconfigure'.DS.'cleofy'.DS.'autopilots'.DS.'generated';
              $targetLocation = $autosDir.DIRECTORY_SEPARATOR.$newFileName ;
              $templator->template(
                  file_get_contents($templatesDir.DIRECTORY_SEPARATOR.$template),
                  array(
                      "gen_srv_array_text" => $this->getServerArrayText($environment["servers"]) ,
                      "env_name" => $environment["any-app"]["gen_env_name"],
                      "db_nodes_env" => $this->getEnvName("database") ,
                  ),
                  $targetLocation );
          echo $targetLocation."\n"; } } }
    }

    public function getEnvName($envType) {
        if (isset($this->params["$envType-nodes-env"])) {
            $this->params["$envType-nodes-environment"] = $this->params["$envType-nodes-env"] ; }
        if (isset($this->params["$envType-nodes-environment"])) { return $this->params["$envType-nodes-environment"] ; }
        $question = "Enter name of environment with your ".ucfirst($envType)."nodes" ;
        $this->params["$envType-nodes-environment"] = $this->askForInput($question) ;
        return $this->params["$envType-nodes-environment"] ;
    }

}