<?php
/**
 * Welcome to the source of Codepoints.net!
 *
 * This is the main (and sole) entry to the site. Classes in
 * lib/*.class.php are auto-loaded. The controller for the URL
 * structure is lib/router.class.php.
 *
 * URLs are mapped to controllers in the following way: Every file in
 * ./controllers/*.php is included and registers an action for a
 * specific URL pattern. If the pattern is detected, the according action
 * is called.
 *
 * In lib/view.class.php is a view system defined, with the
 * views guiding the output living in views/.
 *
 * To get an instance of the database up and running, visit
 * <https://github.com/Boldewyn/unicodeinfo>. On a regular
 * *NIX system, a simple `make` in that project should provide
 * you with the ucd.sqlite to run this instance.
 *
 * This code is dually licensed under GPL and MIT. See
 * <http://codepoints.net/about#this_site> for details.
 */


/**
 * define Unicode Version in use
 */
define('UNICODE_VERSION', '6.1.0');


/**
 * set DEBUG level
 */
define('CP_DEBUG', 1);


/**
 * cache busting string
 */
define('CACHE_BUST', 'fe4f058ae607d3a9ea3b66f0d65464d5d40e2e1a');


/* enable gzip compression of HTML */
ini_set('zlib.output_compression', True);


/**
 * load classes from lib/
 */
function __autoload($class) {
    require_once 'lib/' . strtolower($class) . '.class.php';
}


/**
 * log $msg to /tmp/codepoints.log
 */
function flog($msg) {
   if (CP_DEBUG) {
       error_log(sprintf("[%s] %s\n", date("c"), trim($msg)), 3,
                 '/tmp/codepoints.log');
   }
}


$db = new DB('sqlite:'.dirname(__FILE__).'/ucd.sqlite');
$router = Router::getRouter();


$router->addSetting('db', $db)
       ->addSetting('info', UnicodeInfo::get());


/**
 * controllers sorted by complexity of routing
 * (i.e., simple string matches first)
 */
$controllers = array(
    'index', 'about', 'api_login', 'api_script', 'codepoint_of_the_day',
    'planes', 'random', 'scripts', 'search', 'wizard', 'sitemap',

    'single_character', 'plane', 'codepoint', 'block', 'possible_name',
    'range',
);

foreach ($controllers as $ctrl) {
    require_once "controllers/$ctrl.php";
}


$router->registerUrl('Codepoint', function ($object) {
    return sprintf("U+%s", $object->getId('hex'));
})
->registerUrl('UnicodeBlock', function ($object) {
    return str_replace(' ', '_', strtolower($object->getName()));
})
->registerUrl('UnicodePlane', function ($object) {
    $path = str_replace(' ', '_', strtolower($object->getName()));
    if (substr($path, -6) !== '_plane') {
        $path .= '_plane';
    }
    return $path;
})
->registerUrl('SearchResult', function ($object) {
    $path = 'search';
    if ($object instanceof SearchResult) {
        $q = $object->getQuery;
        $path .= http_build_query($q);
    }
    return $path;
});


if ($router->callAction() === False) {
    header('HTTP/1.0 404 Not Found');
    $block = Null;
    $plane = Null;
    $planes = UnicodePlane::getAll($db);
    if ($router->getSetting('noCP')) {
        $int = hexdec(substr($router->getSetting('request')->trunkUrl, 2));
        try {
            $block = UnicodeBlock::getForCodepoint($int,
                                    $router->getSetting('db'));
        } catch(Exception $e) {
            foreach ($planes as $p) {
                if ((int)$p->first <= $int && (int)$p->last >= $int) {
                    $plane = $p;
                    break;
                }
            }
        }
    }
    $req = $router->getSetting('request');
    $cps = codepoint::getForString(rawurldecode($req->trunkUrl), $db);
    $view = new View('error404');
    echo $view->render(compact('planes', 'block', 'plane', 'cps'));
}


// __END__
