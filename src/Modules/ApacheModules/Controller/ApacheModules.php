<?php

Namespace Controller ;

class ApacheModules extends Base {

    public function execute($pageVars) {

        $thisModel = $this->getModelAndCheckDependencies("ApacheModules", $pageVars) ;
        var_dump($thisModel) ;
        if (!is_object($thisModel)) {
            $this->content["messages"][] = $thisModel ;
            return array ("type"=>"control", "control"=>"index", "pageVars"=>$this->content); }

        $isDefaultAction = parent::checkDefaultActions($pageVars, array(), $thisModel) ;
        if ( is_array($isDefaultAction) ) { return $isDefaultAction; }

        $this->content["messages"][] = "Invalid Action";
        return array ("type"=>"control", "control"=>"index", "pageVars"=>$this->content);
    }

}