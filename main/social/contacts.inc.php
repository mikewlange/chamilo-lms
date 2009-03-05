<?php
$language_file = array('registration','messages','userInfo','admin');
require ('../inc/global.inc.php');
require_once (api_get_path(CONFIGURATION_PATH).'profile.conf.php');
include_once (api_get_path(LIBRARY_PATH).'fileManage.lib.php');
include_once (api_get_path(LIBRARY_PATH).'fileUpload.lib.php');
include_once (api_get_path(LIBRARY_PATH).'image.lib.php');
require_once (api_get_path(LIBRARY_PATH).'usermanager.lib.php');
require_once '../inc/lib/social.lib.php';
$this_section = SECTION_MYPROFILE;
$_SESSION['this_section']=$this_section;
//$list_path_friends=array();
?>
<div id="id" class="actions">
<?php echo utf8_encode(get_lang('SocialInformationComment')); ?>
</div>
<?php
$user_id=api_get_user_id();
$image_path = UserManager::get_user_picture_path_by_id ($user_id,'web',false,true);
?>
<div align="center" >
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="social-content-table">
  <tr>
    <td width="100%" height="20" valign="top">
    <table width="100%" border="0" cellpadding="0" cellspacing="0" class="social-title">
      <tr>
        <td width="100%" height="20" valign="top"><?php
        echo '&nbsp;&nbsp;Dokeos&nbsp;&nbsp;-&nbsp;&nbsp;';
        $user_id=api_get_user_id();
        $user_info=api_get_user_info($user_id);
        echo $user_info['firstName'].'&nbsp;&nbsp;'.$user_info['lastName'];
         ?></td>
        </tr>
    </table>
    </td>
  </tr>
  <tr>
    <td height="25" valign="top">
    <table width="100%" border="0" cellpadding="0" cellspacing="0" class="social-subtitle-search">
      <tr>
        <td width="100%" height="25" valign="top" class="social-align-box">&nbsp;&nbsp;<?php echo get_lang('Search').'&nbsp;&nbsp; : &nbsp;&nbsp;'; ?><input class="social-search-image" type="text" class="search-image" id="id_search_image" name="id_search_image" value="" onkeyup="search_image_social(this)" /></td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td height="175" valign="top">
    <table width="100%" border="0" cellpadding="0" cellspacing="0" >
      <tr>
        <td width="100%" height="22" valign="top">
        <table width="100%" border="0" cellpadding="0" cellspacing="0" class="social-align-box">
          <tr>
            <td width="100%" height="22" valign="top" class="social-title">&nbsp;&nbsp;<?php echo utf8_encode(get_lang('ContactsList')); ?></td>
              </tr>
        </table></td>
        </tr>
      <tr>
	<td height="153" valign="top">
<?php
echo '<div id="div_content_table">';
require_once 'show_search_image.inc.php';
echo '</div>';
 
?>
        </td>
        </tr>
    </table></td>
  </tr>
</table>
</div>