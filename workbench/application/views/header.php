<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Stardog Workbench</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- CSS -->
    <link href="<?=base_url('assets/bootstrap/css/bootstrap.css');?>" rel="stylesheet">
    <style type="text/css">

      /* Sticky footer styles
      -------------------------------------------------- */

      html,
      body {
        height: 100%;
        /* The html and body elements cannot have any padding or margin. */
      }

      /* Wrapper for page content to push down footer */
      #wrap {
        min-height: 100%;
        height: auto !important;
        height: 100%;
        /* Negative indent footer by it's height */
        margin: 0 auto -60px;
      }

      /* Set the fixed height of the footer here */
      #push,
      #footer {
        height: 60px;
      }
      #footer {
        background-color: #f5f5f5;
      }

      /* Lastly, apply responsive CSS fixes as necessary */
      @media (max-width: 767px) {
        #footer {
          margin-left: -20px;
          margin-right: -20px;
          padding-left: 20px;
          padding-right: 20px;
        }
      }

    </style>
    <link href="<?=base_url('assets/bootstrap/css/bootstrap-responsive.css');?>" rel="stylesheet">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="../assets/js/html5shiv.js"></script>
    <![endif]-->
  </head>

  <body>

    <!-- Part 1: Wrap all page content here -->
    <div id="wrap">

      <!-- Fixed navbar -->
      <div class="navbar">
        <div class="navbar-inner">
          <a class="brand" href="<?=base_url();?>"> &nbsp; <?=$this->config->item('project_name');?></a>
          <div class="pull-right nav-collapse collapse">
            <ul class="nav">
              <li><a href="<?=base_url("dashboard/graph_list");?>">Graph Editor</a></li>
              <li><a href="<?=base_url("dashboard/query");?>">Query Tool</a></li>
              <li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="#">Settings <i class="caret"></i>
                  <ul class="dropdown-menu pull-right">
                    <li><a href="<?=base_url("dashboard/");?>">Server Status</a></li>
                    <li><a href="<?=base_url("dashboard/settings");?>">Stardog Settings</a></li>
                    <li><a href="<?=base_url("dashboard/namespace_list");?>">Declared Namespaces</a></li>
                  </ul>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Begin page content -->
      <div class="container">
