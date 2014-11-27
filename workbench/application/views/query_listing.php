<?php $this->load->view('header');?>


    <div class="page-header">
      <h3>Database Query Tool</h3>
    </div>

    <div class="row-fluid">
      <div class="span4">

        <form action="#" method="POST">

            SPARQL 1.1-ish query: <br/><small class="muted">(<a href="<?=base_url('dashboard/namespace_list');?>">declared prefices</a> will automatically be added)</small><br/>
            <textarea name="sparql" style="font-family:Courier; width:100%; font-size:0.9em;" placeholder="Please enter a SPARQL query and click Submit" rows=20 cols=100><?=isset($sparql) ? $sparql : '';?></textarea>

            <br/><br/>
            <input type="submit" name="submit"/>

        </form>


      </div>


      <div class="span8">
        <?php 
        if (isset($error))
        {
            echo "<span class='label label-important'>Error</span><br/><p>" . $error . "</p>";
        }
        ?>

        <p><h5>Query Result:</h5></p>
        <?php if (isset($response)): ?>
            <?php

                    $result = '';
                    $result .= "<table class='sparql-results' style='border-collapse:collapse'>";
                    $result .= "<tr>";
                    foreach ($response->getFields() as $field) {
                        $result .= "<th style='border:solid 1px #000;padding:4px;".
                                   "vertical-align:top;background-color:#eee;'>".
                                   "?$field</th>";
                    }
                    $result .= "<th></th>";
                    $result .= "</tr>";
                    foreach ($response as $row) {
                        $result .= "<tr>";
                        foreach ($response->getFields() as $field) {
                            $result .= "<td style='border:solid 1px #000;padding:4px;".
                                       "vertical-align:top'>".
                                       ($field == "g" ?
                                        '<a href="' . base_url('dashboard/editor?graph_uri=' . rawurlencode($row->$field->dumpValue(false))) . '">' . $row->$field->dumpValue(false) . "</a>"
                                        :
                                        $row->$field->dumpValue(false)
                                       )."</td>";
                        }
                        $result .= "<td style='border:solid 1px #000;padding:4px;''><button>x</button></td>";
                        $result .= "</tr>";
                    }
                    $result .= "</table>";
            
                    echo $result;
            ?>
        <?php else: ?>
            <em class="muted">No query results</em>
        <?php endif; ?>

      </div>
    </div>


<?php $this->load->view('footer');?>