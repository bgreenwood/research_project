<?php $this->load->view('header');?>


    <div class="page-header">
      <h3>RDF Graph Editor</h3>
    </div>

    <h5>Editing Graph: <span class="text-success"><?=$current_graph;?></span></h5>

    <div class="row-fluid">
      <div class="span6">

        <form action="#" method="POST">

            TTL input: <small class="muted">(regularly used prefixes will automatically be declared)</small><br/>
            <textarea name="new_ttl" style="font-family:Courier; width:100%; font-size:0.9em;" placeholder="Please enter valid Turtle RDF" rows="20"><?=isset($current_ttl) ? $current_ttl : '';?></textarea>

            <br/><br/>
            <input type="submit" value="Update Graph" name="submit"/>

        </form>
      </div>


      <div class="span6">
        <?php 
        if (isset($error))
        {
            echo "<span class='label label-important'>Error</span><br/><p>" . $error . "</p>";
        }
        ?>

        <p><h5>Graph Representation:</h5></p>
        <?php 
        if (isset($current_html))
        {
            echo $current_html;
        }
        ?>

      </div>
    </div>


<?php $this->load->view('footer');?>