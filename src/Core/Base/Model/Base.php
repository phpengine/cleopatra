<?php

Namespace Model;

class Base {

    public $params ;
    public $autopilotDefiner ;
    public $programNameFriendly;
    public $programNameInstaller;
    protected $installUserName;
    protected $installUserHomeDir;
    protected $programNameMachine ;
    protected $programDataFolder;
    protected $startDirectory;
    protected $titleData;
    protected $completionData;
    protected $bootStrapData;
    protected $extraBootStrap;
    protected $programExecutorFolder;
    protected $programExecutorTargetPath;
    protected static $tempDir;
    protected $defaultStatusCommandPrefix ;
    protected $statusCommand;
    protected $statusCommandExpects;
    protected $versionInstalledCommand;
    protected $versionRecommendedCommand;
    protected $versionLatestCommand;

    public function __construct($params) {
        if (in_array(PHP_OS, array("Windows", "WINNT"))) {
            $this->tempDir =  getenv('TEMP'); }
        else {
            $this->tempDir =  '/tmp'; }
        $this->autopilotDefiner = $this->getModuleName() ;
        $this->setCmdLineParams($params);
    }

    protected function populateTitle() {
        $this->titleData = <<<TITLE
*******************************
*        Pharaoh Tools        *
*         $this->programNameFriendly        *
*******************************

TITLE;
    }

    protected function populateTinyTitle() {
        $this->titleData = "" ; // "$this->programNameInstaller Starting\n";
    }

    protected function populateTinyCompletion() {
        $this->completionData = "" ; // "$this->programNameInstaller Complete\n";
    }

    protected function populateCompletion() {
        $this->completionData = <<<COMPLETION
... All done!
*******************************
Thanks for installing , visit www.pharaohtools.com for more

COMPLETION;
    }

    protected function setAutoPilotVariables($autoPilot) {
        foreach ( $autoPilot as $step ) { // this should only happen once
            $keys = array_keys($step);
            foreach ($keys as $property) {
                $this->$property = $step[$property] ; } }
    }

    protected function executeAsShell($multiLineCommand, $message=null) {
        $loggingFactory = new \Model\Logging();
        $this->params["echo-log"] = true ;
        $logging = $loggingFactory->getModel($this->params);
        $tempFile = $this->tempfileFromCommand($multiLineCommand) ;
        //@note these chmods are required to make bash run scripts
        // echo "chmod 755 $tempFile 2>/dev/null\n";
        if (!is_executable($tempFile)) {
            // @todo this wont work on windows
            shell_exec("chmod 755 $tempFile 2>/dev/null");
            // echo "chmod +x $tempFile 2>/dev/null\n";
            shell_exec("chmod +x $tempFile 2>/dev/null"); }
//        $logging->log("Changing $tempFile Permissions", $this->getModuleName());
        $logging->log("Executing $tempFile", $this->getModuleName());
        // @todo this should refer to the actual shell we are running
        $commy = "{$tempFile}" ;
        $rc = $this->executeAndGetReturnCode($commy, true) ;
        if ($message !== null) { echo $message."\n"; }
        shell_exec("rm $tempFile");
        $logging->log("Temp File $tempFile Removed", $this->getModuleName());
//        var_dump($rc) ;
        return $rc["rc"] ;
    }

    protected function tempfileFromCommand($multiLineCommand) {
        $loggingFactory = new \Model\Logging();
        $params["echo-log"] = true ;
        $logging = $loggingFactory->getModel($this->params);
        $tempFile = $this->tempDir.DS."ptconfigure-temp-script-".mt_rand(100, 99999999999).".sh";
//        $logging->log("Creating $tempFile", $this->getModuleName());
        $fileVar = "";
        $multiLineCommand = $this->multilineToArray($multiLineCommand) ;
        foreach ($multiLineCommand as $command) { $fileVar .= $command."\n" ; }
        file_put_contents($tempFile, $fileVar) ;
        return $tempFile ;
    }

    protected static function multilineToArray($multiLineCommand) {
        if (!is_array($multiLineCommand)) {
            $multiLineCommand = explode("\n", $multiLineCommand) ;  }
        $newRay = array() ;
        foreach ($multiLineCommand as $singleCommand) {
            $entry = str_replace(PHP_EOL, "", $singleCommand) ;
            $entry = str_replace("\n", "", $entry) ;
            $entry = str_replace("\r\n", "", $entry) ;
            $newRay[] = $entry ; }
        return $multiLineCommand ;
    }

    protected static function tempfileStaticFromCommand($multiLineCommand) {
        $loggingFactory = new \Model\Logging();
        $params["echo-log"] = true ;
        $logging = $loggingFactory->getModel($params);
        $tempFile = self::$tempDir.DS."ptconfigure-temp-script-".mt_rand(100, 999999999).".sh";
//        $logging->log("Creating $tempFile");
        $fileVar = "";
        $multiLineCommand = self::multilineToArray($multiLineCommand) ;
        foreach ($multiLineCommand as $command) { $fileVar .= $command."\n" ; }
        file_put_contents($tempFile, $fileVar) ;
        return $tempFile ;
    }

    protected function executeAndOutput($command, $message=null) {
        $outputText = shell_exec($command);
        if ($message !== null) {
            $outputText .= "$message\n"; }
        print $outputText;
        return $outputText;
    }

    protected function executeAndLoad($command) {
        $outputText = shell_exec($command);
        return $outputText;
    }

    public static function executeAndGetReturnCode($command, $show_output = true, $get_output = null) {
        $tempFile = self::tempfileStaticFromCommand($command) ;
        $loggingFactory = new \Model\Logging();
        $params["echo-log"] = true ;
        $logging = $loggingFactory->getModel($params);
        if (!is_executable($tempFile)) {
            // @todo this wont work on windows
            shell_exec("chmod 755 $tempFile 2>/dev/null");
            shell_exec("chmod +x $tempFile 2>/dev/null"); }

        $proc = proc_open($command, array(
            0 => array("pipe","r"),
            1 => array("pipe",'w'),
            2 => array("pipe",'w'),
        ),$pipes);
        if ($show_output==true) {
            stream_set_blocking($pipes[1], true);
            stream_set_blocking($pipes[2], true);
            $data = "";
            while ( ($buf = fread($pipes[1], 32768)) || ( $buf2 = fread($pipes[2], 32768))) {
                if (isset($buf) && $buf !== false) {
                    $data .= $buf;
                    echo $buf ; }
                if ( (isset($buf2) && $buf2 !== false) || $buf2 = fread($pipes[2], 32768) ) {
//                    $buf2 = "ERR: ".$buf2;
                    $data .= "ERR: ".$buf2;
                    echo "ERR: ".$buf2 ;
                    unset($buf2) ;} } }

        $status = proc_get_status($proc);
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]) ;
        // @note geting status s necessary as sometimes this doesn't return an exit code
        // http://php.net/manual/en/function.proc-close.php
        // morrisdavid gmail comment
        $retVal = proc_close($proc);
        $retVal = ($status["running"] ? $retVal : $status["exitcode"] );
        $output = (isset($stderr)) ? $stdout.$stderr : $stdout ;
        $output = explode("\n", $output) ;
        if ($show_output == true) {
//            $stdout = explode("\n", $stdout) ;
//            foreach ($stdout as $stdoutline) {
//                echo $stdoutline."\n" ; }
            if (strlen($stderr)>0) {
//                echo "ERRORS:\n";
                $stderr = explode("\n", $stderr) ;
                foreach ($stderr as $stderrline) {
//                    echo $stderrline."\n" ;
                } }
            return array("rc"=>$retVal, "output"=>$output) ; }
        if ($get_output == true) {
            return array("rc"=>$retVal, "output"=>$output) ;}
        else {
            return $retVal; }
    }

    protected function setCmdLineParams($params) {
        $cmdParams = array();
//        if (!is_array($params)) { var_dump($params) ; debug_print_backtrace() ; }
        foreach ($params as $paramKey => $paramValue) {
//            var_dump($paramValue);
            if (is_array($paramValue)) {
                // if the value is a php array, the param must be already formatted so do nothing
            }
            else if ($paramValue=="-y") {
                $paramKey = "yes" ;
                $paramValue = true ; }
            else if ($paramValue=="-g") {
                $paramKey = "guess" ;
                $paramValue = true ; }
            else if ($paramValue=="-yg" || $paramValue=="-gy") {
                $cmdParams = array_merge($cmdParams, array("yes" => true));
                $paramKey = "guess" ;
                $paramValue = "true" ; }
            else if (substr($paramValue, 0, 2)=="--" && strpos($paramValue, '=') != null ) {
                $equalsPos = strpos($paramValue, "=") ;
                $paramKey = substr($paramValue, 2, $equalsPos-2) ;
                $paramValue = substr($paramValue, $equalsPos+1, strlen($paramValue)) ; }
            else if (substr($paramValue, 0, 2)=="--" && strpos($paramValue, '=') == false ) {
                $paramKey = substr($paramValue, 2) ;
                $paramValue = true ; }
            $cmdParams = array_merge($cmdParams, array($paramKey => $paramValue)); }
        $this->params = (is_array($this->params)) ? array_merge($this->params, $cmdParams) : $cmdParams;
        $this->transformAllParameters() ;
    }

    protected function transformAllParameters() {
        foreach ($this->params as $key => $val) {
            $this->params[$key] = $this->transformParameterValue($val) ; }
    }

    protected function transformParameterValue($paramValue) {
        $origParamValue = $paramValue ;
        $paramValue = str_replace("::~::", "::Default::", $paramValue) ;
        $paramValue = trim($paramValue, ' ') ;
//        var_dump("vd", $paramValue) ;
        $paramValue = trim($paramValue, "\n") ;
        $paramValue = trim($paramValue, '"') ;
//        var_dump("vd2", $paramValue) ;
        $paramValue = rtrim($paramValue) ;
        $paramValue = ltrim($paramValue) ;
        $trimmedParamValue = $paramValue ;
        if (substr($paramValue, 0, 4) == "::::") {
            $parts_string = substr($paramValue, 4) ;
            $parts_array = explode("::", $parts_string) ;
            $module = $parts_array[0] ;
            if ($module==$this->getModuleName()) { return $paramValue ; }
            $res = $this->loadFromMethod($parts_string,0, 0) ;
            return $res ; }
        if ( (strpos($paramValue, '{{{') !== false) && (strpos($paramValue, '}}}') !== false) ) {
            $sc = substr_count($paramValue, '{{{') ;
            for ($i=1 ; $i<=$sc; $i++) {
                $or_st = strpos($paramValue, '{{{') ;
                if ($or_st === false) {
                    return $paramValue ; }
                $or_end = strpos($paramValue, '}}}', $or_st) ;
                $or_diff = ($or_end - $or_st) + 3 ;
                $parts_string = substr($paramValue, $or_st, $or_diff) ;
                if (strpos($paramValue, '}}}')) {
                    $parts_string = substr($parts_string, 0, strpos($parts_string, '}}}')) ;
                    $parts_string = str_replace("{{{::::", "", $parts_string) ; }
                $parts_array = explode("::", $parts_string) ;
                $module = $parts_array[0] ;
                if (in_array($module, array("Parameter", "Param", "param", "parameter"))) {
                    $res = $this->loadFromParameter($parts_array) ;
                    $paramValue = $this->swapResForVariable($res, $paramValue, $parts_string);
                    return $paramValue ; }
                if ($module==$this->getModuleName()) { return $paramValue ; }
                $res = $this->loadFromMethod($parts_string, $i, $sc) ;
                $paramValue = $this->swapResForVariable($res, $paramValue, $parts_string) ; } }
        return $paramValue;
    }

    protected function swapResForVariable($res, $paramValue, $parts_string) {
        $orig = '{{{::::'.$parts_string.'}}}' ;
//        if (is_array($res)) { var_dump("res is", $res) ; die("\n\nres\n\n"); }
//        if (is_array($orig)) { var_dump("orig is", $orig) ; die("\n\norig\n\n"); }
//        if (is_array($paramValue)) { var_dump("pv is", $paramValue) ; die("\n\nparamValue\n\n"); }
        $paramValue = str_replace($orig, $res, $paramValue);
        return $paramValue ;
    }

    protected function loadFromMethod(&$parts_string, $i, $sc) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel(array());

        $is_reg = \Model\RegistryStore::getValue($parts_string) ;
        if (!is_null($is_reg)) { return $is_reg ; }
        $parts_array = explode("::", $parts_string) ;
        $module = $parts_array[0] ;
        $modelGroup = $parts_array[1] ;
        $method = $parts_array[2] ;
        if (!isset($parts_array[1])) {

            var_dump("pray:", $parts_array, $parts_string, "myi:", $i, "mysc:", $sc) ; }


        $method_params = (isset($parts_array[3])) ? $parts_array[3] : array() ;
        $full_factory = "\\Model\\{$module}" ;
        if (!class_exists($full_factory)) {
//            $logging->log(
//                "Parameter transform unable to find class $method in $module, $modelGroup model group",
//                $this->getModuleName(), LOG_FAILURE_EXIT_CODE) ;
            return null ; }
        $foundFactory = new $full_factory();
        $madeModel = $foundFactory->getModel($this->params, $modelGroup);
        if (method_exists($madeModel, $method)) {
//            $logging->log("Parameter transform loading value from method $method in $module, $modelGroup model group", $this->getModuleName()) ;
            $res = call_user_func_array(array($madeModel, $method), $method_params) ; }
        else {
            $logging->log(
                "Parameter transform unable to find method $method in $module, $modelGroup model group",
                $this->getModuleName(), LOG_FAILURE_EXIT_CODE) ;
            $res = null ; }
        \Model\RegistryStore::setValue($parts_string, $res) ;
        return $res ;

    }

    protected function loadFromParameter($parts_array) {
        $param_requested = $parts_array[1] ;
        return $this->params[$param_requested] ;
    }

    protected function askYesOrNo($question) {
        print "$question (Y/N) \n";
        $fp = fopen('php://stdin', 'r');
        $last_line = false;
        while (!$last_line) {
            $inputChar = fgetc($fp);
            $yesOrNo = ($inputChar=="y"||$inputChar=="Y") ? true : false;
            $last_line = true; }
        return $yesOrNo;
    }

    protected function areYouSure($question) {
        print "!! Sure? $question (Y/N) !!\n";
        $fp = fopen('php://stdin', 'r');
        $last_line = false;
        while (!$last_line) {
            $inputChar = fgetc($fp);
            $yesOrNo = ($inputChar=="y"||$inputChar=="Y") ? true : false;
            $last_line = true; }
        return $yesOrNo;
    }

    protected function askForDigit($question) {
        $fp = fopen('php://stdin', 'r');
        $last_line = false;
        $i = 0;
        while ($last_line == false ) {
            print "$question\n";
            $inputChar = fgetc($fp);
            if (in_array($inputChar, array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9")) ) { $last_line = true; }
            else { echo "You must enter a single digit. Please try again\n"; continue; }
            $i++; }
        return $inputChar;
    }

    protected function askForInput($question, $required=null) {
        $fp = fopen('php://stdin', 'r');
        $last_line = false;
        while (!$last_line) {
            print "$question\n";
            $inputLine = fgets($fp, 1024);
            if ($required && strlen($inputLine)==0 ) {
                print "You must enter a value. Please try again.\n"; }
            else {$last_line = true;} }
        $inputLine = $this->stripNewLines($inputLine);
        return $inputLine;
    }

    protected function askForArrayOption($question, $options, $required=null) {
        $fp = fopen('php://stdin', 'r');
        $last_line = false;
        while ($last_line == false) {
            print "$question\n";
            for ( $i=0 ; $i<count($options) ; $i++) { print "($i) $options[$i] \n"; }
            $inputLine = fgets($fp, 1024);
            if ($required && strlen($inputLine)==0 ) { print "You must enter a value. Please try again.\n"; }
            elseif ( is_int($inputLine) && ($inputLine>=0) && ($inputLine<=count($options) ) ) {
                print "Enter one of the given options. Please try again.\n"; }
            else {$last_line = true; } }
        $inputLine = $this->stripNewLines($inputLine);
        return (isset($options[$inputLine])) ? $options[$inputLine] : null ;
    }

    protected function stripNewLines($inputLine) {
        $inputLine = str_replace("\n", "", $inputLine);
        $inputLine = str_replace("\r", "", $inputLine);
        return $inputLine;
    }

    protected function findStatusByDirectory($inputLine) {
        $inputLine = str_replace("\n", "", $inputLine);
        $inputLine = str_replace("\r", "", $inputLine);
        return $inputLine;
    }

    protected function setInstallFlagStatus($bool) {
        if ($bool) {
            AppConfig::setProjectVariable("installed-modules", $this->getModuleName(), true); }
        else {
            AppConfig::deleteProjectVariable("installed-modules", "any", $this->programNameMachine); }
    }

    public function askStatus() {
        // @todo also use install flag status from methods setInstallFlagStatus getInstallFlagStatus
        if (isset($this->statusCommand) && !is_null($this->statusCommand) &&
            isset($this->statusCommandExpects) && !is_null($this->statusCommandExpects)) {
            $status = ($this->executeAndLoad("$this->statusCommand &") == $this->statusCommandExpects) ? true : false ; }
        else if (isset($this->statusCommand) && !is_null($this->statusCommand)) {
            $res = $this->executeAndGetReturnCode($this->statusCommand, false, true) ;
            $status = ($res["rc"] == 0) ? true : false ; }
        else {
            $status = ($this->executeAndGetReturnCode("{$this->defaultStatusCommandPrefix} {$this->programNameMachine}") == 0) ? true : false ; }
        $inst = ($status == true) ? "Installed" : "Not Installed " ;
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        $logging->log("Module ".$this->getModuleName()." reports itself as {$inst}", $this->getModuleName()) ;
        return $status ;
    }

    protected function askStatusByArray($commsToCheck) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        $passing = true ;
        foreach ($commsToCheck as $commToCheck) {
            $outs = $this->executeAndLoad("command -v $commToCheck") ;
            if ( !strstr($outs, $commToCheck) ) {
                $logging->log("No command '{$commToCheck}' found") ;
                $passing = false ; }
            else {
                $logging->log("Command '{$commToCheck}' found") ; } }
        return $passing ;
    }

    // @todo fix this to use the model factory
    protected function getInstallFlagStatus($programNameMachine) {
        $installedApps = AppConfig::getProjectVariable("installed-modules");
        if (is_array($installedApps) && in_array($programNameMachine, $installedApps)) {
            return true ; }
        return false ;
    }

    protected function swapCommandArrayPlaceHolders(&$commandArray) {
        $this->swapCommandDirs($commandArray);
        $this->swapInstallUserDetails($commandArray);
    }

    protected function swapCommandDirs(&$commandArray) {
        if (is_array($commandArray) && count($commandArray)>0) {
            foreach ($commandArray as &$comm) {
                $comm = str_replace("****PROGDIR****", $this->programDataFolder, $comm);
                $comm = str_replace("****PROG EXECUTOR****", $this->programExecutorTargetPath, $comm); } }
    }

    protected function swapInstallUserDetails(&$commandArray) {
        if (is_array($commandArray) && count($commandArray)>0) {
            foreach ($commandArray as &$comm) {
                $comm = str_replace("****INSTALL USER NAME****", $this->installUserName,
                    $comm);
                $comm = str_replace("****INSTALL USER HOME DIR****",
                    $this->installUserHomeDir, $comm); } }
    }

    public function askAction($action) {
        return $this->askWhetherToDoAction($action);
    }

    public function ensureTrailingSlash($str) {
        if (substr($str, -1, 1) != DIRECTORY_SEPARATOR) { $str .= DIRECTORY_SEPARATOR ; }
        return $str ;
    }

    protected function askWhetherToPerformActionToScreen($action){
        $question = "Perform ".$this->programNameInstaller." $action?";
        return self::askYesOrNo($question);
    }

    protected function performAction($action) {
        $doAction = (isset($this->params["yes"]) && $this->params["yes"]==true) ?
            true : $this->askWhetherToPerformActionToScreen($action);
        if (!$doAction) { return false; }
        return $this->installAction($action);
    }

    protected function askWhetherToDoAction($action) {
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        if ( isset($this->actionsToMethods)) {
            if (isset($this->actionsToMethods[$action]) && method_exists($this, $this->actionsToMethods[$action])) {
                $return = $this->{$this->actionsToMethods[$action]}() ;
                return $return ; }
            else {
                $logging->log("No method {$this->actionsToMethods[$action]} in model ".get_class($this)) ;
                \Core\BootStrap::setExitCode(1);
                return false; } }
        else {
            $logging->log('No property $actionsToMethods in model '.get_class($this)) ;
            \Core\BootStrap::setExitCode(1);
            return false; }
    }

    public function getModuleName() {
        $reflector = new \ReflectionClass(get_class($this));
        $fileName = $reflector->getFileName();
        $end = strpos($fileName, DIRECTORY_SEPARATOR.'Model'.DIRECTORY_SEPARATOR) ;
        $beforeModel = substr($fileName, 0, $end) ;
        $start = strrpos($beforeModel, DIRECTORY_SEPARATOR) ;
        $moduleName = substr($beforeModel, $start+1) ;
        return $moduleName ;
    }

    //@todo maybe this should be a helper
    public function packageAdd($packager, $package, $version = null, $versionOperator = "+") {
        $packageFactory = new PackageManager();
        $packageManager = $packageFactory->getModel($this->params) ;
        return $packageManager->performPackageEnsure($packager, $package, $this, $version, $versionOperator);
    }

    //@todo maybe this should be a helper
    public function packageRemove($packager, $package) {
        $packageFactory = new PackageManager();
        $packageManager = $packageFactory->getModel($this->params) ;
        return $packageManager->performPackageRemove($packager, $package, $this);
    }

    /*Versioning starts here*/
    public function getVersion($type = "Installed") {
        $vt = array("Installed", "installed", "Recommended", "recommended", "Latest", "latest");
        if (isset($this->params["version-type"]) && in_array($this->params["version-type"], $vt) ) {
            $type = $this->params["version-type"] ; }
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        if (in_array($type, array("Installed", "installed"))) {
            if ($this->askStatus() != true) {
                \Core\BootStrap::setExitCode(1) ;
                $logging->log("This program is not installed, so cannot find installed version") ;
                return false; }
            $type = ucfirst($type) ;
            $property = "version{$type}Command" ;
            $trimmer = "{$property}Trimmer" ;
            if (isset($this->$property) && method_exists($this, $trimmer)) {
                $out = $this->executeAndLoad($this->$property);
                return new \Model\SoftwareVersion($this->$trimmer($out)) ; }
            else if (isset($this->$property)) {
                $versionText = $this->executeAndLoad($this->$property);
                $versionObject = new \Model\SoftwareVersion($versionText) ;
                return $versionObject ; }
            else {
                $logging->log("Cannot find version") ;
                return false; } }
        else if (in_array($type, array("Recommended", "recommended", "Latest", "latest"))) {
            $type = ucfirst($type) ;
            $property = "version{$type}Command" ;
            $trimmer = "{$property}Trimmer" ;
            if (isset($this->$property) && method_exists($this, $trimmer)) {
                $out = $this->executeAndLoad($this->$property);
                return new \Model\SoftwareVersion($this->$trimmer($out)) ; }
            else if (isset($this->$property)) {
                $versionText = $this->executeAndLoad($this->$property);
                $versionObject = new \Model\SoftwareVersion($versionText) ;
                return $versionObject ; }
            else {
                $logging->log("Cannot find version") ;
                return false; } }
        else {
            return false; }
    }

    public function getVersionsAvailable() {
        if (isset($this->versionsAvailable)) {
            return $this->versionsAvailable ; }
        else if (method_exists($this, "versionsAvailable")) {
            return $this->versionsAvailable() ; }
        else {
            return false; }
    }

    /**
     * Find the position of the Xth occurrence of a substring in a string
     * @param $haystack
     * @param $needle
     * @param $number integer > 0
     * @return int
     */
    protected function strposX($haystack, $needle, $number){
        if($number == '1'){
            return strpos($haystack, $needle);
        }elseif($number > '1'){
            return strpos($haystack, $needle, $this->strposX($haystack, $needle, $number - 1) + strlen($needle));
        }else{
            return false;
        }
    }

}
