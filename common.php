<?php
//declare common variables and constants


//constants
//the table(s) we are using
	define("STAGE_BROWSE", 0);
	define("STAGE_LOGIN", 1);
	define("STAGE_DATASET", 2);
	define("STAGE_CURVEFIT", 3);
	define("STAGE_DIAGONAL", 4);
	define("STAGE_BUNDLE", 5);

//the table(s) we are using
	$db_tbl_accounts          = 'accounts';
	$db_tbl_datasets          = 'datasets';
	$db_tbl_ageseries         = 'age_series';
	$db_tbl_input             = 'input';
	$db_tbl_curvefit          = 'input_curvefit';
	$db_tbl_diagonals         = 'input_diagonals';
	$db_tbl_generations       = 'generations';
	$db_tbl_generations_model = 'generations_model';
	$db_tbl_bundles           = 'output_bundles';
		
//format variables - write numbers without commas (required for many
//chart and other operations to work
//these are used to format numbers - REMOVE commas when placed in mysql
	$dec_point                = '.';
	$thousands_sep            = '';
	
//define our current debugging level
//our debug mode variables
	$DEBUG  = true;
	$DEBUG2 = false;
?>