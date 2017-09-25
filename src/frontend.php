<?php
namespace codename\core;
use \codename\core\app;

/**
 * Here we have some frontend helpers
 * @package core
 * @since 2016-02-11
 */
class frontend {

    /**
     * There are no groups for navigation buttons available at all
     * @var string
     */
    CONST EXCEPTION_GETGROUP_NOGROUPSAVAILABLE = 'EXCEPTION_GETGROUP_NOGROUPSAVAILABLE';

    /**
     * The desired navigation group cannot be found.
     * @var string
     */
    CONST EXCEPTION_GETGROUP_GROUPNOTFOUND = 'EXCEPTION_GETGROUP_GROUPNOTFOUND';

    /**
     * Returns navigation config nested in an object of type \codename\core\config
     * @return \codename\core\config
     */
    protected function getNavigation() : \codename\core\config {
        return new \codename\core\config\json('/config/navigation.json');
    }

    /**
     * Returns the navigation HTML code for the application
     * @return string
     */
    public function outputNavigation(string $key) : string {
        $output = '';
        $config = $this->getNavigation();
        if(!$config->exists($key)) {
            return $output;
        }

        foreach($config->get($key) as $element) {
            if($element['type'] == 'group') {
                $output .= $this->parseGroup($element);
                continue;
            } else if($element['type'] == 'iframe') {
              $output .= $this->parseIframe($element);
              continue;
            }
            $output .= $this->parseLink($element);
        }

        return $output;
    }

    /**
     * Parses a navigation group
     * @param array $group
     * @return string
     */
    protected function parseGroup(array $group) : string {
        return app::parseFile(app::getInheritedPath('frontend/template/' . app::getRequest()->getData('template') . '/mainnavi/group.php'), $group);
    }

    /**
     * Parses a single link
     * @param array $link
     * @return string
     */
    protected function parseLink(array $link) : string {
        return app::parseFile(app::getInheritedPath('frontend/template/' . app::getRequest()->getData('template') . '/mainnavi/link.php'), $link);
    }

    /**
     * Parses a dropdown containing an iframe
     * @param array $link
     * @return string
     */
    protected function parseIframe(array $action) : string {
        return app::parseFile(app::getInheritedPath('frontend/template/' . app::getRequest()->getData('template') . '/mainnavi/iframe.php'), $action);
    }

    /**
     * Returns a complete configuration
     * @param string $groupname
     * @throws \codename\core\exception
     * @return string
     */
    public function getGroup(string $groupname) : string {
        $data = $this->getNavigation();

        if(!$data->exists("group")) {
            throw new \codename\core\exception(self::EXCEPTION_GETGROUP_NOGROUPSAVAILABLE, \codename\core\exception::$ERRORLEVEL_ERROR, null);
        }

        if(!$data->exists("group>{$groupname}")) {
            throw new \codename\core\exception(self::EXCEPTION_GETGROUP_GROUPNOTFOUND, \codename\core\exception::$ERRORLEVEL_ERROR, $groupname);
        }

        return app::parseFile(app::getInheritedPath('frontend/template/' . app::getRequest()->getData('template') . '/groupnavi/group.php'), $data->get("group>{$groupname}"));
    }

}
