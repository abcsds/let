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
        <link rel="icon" type="image/png" href="/favicon-32x32.png" sizes="32x32">
        <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96">
        <link rel="icon" type="image/png" href="/favicon-16x16.png" sizes="16x16">

        <title><?php wp_title( '|', true, 'right' ) ?></title>
		<meta name="author" content="Alberto Barradas">
        <meta name="description" content="<?php bloginfo('description') ?>">
        <link rel="canonical" href="<?php bloginfo('url') ?>">
		<link rel="author" href="http://github.com/abcsds">
		<?php wp_head() ?>

        <!-- Bootstrap Core CSS -->
        <!-- TODO: Relative paths! -->
        <link rel="stylesheet" href="/css/bootstrap.min.css" type="text/css">

        <!-- Custom Fonts -->
        <link href='http://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800' rel='stylesheet' type='text/css'>
        <link href='http://fonts.googleapis.com/css?family=Merriweather:400,300,300italic,400italic,700,700italic,900,900italic' rel='stylesheet' type='text/css'>
        <!-- TODO: Relative paths! -->
        <link rel="stylesheet" href="/font-awesome/css/font-awesome.min.css" type="text/css">


        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>
    <body <?php body_class() ?>>

        <nav id="mainNav" class="navbar navbar-default navbar-fixed-top">
          <div class="container-fluid">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
              <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
              </button>
              <a class="short-title navbar-brand page-scroll" href="#page-top">
                <img src="/img/logos/let_2x-white.png" alt="Logo LET + ITESM" class="color" />
                <img src="/img/logos/let_2x.png" alt="Logo LET + ITESM" class="white" />
              </a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
              <ul class="nav navbar-nav navbar-right">
                  <?php wp_nav_menu(array(
      				'theme_location' => 'main-nav',
      				'container'      => 'nav',
      				'container_id'   => 'primary-nav'
      			)) ?>
                <?php wp_nav_menu(
                array(
                	'theme_location'  => '',
                	'menu'            => '',
                	'container'       => 'div',
                	'container_class' => '',
                	'container_id'    => '',
                	'menu_class'      => 'menu',
                	'menu_id'         => '',
                	'echo'            => true,
                	'fallback_cb'     => 'wp_page_menu',
                	'before'          => '',
                	'after'           => '',
                	'link_before'     => '',
                	'link_after'      => '',
                	'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                	'depth'           => 0,
                	'walker'          => ''
                ));
                ?>
                {% for menu in t.menu %}
                <li>
                  {% if menu.content == "Español" or menu.content == "English" %}
                  <a class="page-scroll mini" href="{{ menu.link }}">{{ menu.content }}</a>
                    {% else %}
                    <a class="page-scroll" href="{{ menu.link }}">{{ menu.content }}</a>
                  {% endif %}
                </li>
                {% endfor %}
              </ul>
            </div>
            <!-- /.navbar-collapse -->
          </div>
          <!-- /.container-fluid -->
        </nav>

		<header id="bg-primary">
            <div class="header-content">
                <div class="header-content-inner">
                    <img src="{{ t.home.logo }}" alt="let-logo" class="img-responsive main-logo">
                    <hr>
                    <p>{{ t.home.content }}</p>
                    <a href="#objetivos" class="btn btn-primary btn-xl page-scroll">{{ t.home.button }}</a>
                </div>
            </div>

		<div id="content-wrap">
