<?php

function update_login_session($login){
    global $_DB,$_CFG;
	return $_DB->query("update ".$_CFG['prefix']."admin_user set is_login='".$login['is_login']."' where id=".$login['id']);
} 



?>