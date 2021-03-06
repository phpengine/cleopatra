<?php

Namespace Info;

class CleofyInfo extends PTConfigureBase {

    public $hidden = false;

    public $name = "PTConfigure Cleofyer - Creates default autopilots for your project";

    public function __construct() {
        parent::__construct();
    }

    public function routesAvailable() {
        return array( "Cleofy" =>  array_merge(parent::routesAvailable(), array("standard", "db-cluster", "workstation",
            "install-generic-autopilots", "gen", "tiny", "medium", "medium-web", "empty") ) );
    }

    public function routeAliases() {
        return array("cleofy"=>"Cleofy");
    }

    public function helpDefinition() {
      $help = <<<"HELPDATA"
  This module is part of a default Module Core and provides you with a method by which you can
  create a standard set of Autopilot files for your project from the command line.


  Cleofy, cleofy

        - list
        List all of the autopilot files in your build/config/ptconfigure/autopilots
        example: ptconfigure cleofy list

        - install-generic-autopilots, gen
        Install the generic Cleofy autopilot templates for a Tiny or Medium (Current Default) set of Environments
        example: ptconfigure cleofy install-generic-autopilots
        example: ptconfigure cleofy install-generic-autopilots
                    --yes
                    --guess # will set --destination-dir=*this dir +*build/config/ptconfigure/cleofy/autopilots/
                    --template-group=tiny # tiny, medium, dbcluster, ptvirtualize || db-cluster, workstation
                    --destination-dir=*path-to-destination*

HELPDATA;
      return $help ;
    }

}

/*
 *
        - standard
        Create a default set of ptconfigure autopilots in build/config/ptconfigure/autopilots for
        your project.
        example: ptconfigure cleofy standard

        - tiny
        Create a default set of ptconfigure autopilots in build/config/ptconfigure/autopilots for
        a project with a "tiny" style infrastructure.
        example: ptconfigure cleofy tiny

        - medium
        Create a default set of ptconfigure autopilots in build/config/ptconfigure/autopilots for
        a project with a "medium" style infrastructure.
        example: ptconfigure cleofy medium

        - medium-web
        Create a default set of ptconfigure autopilots in build/config/ptconfigure/autopilots for
        a project with a "medium" style infrastructure, with web but not database.
        example: ptconfigure cleofy medium-web

        - db-cluster
        Create a default set of ptconfigure autopilots in build/config/ptconfigure/autopilots for
        your project.
        example: ptconfigure cleofy db-cluster
                    --yes
                    --guess
                    --database-nodes-env=*db-nodes-environment-name*
 */