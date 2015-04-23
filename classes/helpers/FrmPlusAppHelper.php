<?php
class FrmPlusAppHelper{
	public static function is_version2()
	{
		return method_exists( 'FrmAppHelper', 'plugin_version' ) && FrmAppHelper::plugin_version() >= '2.0';
	}

}