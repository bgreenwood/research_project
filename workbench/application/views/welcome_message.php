<?php $this->load->view('header');?>


 <div class="page-header">
          <h1>Stardog Server Dashboard</h1>
        </div>
        <p class="lead">
        	This interface allows you to interact with your Stardog SPARQL endpoint. 
        </p>

        <p>
        	<dl style="padding:20px;">
        		<dt>Stardog Endpoint</dt>
        		<dd><pre><?=$this->config->item('stardog_server');?></pre></dd>

        		<dt>Database Name</dt>
        		<dd><pre><?=$this->config->item('stardog_db_name');?></pre></dd>

        		<dt>Database User</dt>
        		<dd><pre><?=$this->config->item('stardog_username');?></pre></dd>

        		<dt>Data Graph</dt>
        		<dd><pre><?=htmlentities($this->config->item('stardog_data_graph'));?></pre></dd>

        		<dt>Mappings Graph <em class="muted">(graph where the inference engine looks for RDFS/OWL axioms)</em></dt>
        		<dd><pre><?=htmlentities($this->config->item('stardog_mapping_graph'));?></pre></dd>

        		<dt>Server Status</dt>
        		<dd><span class="label">Unknown</span></dd>
        </p>


        <!--form action="#" method="POST">

Prefixes:<br/>
	<textarea name="prefix" rows=3 cols=60><?=isset($prefixes) ? $prefixes : '';?></textarea>


<br/><br/>
SPARQL:<br/>
	<textarea name="sparql" style="font-family:Courier;" rows=20 cols=100><?=isset($sparql) ? $sparql : '';?></textarea>

<br/><br/>
<input type="submit" name="submit" /-->

<?php $this->load->view('footer');?>