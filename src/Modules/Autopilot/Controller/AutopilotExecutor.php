<?php

Namespace Controller ;

use Core\BootStrap;
use Core\View;

class AutopilotExecutor extends Base {

    public function execute($pageVars, $autopilot, $test = false ) {
        $params = $pageVars["route"]["extraParams"];
        $thisModel = $this->getModelAndCheckDependencies("Autopilot", $pageVars) ;
        // if we don't have an object, its an array of errors
        if (is_array($thisModel)) { return $this->failDependencies($pageVars, $this->content, $thisModel) ; }
        $this->content["package-friendly"] = ($test) ? "Autopilot Test Suite" : "Autopilot" ;
        $this->registeredModels = $autopilot->steps ;
        $this->checkForRegisteredModels($params);
        $this->content["autoExec"] = ($test) ?
            $this->executeMyTestsAutopilot($autopilot, $thisModel->params):
            $this->executeMyRegisteredModelsAutopilot($autopilot, $thisModel->params);
        return array ("type"=>"view", "view"=>"autopilot", "pageVars"=>$this->content);
    }

    protected function executeMyRegisteredModelsAutopilot($autoPilot, $autopilotParams) {
        $dataFromThis = "";
        foreach ($autoPilot->steps as $modelArray) {
            $currentControls = array_keys($modelArray) ;
            $currentControl = $currentControls[0] ;
            $currentActions = array_keys($modelArray[$currentControl]) ;
            $currentAction = $currentActions[0] ;
            $modParams = $modelArray[$currentControl][$currentAction] ;
            $modParams = $this->formatParams(array_merge($modParams, $autopilotParams)) ;
            $params = array() ;
            $params["route"] =
                array(
                    "extraParams" => $modParams ,
                    "control" => $currentControl ,
                    "action" => $currentAction ,
                ) ;
            $dataFromThis .= $this->executeControl($currentControl, $params);
            if ( \Core\BootStrap::getExitCode() !== 0 ) {
                $dataFromThis .= "Received exit code: ".\Core\BootStrap::getExitCode();
                break ; }
        }
        return $dataFromThis ;
    }

    protected function executeMyTestsAutopilot($autoPilot, $autopilotParams) {
        $dataFromThis = "";
        if (isset($autoPilot->tests) && is_array($autoPilot->tests) && count($autoPilot->tests)>0) {
            foreach ($autoPilot->tests as $modelArray) {
                $currentControls = array_keys($modelArray) ;
                $currentControl = $currentControls[0] ;
                $currentActions = array_keys($modelArray[$currentControl]) ;
                $currentAction = $currentActions[0] ;
                $modParams = $modelArray[$currentControl][$currentAction] ;
                $of = array("output-format" => "AUTO") ;
                $modParams = $this->formatParams(array_merge($modParams, $autopilotParams, $of)) ;
                $params = array() ;
                $params["route"] =
                    array(
                        "extraParams" => $modParams ,
                        "control" => $currentControl ,
                        "action" => $currentAction ,
                    ) ;
                $dataFromThis .= $this->executeControl($currentControl, $params);
                if ( \Core\BootStrap::getExitCode() !== 0 ) {
                    $dataFromThis .= "Received exit code: ".\Core\BootStrap::getExitCode();
                    break ; } } }
        else {
            $dataFromThis = "No Tests defined in autopilot";  }
        return $dataFromThis ;
    }

    private function formatParams($params) {
        $newParams = array();
        foreach($params as $origParamKey => $origParamVal) {
            $newParams[] = '--'.$origParamKey.'='.$origParamVal ; }
        $newParams[] = '--yes' ;
        $newParams[] = "--hide-title=yes";
        $newParams[] = "--hide-completion=yes";
        return $newParams ;
    }

    public function executeControl($controlToExecute, $pageVars=null) {
        $control = new \Core\Control();
        $controlResult = $control->executeControl($controlToExecute, $pageVars);
        if ($controlResult["type"]=="view") {
            $of = array("params" => array("output-format" => "AUTO")) ;
            $modParams = array_merge($controlResult["pageVars"], $of) ;
            $var = $this->executeView( $controlResult["view"], $modParams );
            return $var ; }
        else if ($controlResult["type"]=="control") {
            $this->executeControl( $controlResult["control"], $controlResult["pageVars"] ); }
    }

    public function executeView($view, Array $viewVars) {
        $viewObject = new View();
        $templateData = $viewObject->loadTemplate ($view, $viewVars) ;
//        @todo this should parse layouts properly but doesnt. so, templates only for autos for now
//        if ($view == "parallaxCli") {
//            var_dump("tdata: ", $templateData) ;
//            die() ;
//        }
//        $data = $viewObject->loadLayout ( "blank", $templateData, $viewVars) ;
        return $templateData ;
    }

}
