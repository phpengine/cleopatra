<?php

Namespace Model;

class JavaUbuntu64 extends BaseLinuxApp {

    // Compatibility
    public $os = array("Linux") ;
    public $linuxType = array("Debian") ;
    public $distros = array("Ubuntu") ;
    public $versions = array("11.04", "11.10", "12.04", "12.10", "13.04") ;
    public $architectures = array("64") ;

    // Model Group
    public $modelGroup = array("Default") ;

    protected $javaDetails ;

    public function __construct($params) {
        parent::__construct($params);
        $this->autopilotDefiner = "Java";
        $this->installCommands = array(
            array("method"=> array("object" => $this, "method" => "askForJavaInstallVersion", "params" => array()) ),
            array("method"=> array("object" => $this, "method" => "askForJavaInstallDirectory", "params" => array()) ),
            array("method"=> array("object" => $this, "method" => "runJavaInstall", "params" => array()) ),
        );

        //@todo uninstall commands of java
        $this->uninstallCommands = array(
            array("method"=> array("object" => $this, "method" => "askForJavaInstallDirectory", "params" => array()) ),);
        $this->programNameMachine = "java"; // command and app dir name
        $this->programNameFriendly = "!!Java JDK!!"; // 12 chars
        $this->programNameInstaller = "The Oracle Java JDK";
        $this->statusCommand = 'java -version' ;
        $this->versionInstalledCommand = 'java -version 2>&1' ;
        $this->versionRecommendedCommand = SUDOPREFIX."apt-cache policy java" ;
        $this->versionLatestCommand = SUDOPREFIX."apt-cache policy java" ;
        $this->initialize();
    }

    protected function askForJavaInstallVersion() {
        if (isset($this->params["version"])) {
            $this->params["java-install-version"] = $this->params["version"] ;
            $this->javaDetails = $this->getJavaDetails($this->params["version"]);
            $this->programDataFolder = "/var/lib/jvm/jdk".$this->params["version"] ;
            return ;  }
        else if (isset($this->params["java-install-version"])) {
            $this->javaDetails = $this->getJavaDetails($this->params["java-install-version"]); }
        else if (isset($this->params["guess"]) && $this->params["guess"]==true) {
            $this->params["java-install-version"] = "1.8" ;
            $this->javaDetails = $this->getJavaDetails("1.8") ; }
        else {
            $question = "Enter Java Install Version (1.7 or 1.8):";
            $jd = self::askForInput($question, true);
            $this->params["java-install-version"] = $jd ;
            $this->javaDetails = $this->getJavaDetails($jd); }
        $this->programDataFolder = "/var/lib/jvm/jdk".$this->params["java-install-version"];
    }

    protected function runJavaInstall() {
        $is_java_installed_command = "bash -c '. /etc/profile ; java -version;' 2>&1" ;
        $is_java_installed_out = $this->executeAndLoad($is_java_installed_command) ;
        $str_to_find = 'java version' ;
        if (substr_count($is_java_installed_out, $str_to_find) == 1 ) {
            $is_java_installed = true ;
        } else {
            $is_java_installed = false ;
        }

        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);

        if ($is_java_installed === true) {
            $str_two_to_find = 'build '.$this->javaDetails['version_short'] ;
            if (substr_count($is_java_installed_out, $str_two_to_find) == 1 ) {
                $requested_version_is_installed = true ;
            } else {
                $msg =
                    "A Different Java JDK Version than the requested {$this->javaDetails['version_short']} has been found." ;
                $logging->log($msg, $this->getModuleName()) ;
                $requested_version_is_installed = false ;
            }
        } else {
            $msg =
                "No Java JDK installation has been found." ;
            $logging->log($msg, $this->getModuleName()) ;
            $requested_version_is_installed = false ;
        }
        $force_param_is_set = (isset($this->params["force"]) && $this->params["force"] != false ) ;
        if ($requested_version_is_installed && !$force_param_is_set) {
            $msg =
                "Requested Java JDK Version {$this->javaDetails['version_short']} is already installed." .
                " Use force parameter to install anyway." ;
            $logging->log($msg, $this->getModuleName()) ;
            $ray = array( ) ;

        } else {

            if ($force_param_is_set && $is_java_installed != "") {
                $msg = "Found $is_java_installed version already installed, though installing anyway as force param is set." ;
                $logging->log($msg, $this->getModuleName()) ;
            }

            $stamp = time() ;
            $tmp_java = "/tmp/oraclejdk{$stamp}.tar.gz" ;
            if (!file_exists($tmp_java)) {
                $this->packageDownload($this->javaDetails['jdk_url'], $tmp_java) ;
//                $msg = "Copying from opt to tmp" ;
//                $logging->log($msg, $this->getModuleName()) ;
//                copy('/opt/jdk1.8x64.tar.gz', $tmp_java) ;
            }

            $tmp_str = "/tmp/oraclejdk{$stamp}" ;

            mkdir($tmp_str, 0775) ;

            // decompress from gz
            $p = new \PharData($tmp_str.'.tar.gz');
            $p->decompress(); // creates /path/to/my.tar

            // unarchive from the tar
            $phar = new \PharData($tmp_str.'.tar');
            $phar->extractTo($tmp_str, null, true);

            unlink($tmp_str.'.tar.gz') ;

            if (!is_dir($this->programDataFolder)) {
                mkdir($this->programDataFolder, 0775, true) ;
            }

            $comm = "rm -rf {$this->programDataFolder}" ;
            $this->executeAndOutput($comm) ;

            // MAKE IT RECURSIVE
            $comm = 'cp -r '.$tmp_str.DIRECTORY_SEPARATOR."{$this->javaDetails['extracted_dir']} {$this->programDataFolder}" ;
            $this->executeAndOutput($comm) ;

            chmod($tmp_str, octdec('0711') ) ;

            $profile_lines = array(
                'echo \'JAVA_HOME='.$this->programDataFolder.'\' >> /etc/profile',
                'echo \'PATH=$PATH:$HOME/bin:$JAVA_HOME/bin\' >> /etc/profile',
                'echo \'export JAVA_HOME\' >> /etc/profile',
                'echo \'export PATH\' >> /etc/profile',
            ) ;

            foreach ($profile_lines as $profile_line) {
                $this->executeAndOutput($profile_line) ;
            }

            $j_opts = array('java', 'javac', 'javaws') ;
            foreach ($j_opts as $j_opt) {
                $comm = SUDOPREFIX.'update-alternatives --install "/usr/bin/'.$j_opt.'" "'.$j_opt.'" "'.$this->programDataFolder.'/bin/'.$j_opt.'" 1 ' ;
                $this->executeAndOutput($comm) ;
            }

            foreach ($j_opts as $j_opt) {
                $comm = SUDOPREFIX.'update-alternatives --set '.$j_opt.' '.$this->programDataFolder.'/bin/'.$j_opt.' ' ;
                $this->executeAndOutput($comm) ;
            }

        }
//        $this->installCommands = $ray ;
//        return $this->doInstallCommand() ;
        return true ;
    }

    public function fsmodify($obj) {
        $chunks = explode(DIRECTORY_SEPARATOR, $obj);
        chmod($obj, is_dir($obj) ? 0755 : 0644);
        chown($obj, $chunks[2]);
        chgrp($obj, $chunks[2]);
    }


    public function fsmodifyr($dir) {
        if($objs = glob($dir.DIRECTORY_SEPARATOR."*")) {
            foreach($objs as $obj) {
                $this->fsmodify($obj);
                if(is_dir($obj)) $this->fsmodifyr($obj);
            }
        }
        return $this->fsmodify($dir);
    }


    public function packageDownload($remote_source, $temp_exe_file) {
        if (file_exists($temp_exe_file)) {
            unlink($temp_exe_file) ;
        }
        # var_dump('BWA packageDownload 2', $_ENV, $temp_exe_file) ;
        $loggingFactory = new \Model\Logging();
        $logging = $loggingFactory->getModel($this->params);
        $logging->log("Downloading From {$remote_source}", $this->getModuleName() ) ;

        echo "Download Starting ...".PHP_EOL;
        ob_start();
        ob_flush();
        flush();

        $fp = fopen ($temp_exe_file, 'w') ;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $remote_source);
        // curl_setopt($ch, CURLOPT_BUFFERSIZE,128);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, array($this, 'progress'));
        curl_setopt($ch, CURLOPT_NOPROGRESS, false); // needed to make progress function work
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($ch);
        # $error = curl_error($ch) ;
        # var_dump('downloaded', $downloaded, $error) ;
        curl_close($ch);

        ob_flush();
        flush();

        echo "Done".PHP_EOL ;
        return $temp_exe_file ;
    }

    public function progress($resource, $download_size, $downloaded, $upload_size, $uploaded) {
        $is_quiet = (isset($this->params['quiet']) && ($this->params['quiet'] == true) ) ;
        if ($is_quiet == false) {
            if($download_size > 0) {
                $dl = ($downloaded / $download_size)  * 100 ;
                # var_dump('downloaded', $dl) ;
                $perc = round($dl, 2) ;
                # var_dump('perc', $perc) ;
                echo "{$perc} % \r" ;
            }
            ob_flush();
            flush();
        }
    }

    public function getJavaDetails($version) {
        if ($version == "1.8") {
            $details['jdk_url'] = "https://repositories.internal.pharaohtools.com/index.php?control=BinaryServer&action=serve&item=java_jdk" ;
            $details['path_in_repo'] = "phpengine-cleo-jdk-64-6c383e2868bd/jdk-7u60-linux-x64.tar.gz" ;
            $details['fname_in_repo'] = "jdk-7u60-linux-x64.tar.gz" ;
            $details['version_short'] = "1.8.0" ;
            $details['extracted_dir'] = "jdk{$details['version_short']}_211" ;
        } else {
            $details['jdk_url'] = "http://46f95a86014936ec1625-77a12a9c8b6f69dd83500dbd082befcc.r16.cf3.rackcdn.com/jdk1.7.tar.gz" ;
            $details['path_in_repo'] = "jdk-7u60-linux-x64.tar.gz" ;
            $details['fname_in_repo'] = "jdk-7u60-linux-x64.tar.gz" ;
            $details['version_short'] = "1.7.0" ;
            $details['extracted_dir'] = "jdk{$details['version_short']}_60" ;
        }
        return $details ;
    }

    protected function askForJavaInstallDirectory() {
        if (isset($this->params["java-install-dir"])) {
            $this->programDataFolder = $this->params["java-install-dir"]; }
        else if (isset($this->params["guess"]) && $this->params["guess"]==true) {
            return; }
        else {
            $question = "Enter Java Install Directory (no trailing slash):";
            $this->programDataFolder = self::askForArrayOption($question, array("1.7", "1.8"), true); }
    }

    public function versionInstalledCommandTrimmer($text) {
        $leftQuote = strpos($text, 'java version "') + 14 ;
        $rightQuote = strpos($text, '"', $leftQuote) ;
        $difference = $rightQuote - $leftQuote ;
        $done = substr($text, $leftQuote, $difference) ;
        return $done ;
    }

    public function versionLatestCommandTrimmer($text) {
        $done = substr($text, 53, 17) ;
        return $done ;
    }

    public function versionRecommendedCommandTrimmer($text) {
        $done = substr($text, 53, 17) ;
        return $done ;
    }

}
