<?php class popup_helper
{
    protected $popupCookie;

    public function __construct()
    {
        $this->popupCookie = false;
        if (!$this->getPopupCookie('magentoSessionId') && isset($_COOKIE['frontend'])) {
            $this->setPopupCookie('magentoSessionId', $_COOKIE['frontend']);
        }
    }

    public function getIsCrawler()
    {
        $http_user_agent = $_SERVER['HTTP_USER_AGENT'];
        $petten = 'robot|spider|crawler|curl|Bloglines subscriber|Dumbot|Sosoimagespider|QihooBot|FAST-WebCrawler|Superdownloads Spiderman|LinkWalker|msnbot|ASPSeek|WebAlta Crawler|Lycos|FeedFetcher-Google|Yahoo|YoudaoBot|AdsBot-Google|Googlebot|Scooter|Gigabot|Charlotte|eStyle|AcioRobot|GeonaBot|msnbot-media|Baidu|CocoCrawler|Google|Charlotte t|Yahoo! Slurp China|Sogou web spider|YodaoBot|MSRBOT|AbachoBOT|Sogou head spider|AltaVista|IDBot|Sosospider|Yahoo! Slurp|Java VM|DotBot|LiteFinder|Yeti|Rambler|Scrubby|Baiduspider|accoona';
        $isCrawler = (preg_match("/$petten/i", $http_user_agent) > 0);
        return $isCrawler;
    }

    public function getParam($name)
    {
        if (isset($_GET[$name])) {
            return $_GET[$name];
        } elseif (isset($_POST[$name])) {
            return $_POST[$name];
        } else {
            return false;
        }
    }

    function getPopupCookie($name, $not_check_expire = false)
    {
        if ($this->popupCookie) {
            $cookie = $this->popupCookie;
        } else {
            $cookie = isset($_COOKIE['popupData']) ? $_COOKIE['popupData'] : '';
            $this->popupCookie = $cookie;
        }
        $name = explode($name . ":", $cookie);
        if (!isset($name[1])) {
            if ($name == 'lastSession' && isset($_COOKIE['lastPopupSession'])) {
                $_cookie = $_COOKIE['lastPopupSession'];
            } elseif ($name == 'lastRandId' && isset($_COOKIE['lastRandId'])) {
                $_cookie = $_COOKIE['lastRandId'];
            } else {
                return false;
            }
        } else {
            $_cookie = explode("|", $name[1]);
            $_cookie = $_cookie[0];
        }
        if (!$not_check_expire) {
            $_cookie = explode("=", $_cookie);
            if (isset($_cookie[1])) {
                $expire = $_cookie[1];
                if ($expire < (time())) return false;
            }
            $_cookie = $_cookie[0];
        }
        return $_cookie;
    }

    public function setPopupCookie($name, $_cookie, $expire = false)
    {
        if ($expire) {
            $_cookie .= "=" . $expire;
        }
        if ($this->popupCookie) {
            $cookie = $this->popupCookie;
            $old_cookie = $this->getPopupCookie($name, true);
            if (strpos($cookie, $name) !== false) {
                $cookie = str_replace($name . ":" . $old_cookie, $name . ":" . $_cookie, $cookie);
            } else {
                $cookie .= "|" . $name . ":" . $_cookie;
            }
        } else {
            $cookie = $name . ":" . $_cookie;
        }
        setcookie('popupData', $cookie, time() + @date('Z') + (3600 * 24 * 365), '/');
        $this->popupCookie = $cookie;
    }

    public function isLoggedIn()
    {
        if (isset($_COOKIE['frontend']) && $this->getPopupCookie('magentoSessionId') == $_COOKIE['frontend']) {
            return $this->getPopupCookie('loggedIn');
        } else {
            return 0;
        }
    }

    public function isSecure()
    {
        return substr(urldecode($this->getParam('baseUrl')), 0, 5) == 'https';
    }
} ?>