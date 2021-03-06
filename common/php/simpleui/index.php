<?php
//	ini_set("log_errors", 1);
//	ini_set("error_log", "/tmp/php-hdhr-error.log");
//	error_log( "======= Debug Log START =========" );

	error_reporting(E_ALL & ~(E_DEPRECATED | E_STRICT));
	define('TINYAJAX_PATH', '.');
//	opcache_reset();
	require_once("TinyAjax.php");
	require_once("TinyAjaxBehavior.php");
	require_once("vars.php");
	require_once("includes/dvrui_hdhrbintools.php");
	require_once("includes/dvrui_recordengine_loglist.php");
	require_once("logfile.php");
	require_once("statusmessage.php");
	require_once("recordings.php");
	require_once("server.php");
	require_once("hdhr.php");
	require_once("theme.php");

	/* Prepare Ajax */
//	error_log( "Setting up Ajax" );
	$ajax = new TinyAjax();
	$ajax->setRequestType("POST");    // Change request-type from GET to POST
	$ajax->showLoading();             // Show loading while callback is in progress
	
	/* Export the PHP Interface */
//	error_log( "Setting up Ajax functions" );
	$ajax->exportFunction("getLogFile", "filename");
	$ajax->exportFunction("rmLogFile", "filename");
	$ajax->exportFunction("updateRecordPath","recordPath");
	$ajax->exportFunction("updateServerPort","serverPort");
	$ajax->exportFunction("changeDvrState","option");
	$ajax->exportFunction("openLogPage","");
	$ajax->exportFunction("openRecordingsPage","");
	$ajax->exportFunction("openHDHRPage","");
	$ajax->exportFunction("openServerPage","");
	$ajax->exportFunction("deleteRecordingByID","id, rerecord");

	/* GO */
//	error_log( "Enable Ajax" );
	$ajax->process(); // Process our callback

	// Apply default Theme */
	error_log( "Generate CSS if needed" );
	applyDefaultTheme();
	
	// Prep data for the page
//	error_log( "Get latest Status" );
	$statusmsg = getLatestHDHRStatus();

	// Get HDHR Version
	error_log( "Get HDHR DVR version" );
	$hdhr = DVRUI_Vars::DVR_pkgPath . '/' . DVRUI_Vars::DVR_bin;
	$DVRBin = new DVRUI_HDHRbintools($hdhr);
	$DVRBinVersion = $DVRBin->get_DVR_version();
	
	//Build navigation menu for pages
//	error_log( "Build Navigation Pages" );
// TODO: finish new Recordings page
//	$pageTitles = array('Server', 'HDHRs', 'Logs', 'Recordings', 'Recordings2');
//	$pageNames = array('server_page', 'hdhr_page', 'log_page', 'recordings_page', 'newrec_page');
	$pageTitles = array('Server', 'HDHRs', 'Logs', 'Recordings');
	$pageNames = array('server_page', 'hdhr_page', 'log_page', 'recordings_page');
	$menu_data = file_get_contents('style/pagemenu.html');
	$menuEntries = '';
	for ($i=0; $i < count($pageNames); $i++) {
		$menuEntry = str_replace('<!-- dvrui_menu_pagename-->',$pageNames[$i],file_get_contents('style/pagemenu_entry.html'));
		$menuEntry = str_replace('<!-- dvrui_menu_pagetitle-->',$pageTitles[$i],$menuEntry);
		$menuEntries .= $menuEntry;
	}
	$menu_data = str_replace('<!-- dvrui_pagemenu_entries-->',$menuEntries,$menu_data);
	
	// --- Build Page Here ---
//	error_log( "Build Index Page - get version" );
	$pageName = DVRUI_Vars::DVRUI_name;
	$UIVersion = "Version:" . DVRUI_Vars::DVRUI_version;
	$DVRVersion = "Record Engine Version: <i>" . $DVRBinVersion . "</i>";
	$pagecontent = "";

	// --- include header ---
//	error_log( "Build Index Page - header" );
	$header = file_get_contents('style/header.html');
	$pagecontent = str_replace('[[pagetitle]]',$pageName,$header);
	$pagecontent = str_replace('<!-- tinyAjax -->',$ajax->drawJavaScript(false, true),$pagecontent);

	// --- Build Body ---
//	error_log( "Build Index Page - body" );
	$indexPage = file_get_contents('style/index_page.html');
	$topmenu = file_get_contents('style/topmenu.html');
	$logfilelist = file_get_contents('style/index_loglist.html');
	$logfiledata = file_get_contents('style/index_logdata.html');
	$recordingsdata = file_get_contents('style/recordings.html');
	$serverdata = file_get_contents('style/server.html');
	$hdhrdata = file_get_contents('style/hdhr.html');

	$topmenu = str_replace('[[pagetitle]]',$pageName,$topmenu);
	$topmenu = str_replace('[[UI-Version]]',$UIVersion,$topmenu);
	$topmenu = str_replace('[[DVR-Version]]',$DVRVersion,$topmenu);

	$indexPage = str_replace('<!-- dvrui_topmenu -->',$topmenu,$indexPage);
	$indexPage = str_replace('<!-- dvrui_pagemenu -->',$menu_data,$indexPage);
	$indexPage = str_replace('<!-- dvrui_loglist -->',$logfilelist,$indexPage);
	$indexPage = str_replace('<!-- dvrui_logfile -->',$logfiledata,$indexPage);
	$indexPage = str_replace('<!-- dvrui_recordingslist -->',$recordingsdata,$indexPage);
	$indexPage = str_replace('<!-- dvrui_serverlist -->',$serverdata,$indexPage);
	$indexPage = str_replace('<!-- dvrui_hdhrlist -->',$hdhrdata,$indexPage);

	// -- Attach the Index to the Page
//	error_log( "Build Index Page - attach to page" );
	$pagecontent .= $indexPage;

	// --- include footer ---
//	error_log( "Build Index Page - footer" );
	$footer = file_get_contents('style/footer.html');
	$footer = str_replace('<!--dvr-statusmsg-->',$statusmsg,$footer);
	$pagecontent .= $footer;

//	error_log( "Output page" );
	echo($pagecontent);
//	error_log( "======= Debug Log END =========" );
?>

