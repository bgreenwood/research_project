<?php $this->load->view('header');?>


    <div class="page-header">
      <h3>RDF Store - Namespace Listing</h3>
    </div>

    <div class="row-fluid">

      <div class="span8">
        <?php 
        if (isset($error))
        {
            echo "<span class='label label-important'>Error</span><br/><p>" . $error . "</p>";
        }
        ?>

        <p><h5>Registered built-in namespaces:</h5></p>
        <?php if (isset($namespaces)): ?>
            <?php

                    $result = '';
                    $result .= "<table>";
                    $result .= "<tr>";
                    $result .= "<th>Prefix</th>";
                    $result .= "<th>Namespace URI</th>";
                    $result .= "</tr>";
                    foreach ($namespaces as $prefix => $value) {
                        $result .= "<tr>";
                        $result .= "<td>$prefix:</td>";
                        $result .= "<td>&lt;$value&gt;</td>";
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