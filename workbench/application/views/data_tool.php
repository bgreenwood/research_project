<?php $this->load->view('header');?>


    <div class="page-header">
      <h3>RDF Triplestore<?=$mode;?> Tool</h3>
    </div>

    <div class="row-fluid">
      <div class="span6">

        <form action="#" method="POST">

            TTL input: <small class="muted">(regularly used prefixes will automatically be declared)</small><br/>
            <textarea name="data" style="font-family:Courier; width:100%; font-size:0.9em;" placeholder="Please enter valid Turtle RDF" rows="20"><?=isset($data) ? $data : '';?></textarea>

            <br/><br/>
            <input type="submit" value="<?=$mode;?> Data" name="submit"/>

        </form>
      </div>


      <div class="span6">
        <?php 
        if (isset($error))
        {
            echo "<span class='label label-important'>Error</span><br/><p>" . $error . "</p>";
        }
        ?>

        <p><h5>Query Result:</h5></p>
        <?php 
        if (isset($result))
        {
            print_pre($result);
        }
        ?>

      </div>
    </div>


<?php $this->load->view('footer');?>