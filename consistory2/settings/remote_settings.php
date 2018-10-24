{\rtf1\ansi\ansicpg1252\cocoartf1671
{\fonttbl\f0\fswiss\fcharset0 Helvetica;}
{\colortbl;\red255\green255\blue255;}
{\*\expandedcolortbl;;}
\margl1440\margr1440\vieww10800\viewh8400\viewkind0
\pard\tx720\tx1440\tx2160\tx2880\tx3600\tx4320\tx5040\tx5760\tx6480\tx7200\tx7920\tx8640\pardirnatural\partightenfactor0

\f0\fs24 \cf0 <?php\
define('DEBUG', false); // Should be false\
//define('DEBUG', true); // Should be false\
\
// overrides constants in genbio_top.php when running locally\
// if( $_SERVER['HTTP_HOST'] == '130.74.110.22' || $_SERVER['HTTP_HOST'] == 'consistory.hist.olemiss.edu' )\
if( $_SERVER['HTTP_HOST'] == '130.74.110.2' || $_SERVER['HTTP_HOST'] == 'consistory.hist.olemiss.edu' )\
\{\
//	define('SITE_PREFIX', '/');\
	define("SITE_PREFIX", "/");\
\}\
else\
\{\
//	define('SITE_PREFIX', '/consistory2/');\
	define("SITE_PREFIX", "/consistory2/");\
\}\
\
// Added by IDJ on 060802, different from  PFP_060331 --->  $_SERVER['DOCUMENT_ROOT']=$_SERVER['DOCUMENT_ROOT'] ."/consistory2/";\
define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT'] . SITE_PREFIX );\
\
// Declare DATABASE constants\
define('DB_SERVER', 'localhost');\
define('DB_USERNAME', 'imwatt');  // jconsist_dbuser\
define('DB_PASS', '#Calvin@John!');\
define('DB_NAME', 'jconsist_genbios');\
define('DB_TYPE', 'mysql');\
?>}