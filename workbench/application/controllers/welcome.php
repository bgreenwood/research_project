<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -  
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in 
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
public function view()
{
	$this->load->view('welcome_message');
}

	public function client()
	{
		if ($this->input->post('submit'))
		{
			$query = $this->input->post('sparql');
			$sparql = new EasyRdf_Sparql_Client('http://localhost:5822/test_db/','admin','admin');
			$result = $sparql->query($query);
			echo $result->dump(true);
		}
		else
		{
			$this->load->view('welcome_message');
		}
	}


	public function index()
	{
		echo "<pre>";
		$this->stardog->connect('http://localhost:5822/test_db/','admin','admin');
		echo $this->stardog->addData('dc:created owl:equivalentProperty dc:madeup.');

		$results = $this->stardog->query("SELECT ?s ?o WHERE { ?s dc:madeup ?o }", 'QL');
		echo $results->dump(true);
		  // Use a local SPARQL 1.1 Graph Store (eg RedStore)
		//echo $this->stardog->marco();
		//$sparql = new EasyRdf_Sparql_Client('http://localhost:5822/test_db/','admin','admin');
		//$results = $sparql->query("SELECT * WHERE { ?s ?p ?o } LIMIT 10");
		/*$sparql->query("CREATE GRAPH <test.rdf>");

		$gs = new EasyRdf_GraphStore('http://localhost:5822/test_db/query');
		//echo $gs;
		  // Add the current time in a graph
		  $graph1 = new EasyRdf_Graph();
		  $graph1->add('http://example.com/test', 'rdfs:label', 'Test');
		  $graph1->add('http://example.com/test', 'dc:date', time());
		  echo $graph1->dump();
		  $gs->insert($graph1,'test.rdf');

		   // Get the graph back out of the graph store and display it
  			//$graph2 = $gs->get('time.rdf');
			//  print $graph2->dump();*/
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */