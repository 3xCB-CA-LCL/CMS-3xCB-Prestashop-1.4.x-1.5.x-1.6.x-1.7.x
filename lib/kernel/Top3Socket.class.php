<?php

/**
 * Connection class using fsockopen
 *
 *
 */
class Top3Socket {

  const TIMEOUT = 5;

  protected $host;
  protected $port;
  protected $is_ssl = true;
  protected $method = 'POST';
  protected $data;
  protected $path;
  protected $response = '';
  protected $errno;
  protected $errstr;

  /**
   * inits a connetion to a server
   *
   * @param string $url URL to reach
   * @param string $method HTTP method (GET or POST)
   * @param array $data data to send
   */
  public function __construct($url, $method = 'POST', array $data = null, $json_format = false) {
    //sets the HTTP method if recognized, throw an error otherwise
    if (strtoupper($method) == 'GET' || strtoupper($method) == 'POST')
      $this->method = strtoupper($method);
    else {
      $msg = "La méthode demandée ($method) n'est pas reconnue.";
      insertLog(__METHOD__ . ' : ' . __LINE__, $msg);
      throw new Exception($msg);
    }

    //builds data string
    if (!is_null($data))
      if($json_format)
          $this->data = json_encode($data);
        else
          $this->data = http_build_query($data);

    //pars the given URL
    //Watch out !! It will replace the actual value of $this->data if $url contains datas
    $this->parseUrl($url);
  }

  /**
   * cleans the URL $url to split scheme, host, path and query
   * nettoie l'url appelée pour séparer hôte et script
   *
   * @param string $url url du script appelé
   */
  public function parseUrl($url) {
    preg_match('`^([a-z0-9]+://)?([^/:]+)(/.*$)?`i', $url, $out);

    $components = parse_url($url);
    extract($components);
    //if non secured connexion asked, sets is_ssl to false and port to 80
    if ($scheme == 'http') {
      $this->is_ssl = false;
      $this->port = 80;
    }

    //if secured connexion asked, sets is_ssl to trueand port to 443
    if ($scheme == 'https') {
      $this->is_ssl = true;
      $this->port = 443;
    }

    //gets host
    $this->host = $host;
    //gets path
    $this->path = $path;

    //gets the query data
    if (isset($query))
      $this->data = $query;
  }

  /**
   * builds and returns header
   *
   * @return string
   */
  function build_header($json_format = false) {
    if ($this->method == 'POST') {
      $header = "POST " . $this->path . " HTTP/1.0\r\n";
      $header .= "Host: " . $this->host . "\r\n";
      if($json_format)
        $header .= "Content-Type: application/json\r\n";
      else
        $header .= "Content-type: application/x-www-form-urlencoded\r\n";
      $header .= "Content-length: " . strlen($this->data) . "\r\n\r\n";
      $header .= $this->data;
    } elseif ($this->method == 'GET') {
      if (strlen($this->path . $this->data) > 2048) {
        insertLog(get_class($this) . " : __construct", "Maximum length in get method reached(" . strlen($this->path . $this->data) . ")");
      }
      $header = "GET " . $this->path . '?' . $this->data . " HTTP/1.1\r\n";
      $header .= "Host: " . $this->host . "\r\n";
      $header .= "Connection: close\r\n\r\n";
    }

    return ($header);
  }

  /**
   * sends the request to host and returns the response
   * 
   * @return string
   */
  function send($json_format = false) {
    //builds header
    $header = $this->build_header($json_format);

    //connects to the server and send header and gets the response
    $this->response = $this->connect($header);
	
    if($json_format){

      if($this->response != false){
        $result['header'] = $this->getContentHeader();
        $result['response'] = $this->getContent();
        return $result;
      }
      else
        return false;
    }
    else{

       $this->response = $this->connect($header);
       return $this->getContent();
    }
  }


  /**
   * sends the request to host and returns the response
   * 
   * @return string
   */
  function send_copilot() {
    //builds header
    $header = $this->build_header();

    //connects to the server and send header and gets the response
    $this->response = $this->connect($header);
	
	if($this->response != false){
		$result['header'] = $this->getContentHeader();
		$result['response'] = $this->getContent();
		return $result;
	}
	else
		return false;
  }

  /**
   * connects to a server, reaches the path and returns the response if connexion succeed, false otherwise
   *
   * @param string $header request header
   * @return mixed
   */
  function connect($header) {
    //connects with SSL or TLS protocol if secure connection asked, HTTP protocol otherwise
	
    if ($this->is_ssl){
		//Tentative de connexion en SSL fix bug des versions de php ne prenant pas en compte tls1.x
		//on force tout de meme en ssl, la negociation se fera en TLS si SSL non trouvé
		$socket = fsockopen('ssl://' . $this->host, $this->port, $this->errno, $this->errstr, Top3Socket::TIMEOUT);
		if($socket == false){ //si la connexion SSL échoue, on tente en TLS
			insertLog(__METHOD__ . ' : ' . __LINE__, "Connexion TLS échouée, envoi des données impossible sur : " . $this->host);
			//Tentative de connexion en TLS
			$socket = fsockopen('tls://' . $this->host, $this->port, $this->errno, $this->errstr, Top3Socket::TIMEOUT);
			if($socket == false){
				insertLog(__METHOD__ . ' : ' . __LINE__, "Connexion SSL échouée, envoi des données impossible sur : " . $this->host);
			}
		}
	}
    else
      $socket = fsockopen($this->host, $this->port);

    //if connection established
    if ($socket !== false) {
      $res = '';

      //sends header and reads response
      if (@fputs($socket, $header))
        while (!feof($socket))
          $res .= fgets($socket, 128);
      //if header sending is impossible : log
      else {
        insertLog(__METHOD__ . ' : ' . __LINE__, "Envoi des données impossible sur : " . $this->host);
        $res = false;
      }
      //closes the connexion
      fclose($socket);
    } else { //if connection failed, log
      insertLog(__METHOD__ . ' : ' . __LINE__, "Connexion socket impossible sur l'hôte $this->host. Erreur " . $this->errno . " : " . $this->errstr);
      $res = false;
    }
	
    //return the response
    return $res;
  }

  /**
   * splits header and body response and returns header
   *
   * @return string
   */
  public function getContentHeader() {
    return preg_replace('#(.+)(\r\n){2}(.+)$#s', '$1', $this->response);
  }

  /**
   * splits header and body response and returns body
   *
   * @return string
   */
  public function getContent() {
    return preg_replace('#.+(\r\n){2}(.+)$#s', '$2', $this->response);
  }

}