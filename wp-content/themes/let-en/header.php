<!DOCTYPE html>
<!--[if lt IE 7]><html class="no-js lt-ie9 lt-ie8 lt-ie7" <?php language_attributes() ?>><![endif]-->
<!--[if IE 7]><html class="no-js lt-ie9 lt-ie8" <?php language_attributes() ?>><![endif]-->
<!--[if IE 8]><html class="no-js lt-ie9" <?php language_attributes() ?>><![endif]-->
<!--[if gt IE 8]><!--><html class="no-js" <?php language_attributes() ?>><!--<![endif]-->
    <head>
        <meta charset="<?php bloginfo( 'charset' ) ?>">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- TODO: Relative paths! -->
        <link rel="icon" type="image/png" href="<?php bloginfo('template_directory'); ?>/favicon-32x32.png" sizes="32x32">
        <link rel="icon" type="image/png" href="<?php bloginfo('template_directory'); ?>/favicon-96x96.png" sizes="96x96">
        <link rel="icon" type="image/png" href="<?php bloginfo('template_directory'); ?>/favicon-16x16.png" sizes="16x16">

        <title><?php bloginfo('name') ?></title>
		<meta name="author" content="Alberto Barradas">
        <meta name="description" content="<?php bloginfo('description') ?>">
        <link rel="canonical" href="<?php bloginfo('url') ?>">
		<link rel="author" href="http://github.com/abcsds">
		<?php wp_head() ?>

        <meta property="og:title" content="MECATE International Public Entrepreneurship Program">
        <meta property="og:site_name" content="Laboratorio de Emprendimiento y Transformación.">
        <meta property="og:url" content="http://let-emprendimientopublico.mx/mecate">
        <meta property="og:description" content="Diseñado por el Laboratorio de Emprendimiento y Transformación (LET) de la Escuela de Gobierno y Transformación Pública, Mecate es un programa internacional de emprendimiento público que convoca a estudiantes de posgrados de distintas universidades de México y del mundo, así como a ciudadanos y emprendedores excepcionales, para realizar propuestas con impacto social en México.">
        <meta property="og:type" content="website">
        <meta property="og:image" content="http://let-emprendimientopublico.mx/wp-content/img/meta.jpg">

        <meta name="twitter:card" content="player">
        <meta name="twitter:site" content="@LETEmprende">
        <meta name="twitter:title" content="MECATE International Public Entrepreneurship Program">
        <meta name="twitter:description" content="Diseñado por el Laboratorio de Emprendimiento y Transformación (LET) de la Escuela de Gobierno y Transformación Pública, Mecate es un programa internacional de emprendimiento público que convoca a estudiantes de posgrados de distintas universidades de México y del mundo, así como a ciudadanos y emprendedores excepcionales, para realizar propuestas con impacto social en México.">
        <meta name="twitter:image:src" content="http://let-emprendimientopublico.mx/wp-content/img/logos/mecate-prog.png">
        <meta name="twitter:player" content="">

        <!-- Bootstrap Core CSS -->
        <!-- TODO: Relative paths! -->
        <link rel="stylesheet" href="<?php bloginfo('template_directory'); ?>/css/bootstrap.min.css" type="text/css">
        <link rel="stylesheet" href="<?php bloginfo('template_directory'); ?>/css/global.css" type="text/css">

        <!-- Custom Fonts -->
        <link href='http://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800' rel='stylesheet' type='text/css'>
        <link href='http://fonts.googleapis.com/css?family=Merriweather:400,300,300italic,400italic,700,700italic,900,900italic' rel='stylesheet' type='text/css'>
        <!-- TODO: Relative paths! -->
        <link rel="stylesheet" href="<?php bloginfo('template_directory'); ?>/font-awesome/css/font-awesome.min.css" type="text/css">


        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>
    <body <?php body_class() ?>>
        <nav id="mainNav" class="navbar navbar-default navbar-fixed-top">
          <div class="container-fluid" id="primary-nav">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
              <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
              </button>
              <a class="short-title navbar-brand page-scroll" href="<?php get_home_url(); ?>#page-top">
                <img src="<?php bloginfo('template_directory'); ?>/img/logos/let_2x-white.png" alt="Logo LET + ITESM" class="white" />
                <img src="<?php bloginfo('template_directory'); ?>/img/logos/let_2x.png" alt="Logo LET + ITESM" class="color" />
              </a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
              <ul class="nav navbar-nav navbar-right">
            <?php
            $args = array(
            	'sort_order' => 'DESC',
            	'hierarchical' => 1,
            	'exclude' => '',
            	'child_of' => 0,
            	'parent' => -1,
            	'exclude_tree' => '',
            	'number' => '',
            	'offset' => 0,
            	'post_type' => 'page',
            	'post_status' => 'publish'
            );
            $pages = get_pages($args);
            foreach ($pages as $page_data) {
                $title = $page_data->post_title;
                $slug = $page_data->post_name;
            ?>

                <li>
                        <a class="page-scroll" href="<?php get_home_url(); ?>#<?php echo "$slug" ?>"><?php echo "$title" ?></a>
                </li>

            <?php
            }?>
                <li>
                    <a class="page-scroll mini" href="http://let-emprendimientopublico.mx/">Espa&ntilde;ol</a>
                </li>
            </ul>
          <div>
            <!-- /.navbar-collapse -->
          </div>
          <!-- /.container-fluid -->
        </nav>

        <header class="bg-primary">
          <div class="header-content">
            <div class="header-content-inner">
              <img src="<?php bloginfo('template_directory'); ?>/img/logos/let-logo-hor-white.png" alt="let-logo" class="img-responsive main-logo">
              <hr>
              <p>An active community focused on designing, building and implementing new solutions to transform people’s lives through social innovation and public entrepreneurship in key sectors.</p>
              <a href="#objetivos" class="btn btn-primary btn-xl page-scroll">Learn more</a>
            </div>
          </div>
        </header>



        <div id="content-wrap">