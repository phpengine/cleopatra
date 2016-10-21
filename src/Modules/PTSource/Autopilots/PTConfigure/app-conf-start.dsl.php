Logging log
  log-message "Lets configure for Pharaoh Source"

Mkdir path
  label "Ensure the Repositories Directory exists"
  path "{{{ Facts::Runtime::factGetConstant::REPODIR }}}"
  recursive

Chmod path
  label "Ensure the Repositories Directory is writable"
  path "{{{ Facts::Runtime::factGetConstant::REPODIR }}}"
  recursive
  mode 0755

PackageManager pkg-install
  label "Install apache Mod Auth External"
  package-name libapache2-mod-authnz-external
  packager Apt

RunCommand install
  label "Enable apache Mod Auth External"
  guess
  command "a2enmod authnz_external"

Copy put
  label "{{{ Parameter::app-slug }}} Apache Custom Authentication method Conf file"
  source "{{{ Facts::Runtime::factGetConstant::PFILESDIR }}}ptconfigure/ptconfigure/src/Modules/PTSource/Templates/{{{ Parameter::app-slug }}}_auth.conf"
  target "/etc/apache2/conf-available/{{{ Parameter::app-slug }}}_auth.conf"

RunCommand install
  label "Enable apache Mod Auth External"
  guess
  command "a2enconf {{{ Parameter::app-slug }}}_auth"

Logging log
  log-message "Configuration Management for Pharaoh Source Complete"