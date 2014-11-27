<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Dashboard extends CI_Controller {

	public function __construct()
	{
		parent::__construct();

		try
		{
			$this->stardog->connect($this->config->item('stardog_server'), $this->config->item('stardog_db_name'), 
									$this->config->item('stardog_username'), $this->config->item('stardog_password'));
		}
		catch (Exception $e)
		{
			die('Unable to connect to Stardog Database! Check your settings.');
		}
	}



	public function index()
	{
		$this->load->view('welcome_message');
	}


	public function query()
	{
		$data = array();

		if ($this->input->get('sparql'))
		{
			$data['sparql'] = $this->input->get('sparql');
		}
		else
		{
			$data['sparql'] = "SELECT * { GRAPH ?g { ?s ?p ?o } } LIMIT 10";
		}

		if ($this->input->post('sparql'))
		{
			$data['sparql'] = $this->input->post('sparql');
			try
			{
				$results = $this->stardog->query($this->input->post('sparql'), 'QL');
				$data['response'] = $results;

			}
			catch (Exception $e)
			{
				$data['error'] = $e->getMessage();
			}

		}

		
		$this->load->view('query_listing', $data);
	}

	public function settings()
	{
		if ($this->input->post('submit'))
		{
			$values = $this->input->post();
			unset($values['submit']);
			$config_values = array();
			foreach ($values AS $key => $val)
			{
				if (!in_array($key, array("database_name", "reasoning_punning_enabled", "database_online")))
				{
					$config_values[str_replace("_",".",$key)] = $val;
				}
			}
			$this->stardog->config($config_values);
		}
		$data = array ("config_items" => $this->stardog->config());
		$this->load->view('config_editor', $data);
	}

	public function graph_list()
	{	
		$graph = $this->input->get('clear_graph');
		if (isset($graph) && $graph)
		{
			$this->stardog->clearGraph($graph);
			header("Location: ". base_url('dashboard/graph_list'));
			die();
		}

		$data = array();
		$data['sparql'] = "SELECT ?graph_name (COUNT(*) AS ?triple_count) WHERE { GRAPH ?graph_name { ?s ?p ?o } } GROUP BY ?graph_name";

		try
		{
			$results = $this->stardog->query($data['sparql']);
			$data['response'] = $results;
		}
		catch (Exception $e)
		{
			$data['error'] = $e->getMessage();
		}
		
		$this->load->view('graph_listing', $data);
	}


	public function add()
	{
		$data = array("mode"=>"Add");

		if ($this->input->get('data'))
		{
			$data['data'] = $this->input->get('data');
		}

		if ($this->input->post('data'))
		{
			try
			{
				$result = $this->stardog->addData($this->input->post('data'), "http://testgraph.com/");
				$data['result'] = $result;
			}
			catch (Exception $e)
			{
				$data['error'] = $e->getMessage();
			}
		}



		$this->load->view('data_tool', $data);
	}

	function namespace_list()
	{
		$data = array();

		$data['namespaces'] = $this->stardog->listNamespaces();

		$this->load->view('namespace_list', $data);
	}

	function editor($graph_uri = null)
	{
		$data = array();
		if ($this->input->get('graph_uri'))
		{
			$graph_uri = $this->input->get('graph_uri');
		}

		// Default to TBox
		if (is_null($graph_uri)) 
		{
			$graph_uri = $this->config->item('stardog_mapping_graph');
		}

		$data['current_graph'] = $graph_uri;

		try
		{
			$gs = new EasyRdf_GraphStore($this->config->item('stardog_server') . $this->config->item('stardog_db_name'),
										$this->config->item('stardog_username'), $this->config->item('stardog_password'));
			$graph = $gs->get($graph_uri);

			if ($this->input->post('new_ttl'))
			{
				$ttl_data = $this->stardog->addPrefixes($this->input->post('new_ttl'));
				$graph = new EasyRdf_Graph($graph_uri, $ttl_data, 'turtle');
				try
				{
					$gs->replace($graph);
					$data['current_ttl'] = $graph->serialise('turtle');
					$data['current_html'] = $graph->dump(true);
				}
				catch (Exception $e)
				{
					$data['current_html'] = "Error updating...";
				}

			}

			$data['current_ttl'] = $graph->serialise('turtle');
			$data['current_html'] = $graph->dump(true);

			$this->load->view('editor', $data);
		}
		catch (Exception $e)
		{
			$data['current_ttl'] = $this->input->post('new_ttl');
			$data['current_html'] ="<div class='alert alert-error'><b>Error: </b> " . $e->getMessage() ."</div>";
			$this->load->view('editor', $data);
		}
	}

	public function delete()
	{
		$data = array("mode"=>"Delete");

		if ($this->input->get('data'))
		{
			$data['data'] = $this->input->get('data');
		}

		if ($this->input->post('data'))
		{
			try
			{
				$result = $this->stardog->deleteData($this->input->post('data'));
				$data['result'] = $result;
			}
			catch (Exception $e)
			{
				$data['error'] = $e->getMessage();
			}
		}



		$this->load->view('data_tool', $data);
	}


}