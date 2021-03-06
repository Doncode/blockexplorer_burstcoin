<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">   
    <title>{$siteTitle|default:"Burstcoin Block-Explorer"} - burstcoin</title>
    <link href="favicon.ico" rel="shortcut icon">
    {*<link href="{$httpRoot}assets/css/style.css" rel="stylesheet">
    <link href="{$httpRoot}assets/css/theme.css" rel="stylesheet">
    <link href="{$httpRoot}assets/css/ui.css" rel="stylesheet">
    <link href="{$httpRoot}assets/css/layout.css" rel="stylesheet">
    <link href="{$httpRoot}assets/plugins/metrojs/metrojs.min.css" rel="stylesheet">*}
    <link href="{$httpRoot}assets/css/main.css" rel="stylesheet">
    
    <script src="{$httpRoot}assets/plugins/modernizr/modernizr-2.6.2-respond-1.1.0.min.js"></script>
    {if isset($jsCaptcha)}<script src="https://www.google.com/recaptcha/api.js"></script>{/if}
  </head>
  <body class="fixed-topbar fixed-sidebar theme-sdtl color-default dashboard">
    <section>
      <!-- BEGIN SIDEBAR -->
      <div class="sidebar">
        <div class="logopanel">
          <h1>
            <a href="{$httpRoot}"></a>
          </h1>
        </div>
        <div class="sidebar-inner">
          <div class="sidebar-top">
	    <!--<form action="search-result.html" method="post" class="searchform" id="search-results">
              <input type="text" class="form-control" name="keyword" placeholder="Search...">
            </form>-->
            <form class="searchform" method="post" action="{$httpUrl}search" role="form">
	      <input type="text" class="form-control" name="search" placeholder="Search...">
	    </form>
          </div>
          <ul class="nav nav-sidebar">
            <li{$navHome}>
	      <a href="{$httpRoot}">
		<i class="icon-home"></i><span>Home</span>
	      </a>
	    </li>
            <li{$navExplorer}>
	      <a href="{$httpRoot}blocks">
		<i class="icon-layers"></i><span>Block Explorer</span>
	      </a>
            </li>
            <li{$navCharts}>
	      <a href="{$httpRoot}charts">
		<i class="icon-pie-chart"></i><span>Charts</span>
	      </a>
            </li>
            <li{$navStats}>
	      <a href="{$httpRoot}stats">
		<i class="icon-bar-chart"></i><span>Stats</span>
	      </a>
            </li>
              <li{$navCalculator}>
	      <a href="{$httpRoot}calculator">
		<i class="icon-calculator"></i><span>Calculator</span>
	      </a>
            </li>
          <li{$navFaucet}>
              <a href="{$httpRoot}faucet">
                  <i class="icon-layers"></i><span>Faucet</span>
              </a>

            <li class="nav-parent{$navTools}">
              <a href="#"><i class="icon-wrench"></i><span>Tools</span> <span class="fa arrow"></span></a>
              <ul class="children collapse">
                <li{$navCalculator}><a href="{$httpRoot}downloads"> Downloads</a></li>
                <li{$navPools}><a href="{$httpRoot}pools"> Pools</a></li>
                <li{$navChat}><a href="{$httpRoot}chat"> Chat</a></li>
              </ul>
            </li>

	   <li{$navexplorer}>
              <a href="http://status.burstcontrol.com:7777/network" target="_blank">
              <i class="icon-bar-chart"></i><span>Asset Overview</span>
	      </a>
           </li>
          </ul>            
          <!--<div class="sidebar-footer clearfix">
            <a class="pull-left footer-settings" href="#" data-rel="tooltip" data-placement="top" data-original-title="Settings">
            <i class="icon-settings"></i></a>
            <a class="pull-left toggle_fullscreen" href="#" data-rel="tooltip" data-placement="top" data-original-title="Fullscreen">
            <i class="icon-size-fullscreen"></i></a>
            <a class="pull-left" href="#" data-rel="tooltip" data-placement="top" data-original-title="Lockscreen">
            <i class="icon-lock"></i></a>
            <a class="pull-left btn-effect" href="#" data-modal="modal-1" data-rel="tooltip" data-placement="top" data-original-title="Logout">
            <i class="icon-power"></i></a>
          </div>-->
        </div>
      </div>
      <!-- END SIDEBAR -->
      <div class="main-content">
        <!-- BEGIN TOPBAR -->
        <div class="topbar">
          <div class="header-left">
            <div class="topnav">
              <a class="menutoggle" href="#" data-toggle="sidebar-collapsed"><span class="menu__handle"><span>Menu</span></span></a>
            </div>
          </div>
          <div class="header-right">
            <ul class="header-menu nav navbar-nav">
              <!-- BEGIN MESSAGES DROPDOWN -->
              <li class="dropdown" id="messages-header">
                <a href="#" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                <span class="text-muted">1 Burst = {$globalStats.burstBTC|number_format:8}&nbsp;</span>
                <i class="fa fa-bitcoin"></i>
                </a>
                <ul class="dropdown-menu">
                  <li class="dropdown-header clearfix">
                    <p class="pull-left">Burstcoin / Bitcoin Price</p>
                  </li>
                  <li class="dropdown-body">
                    <ul class="dropdown-menu-list withScroll" data-height="180">
                      <li style="cursor:default;padding:10px;">1k Burst = $ {$kBurstUSD|number_format:2} / 1 Burst = $ {$marketPriceUSD|number_format:6}</li>
		      <li style="cursor:default;padding:10px;">1k Burst = € {$kBurstEUR|number_format:2} / 1 Burst = € {$marketPriceEUR|number_format:6}</li>
		      <li style="cursor:default;padding:10px;">1 BTC = $ {$globalStats.btcUSD|number_format:2}</li>
		      <li style="cursor:default;padding:10px;">1 BTC = € {$globalStats.btcEUR|number_format:2}</li>
                    </ul>
                  </li>
                </ul>
              </li>
            </ul>
          </div>
        </div>
        <div class="page-content">
