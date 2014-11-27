<?php $this->load->view('header');?>


    <div class="page-header">
      <h3>RDF Stardog Graph Listing</h3>
    </div>

    <div class="row-fluid">

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
                        
                        $graph_uri = array_shift($response->getFields());
                        if (!isset($row->$graph_uri))
                        {
                            continue;
                        }
                        $graph_uri = $row->$graph_uri->dumpValue(false);

                        foreach ($response->getFields() as $field) {
                            $result .= "<td style='border:solid 1px #000;padding:4px;".
                                       "vertical-align:top'>".
                                       $row->$field->dumpValue(false)."</td>";
                        }
                        $result .= "<td style='border:solid 1px #000;padding:4px;''>";
                        $result .= "<a href='".base_url('dashboard/editor?graph_uri=' . rawurlencode($graph_uri))."'><i class=\"icon-pencil\"></i></a> ";
                        $result .= "<a onclick=\"return confirm('This will permanently remove this graph?');\" href='".base_url('dashboard/graph_list?clear_graph=' . rawurlencode($graph_uri))."'><i class=\"icon-trash\"></i></a></td>";
                        $result .= "</tr>";
                    }
                    $result .= "</table>";
                    $result .= "<p></p>";
                    $result .= "<p><br/></p>";
                    $result .= "<form action='".base_url('dashboard/editor')."' method='GET'>";
                    $result .= "New graph: <small><input type='text' name='graph_uri' /></small> <input type='submit' class='btn btn-mini' value='Create'/>";
                    $result .= "</form>";

                    echo $result;
            ?>
        <?php else: ?>
            <em class="muted">No query results</em>
        <?php endif; ?>

      </div>
    </div>


<?php $this->load->view('footer');?>