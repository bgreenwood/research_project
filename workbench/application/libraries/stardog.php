<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 


class Stardog 
{

	private $_uriBase = NULL;
	private $_database = NULL;
	private $_username = NULL;
	private $_password = NULL;
	private $client = NULL;
	private $_active_transaction = NULL;

	private $_config_options = array ("database.name", "search.enabled", "reasoning.punning.enabled", "reasoning.schema.graphs");


	function connect($url, $database, $username, $password)
	{
		$this->_uriBase = $url;
		$this->_database = $database;
		$this->_username = $username;
		$this->_password = $password;
		$this->_init();

		$this->client = EasyRdf_Http::getDefaultHttpClient();
        $this->client->resetParameters(true);
		$this->client->setHeaders('Authorization', "Basic " . base64_encode($username . ":" . $password));


        $this->client->setUri($url . $database . "/size");
        $this->client->setMethod('GET');
       	$this->client->setRawData(NULL);

        $response = $this->client->request();
        if(!$response->isSuccessful())
        {
        	throw new Exception("Unable to connect to Stardog instance: " . $response->asString());
        }
        return true;
	}


	function query($query, $inference = NULL)
	{	
		$sparql = new EasyRdf_Sparql_Client($this->_uriBase . $this->_database . "/query", $this->_username, $this->_password);
		return $sparql->query($query, $inference);
	}


	function addData($graph, $graph_uri = NULL, $format = 'turtle')
	{
		$this->_initTransaction();

		$formatObj = EasyRdf_Format::getFormat($format);
        $mimeType = $formatObj->getDefaultMimeType();

		if (is_object($graph) and $graph instanceof EasyRdf_Graph) {
            $data = $graph->serialise($format);
        } else {
            $data = $graph;
        }

		$prefixes = '';
        foreach (EasyRdf_Namespace::namespaces() as $prefix => $uri) {
            if (strpos($data, "$prefix:") !== false and
                strpos($data, "@prefix $prefix:") === false) {
                $prefixes .=  "@prefix $prefix: <$uri>.\n";
            }
        }


		$this->client->setUri($this->_uriBase . $this->_database . "/" . $this->_active_transaction . "/add" . ($graph_uri ? "?graph-uri=" . $graph_uri : ""));

		$this->client->setMethod('POST');
		$this->client->setRawData($prefixes . $data . "\n");

		$this->client->setHeaders('Content-Type', $mimeType );
		$this->client->setHeaders('Accept', $mimeType );

		$response = $this->client->request();
		if ($response->isSuccessful())
		{
			$this->_commit();
		}

		return $response->asString();
	}

	function clearGraph($graph_uri)
	{
		$this->_initTransaction();

		$this->client->setUri($this->_uriBase . $this->_database . "/" . $this->_active_transaction . "/clear" . ($graph_uri ? "?graph-uri=" . $graph_uri : ""));

		$this->client->setMethod('POST');

		$response = $this->client->request();
		if ($response->isSuccessful())
		{
			$this->_commit();
		}

		return $response->asString();
	}

	function listNamespaces()
	{
		return EasyRdf_Namespace::namespaces();
	}

	function deleteData($graph, $graph_uri = NULL, $format = 'turtle')
	{
		$this->_initTransaction();

		$formatObj = EasyRdf_Format::getFormat($format);
        $mimeType = $formatObj->getDefaultMimeType();

		if (is_object($graph) and $graph instanceof EasyRdf_Graph) {
            $data = $graph->serialise($format);
        } else {
            $data = $graph;
        }

		$prefixes = '';
        foreach (EasyRdf_Namespace::namespaces() as $prefix => $uri) {
            if (strpos($data, "$prefix:") !== false and
                strpos($data, "@prefix $prefix:") === false) {
                $prefixes .=  "@prefix $prefix: <$uri>.\n";
            }
        }


		$this->client->setUri($this->_uriBase . $this->_database . "/" . $this->_active_transaction . "/remove" . ($graph_uri ? "?graph-uri=" . $graph_uri : ""));

		$this->client->setMethod('POST');
		$this->client->setRawData($prefixes . $data . "\n");

		$this->client->setHeaders('Content-Type', $mimeType );
		$this->client->setHeaders('Accept', $mimeType );

		$response = $this->client->request();
		$this->_commit();
	}



	function _initTransaction()
	{
		$this->client->setUri($this->_uriBase . $this->_database . "/transaction/begin");
		$this->client->setMethod('POST');
		$response = $this->client->request();

		if(!$response->isSuccessful())
        {
        	throw new Exception("Unable to generate Stardog transaction ID: " . $response->asString());
        }
        else
        {
        	$this->_active_transaction = $response->getBody();
        }
	}

	function _commit()
	{
		if (is_null($this->_active_transaction))
		{
			throw new Exception("Call to commit without active transaction ID");
		}

		$this->client->setUri($this->_uriBase . $this->_database . "/transaction/commit/" . $this->_active_transaction);
		
		$this->client->setRawData(NULL);
		$this->client->setMethod('POST');
		$response = $this->client->request();

		if(!$response->isSuccessful())
        {
        	throw new Exception("Unable to generate Stardog transaction ID: " . $response->asString());
        }
        else
        {
        	$this->_active_transaction = NULL;
        	return $response;
        }
	}

	function config($settings = null)
	{
		$edit = !is_null($settings);
		$this->client->resetParameters(true);
		$mimeType = "application/json";
		$this->client->setHeaders('Content-Type', $mimeType );
		$this->client->setHeaders('Accept', $mimeType );
		$this->client->setHeaders('Authorization', "Basic " . base64_encode($this->_username . ":" . $this->_password));
		$this->client->setUri($this->_uriBase . "admin/databases/" . $this->_database . "/offline");
		$this->client->setMethod('PUT');
		$response = $this->client->request();
		if (!$response->isSuccessful())
		{
			throw new Exception("Error Putting Stardog database offline");
		}


		/* Get the settings */
		if (is_null($settings))
		{
			$settings = array_fill_keys($this->_config_options, "");
			$this->client->setMethod('PUT');
		}
		else
		{
			$this->client->setMethod('POST');
		}

		$this->client->setUri($this->_uriBase . "admin/databases/" . $this->_database . "/options");
		$this->client->setRawData(json_encode($settings));
		$response = $this->client->request();
		if (!$response->isSuccessful())
		{
			echo '<div class="alert alert-error">'.$response->getBody().'</div>';
			$settings = null;
		}
		else
		{
			if ($edit) {
				echo '<div class="alert alert-success">Settings updated!</div>';
			}
			$settings = json_decode($response->getBody(), true);
		}

		$this->client->setUri($this->_uriBase . "admin/databases/" . $this->_database . "/online");
		$this->client->setMethod('PUT');
		$response = $this->client->request();
		if (!$response->isSuccessful())
		{
			throw new Exception("Error Putting Stardog database online");
		}

		return $settings;
	}

	function addPrefixes($data)
	{
		$prefixes = '';
        foreach (EasyRdf_Namespace::namespaces() as $prefix => $uri) {
            if (strpos($data, "$prefix:") !== false and
                strpos($data, "@prefix $prefix:") === false) {
                $prefixes .=  "@prefix $prefix: <$uri>.\n";
            }
        }

        return $prefixes . $data;

	}

	function _init()
	{
		EasyRdf_Namespace::set('rda', 'http://purl.org/au-research/data/');
		EasyRdf_Namespace::set('subj', 'http://purl.org/au-research/subjects/');
		EasyRdf_Namespace::set('for', 'http://purl.org/au-research/vocabulary/anzsrc-for/2008/');
		EasyRdf_Namespace::set('dc', 'http://purl.org/dc/elements/1.1/');
		EasyRdf_Namespace::set('dct', 'http://purl.org/dc/terms/');
		EasyRdf_Namespace::set('dcat', 'http://www.w3.org/ns/dcat#');
		EasyRdf_Namespace::set('skos', 'http://www.w3.org/2004/02/skos/core#');
	}

}