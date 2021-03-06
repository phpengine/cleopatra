<?php

Namespace Controller ;

class Parallax extends Base {

    public function execute($pageVars) {

        $action = $pageVars["route"]["action"];

        if ($action=="help") {
            $helpModel = new \Model\Help();
            $this->content["helpData"] = $helpModel->getHelpData($pageVars["route"]["control"]);
            return array ("type"=>"view", "view"=>"help", "pageVars"=>$this->content); }

        if ($action=="cli") {
            $thisModel = $this->getModelAndCheckDependencies(substr(get_class($this), 11), $pageVars) ;
            // if we don't have an object, its an array of errors
            if (is_array($thisModel)) { return $this->failDependencies($pageVars, $this->content, $thisModel) ; }
            $this->content["cliResult"] = $thisModel->askWhetherToRunParallelCommand();
            return array ("type"=>"view", "view"=>"parallaxCli", "pageVars"=>$this->content); }

        if ($action=="child") {
            $thisModel = $this->getModelAndCheckDependencies(substr(get_class($this), 11), $pageVars, "Child") ;
            $this->content["commandExecResult"] = $thisModel->askWhetherToDoCommandExecution($pageVars);
            $this->content["layout"] = "blank";
            return array ("type"=>"view", "view"=>"parallaxChild", "pageVars"=>$this->content); }

        \Core\BootStrap::setExitCode(1);
        $this->content["messages"][] = "Action $action is not supported by ".get_class($this)." Module";
        return array ("type"=>"control", "control"=>"index", "pageVars"=>$this->content);

    }

}