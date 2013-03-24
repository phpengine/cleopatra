<?php

Namespace Controller ;

class DevClient extends Base {

    public function execute($pageVars) {

      $this->content["route"] = $pageVars["route"];
      $this->content["messages"] = $pageVars["messages"];
      $action = $pageVars["route"]["action"];

      $phpUnitModel = new \Model\PHPUnit();
      $this->content["phpUnitInstallResult"] = $phpUnitModel->askWhetherToInstallPHPApp();

      $phpCSModel = new \Model\PHPCS();
      $this->content["phpCSInstallResult"] = $phpCSModel->askWhetherToInstallPHPApp();

      return array ("type"=>"view", "view"=>"installDevClient", "pageVars"=>$this->content);

    }

}