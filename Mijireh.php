<?php 

$root_dir = dirname(__FILE__) . DIRECTORY_SEPARATOR;

if(!class_exists('Pest')) {
  require_once  $root_dir . 'pest.php';
}

// Require the mijireh library classes
require_once $root_dir . 'Mijireh/Model.php';
require_once $root_dir . 'Mijireh/Address.php';
require_once $root_dir . 'Mijireh/Item.php';
require_once $root_dir . 'Mijireh/Order.php';

class Mijireh_Exception extends Exception {}               
class Mijireh_ClientError extends Mijireh_Exception {}         /* Status: 400-499 */
class Mijireh_BadRequest extends Mijireh_ClientError {}        /* Status: 400 */
class Mijireh_Unauthorized extends Mijireh_ClientError {}      /* Status: 401 */
class Mijireh_NotFound extends Mijireh_ClientError {}          /* Status: 404 */
class Mijireh_ServerError extends Mijireh_Exception {}         /* Status: 500-599 */
class Mijireh_InternalError extends Mijireh_ServerError {}     /* Status: 500 */

class Mijireh {
  
  /* Live server urls */
  public static $base_url = 'https://secure.mijireh.com/';
  public static $url      = 'https://secure.mijireh.com/api/1/';
  
  public static $access_key;
  
  /**
   * Return the job id of the slurp
   */
  public static function slurp($url) {
    $url_format = '/^(https?):\/\/'.                           // protocol
    '(([a-z0-9$_\.\+!\*\'\(\),;\?&=-]|%[0-9a-f]{2})+'.         // username
    '(:([a-z0-9$_\.\+!\*\'\(\),;\?&=-]|%[0-9a-f]{2})+)?'.      // password
    '@)?(?#'.                                                  // auth requires @
    ')((([a-z0-9][a-z0-9-]*[a-z0-9]\.)*'.                      // domain segments AND
    '[a-z][a-z0-9-]*[a-z0-9]'.                                 // top level domain  OR
    '|((\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5])\.){3}'.
    '(\d|[1-9]\d|1\d{2}|2[0-4][0-9]|25[0-5])'.                 // IP address
    ')(:\d+)?'.                                                // port
    ')(((\/+([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)*'. // path
    '(\?([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)'.      // query string
    '?)?)?'.                                                   // path and query string optional
    '(#([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)?'.      // fragment
    '$/i';
    
    if(!preg_match($url_format, $url)) {
      throw new Mijireh_NotFound('Unable to slurp invalid URL: $url');
    }
    
    try {
      $pest = new Pest($url);
      $html = $pest->get('');
      $data = array(
        'url' => $url,
        'html' => $html,
      );
      $pest = new PestJSON(self::$url);
      $pest->setupAuth(self::$access_key, '');
      $result = $pest->post('slurps', $data);
      return $result['job_id'];
    }
    catch(Pest_Unauthorized $e) {
      throw new Mijireh_Unauthorized("Unauthorized. Please check your api access key");
    }
    catch(Pest_NotFound $e) {
      throw new Mijireh_NotFound("Mijireh resource not found: " . $pest->last_request['url']);
    }
    catch(Pest_ClientError $e) {
      throw new Mijireh_ClientError($e->getMessage());
    }
    catch(Pest_ServerError $e) {
      throw new Mijireh_ServerError($e->getMessage());
    }
    catch(Pest_UnknownResponse $e) {
      throw new Mijireh_Exception('Unable to slurp the URL: $url');
    }
  }
  
  /**
   * Return an array of store information
   */
  public static function get_store_info() {
    $pest = new PestJSON(self::$url);
    $pest->setupAuth(self::$access_key, '');
    try {
      $result = $pest->get('store');
      return $result;
    }
    catch(Pest_BadRequest $e) {
      throw new Mijireh_BadRequest($e->getMessage());
    }
    catch(Pest_Unauthorized $e) {
      throw new Mijireh_Unauthorized("Unauthorized. Please check your api access key");
    }
    catch(Pest_NotFound $e) {
      throw new Mijireh_NotFound("Mijireh resource not found: " . $pest->last_request['url']);
    }
    catch(Pest_ClientError $e) {
      throw new Mijireh_ClientError($e->getMessage());
    }
    catch(Pest_ServerError $e) {
      throw new Mijireh_ServerError($e->getMessage());
    }
  }
  
  public static function preview_checkout_link() {
    if(empty(Mijireh::$access_key)) {
      throw new Mijireh_Exception('Access key required to view checkout preview');
    }
    
    return self::$base_url . 'checkout/' . self::$access_key;
  }
  
}
