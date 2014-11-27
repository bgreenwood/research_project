<?php $this->load->view('header');?>


    <div class="page-header">
      <h3>Stardog Settings Editor</h3>
    </div>
    <div class="row-fluid">
      <div class="span6">

        <form action="#" method="POST">

         <dl class="dl-horizontal">
         
         <?php
         if (!is_null($config_items))
         {
            foreach ($config_items AS $key => $value)
            {
                echo "<dt>$key</dt><dd><input type='text' name=\"" . str_replace(".","_",$key) . "\" value=\"" . str_replace("'","", var_export($value, true)) . "\"/></dd>";
            }
         }
         ?>
        </dl>
        <input class="btn btn-success" value="Update Settings" name="submit" type="submit" />
        </form>

      </div>
    </div>


<?php $this->load->view('footer');?>