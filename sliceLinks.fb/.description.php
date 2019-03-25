<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => "sliceLinks.FB",
	"DESCRIPTION" => "Show slice links from facet index",
	"ICON" => "/images/menu_ext.gif",
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "relevant",
		"NAME" => "Relevant",
		"CHILD" => array(
			"ID" => "filter",
			"NAME" => "Filter"
		)
	),
);

?>